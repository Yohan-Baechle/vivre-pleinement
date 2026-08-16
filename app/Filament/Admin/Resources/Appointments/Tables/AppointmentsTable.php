<?php

namespace App\Filament\Admin\Resources\Appointments\Tables;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentNoShow;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Services\AppointmentLifecycleService;
use App\Services\AppointmentSlotService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->emptyStateIcon(Heroicon::OutlinedCalendarDays)
            ->emptyStateHeading('Aucun rendez-vous à venir')
            ->emptyStateDescription('Les réservations prises sur le site '
                .'arrivent ici. Vous pouvez aussi en ajouter une à la main.')
            ->emptyStateActions([
                CreateAction::make()->label('Ajouter un rendez-vous'),
            ])
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Date & heure')
                    ->dateTime('D d/m/Y · H:i')
                    ->sortable(),

                TextColumn::make('reference')
                    ->label('Réf.')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('customer_full_name')
                    ->label('Client')
                    ->searchable(['customer_first_name', 'customer_last_name'])
                    ->description(fn (Appointment $record) => filled($record->notes)
                        ? 'Message : '.Str::limit($record->notes, 60)
                        : null)
                    ->tooltip(fn (Appointment $record) => $record->notes),

                TextColumn::make('service.name')
                    ->label('Prestation')
                    ->sortable(),

                TextColumn::make('channel')
                    ->label('Format')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge(),

                TextColumn::make('customer_phone')
                    ->label('Téléphone')
                    ->placeholder('–')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(AppointmentStatus::class),

                SelectFilter::make('appointment_service_id')
                    ->label('Prestation')
                    ->options(fn () => AppointmentService::query()->orderBy('name')->pluck('name', 'id')),

                Filter::make('upcoming')
                    ->label('À venir uniquement')
                    ->query(fn (Builder $query) => $query->where('starts_at', '>=', CarbonImmutable::now()))
                    ->default(),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->button()
                    ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Pending)
                    ->requiresConfirmation()
                    ->modalDescription('Le client recevra l\'email de '
                        .'confirmation avec le lien de la séance.')
                    ->action(function (Appointment $record): void {
                        app(AppointmentLifecycleService::class)->confirm($record);

                        Notification::make()->success()->title('Rendez-vous confirmé')->send();
                    }),

                ActionGroup::make([
                    ViewAction::make()
                        ->label('Voir la fiche'),

                    self::rescheduleAction(),

                    Action::make('cancel')
                        ->label('Annuler')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Appointment $record) => $record->status->isCancellable())
                        ->requiresConfirmation()
                        ->action(function (Appointment $record): void {
                            app(AppointmentLifecycleService::class)->cancel($record);

                            Notification::make()->success()->title('Rendez-vous annulé')->send();
                        }),

                    Action::make('noShow')
                        ->label('Marquer absent')
                        ->icon('heroicon-o-user-minus')
                        ->color('danger')
                        ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Confirmed
                            && $record->ends_at->isPast())
                        ->requiresConfirmation()
                        ->modalDescription('Le client sera marqué comme absent et recevra un email l\'invitant à reprendre rendez-vous.')
                        ->action(function (Appointment $record): void {
                            $record->update(['status' => AppointmentStatus::NoShow]);

                            Mail::to($record->customer_email)->send(new AppointmentNoShow($record));

                            Notification::make()->success()->title('Client marqué absent')->send();
                        }),

                    EditAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Déplacement guidé : l'admin choisit un jour, puis l'un des créneaux
     * réellement réservables ce jour-là. Un champ date-heure libre laissait
     * poser une séance un dimanche ou en plein congé, puisque seul le
     * chevauchement avec un autre rendez-vous était vérifié.
     */
    private static function rescheduleAction(): Action
    {
        return Action::make('reschedule')
            ->label('Déplacer')
            ->icon('heroicon-o-arrows-right-left')
            ->color('warning')
            ->visible(fn (Appointment $record) => $record->isManageable())
            ->modalHeading('Déplacer le rendez-vous')
            ->modalSubmitActionLabel('Déplacer et prévenir le client')
            ->fillForm(fn (Appointment $record) => [
                'date' => $record->starts_at->toDateString(),
            ])
            ->schema([
                DatePicker::make('date')
                    ->label('Nouveau jour')
                    ->native(false)
                    ->displayFormat('D d/m/Y')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('starts_at', null)),

                Select::make('starts_at')
                    ->label('Créneau disponible')
                    ->options(fn (Get $get, Appointment $record) => self::slotOptions($record, $get('date')))
                    ->required()
                    ->native(false)
                    ->helperText('Seuls les créneaux ouverts et libres de ce '
                        .'jour sont proposés. Un jour sans créneau est fermé '
                        .'ou déjà complet.'),
            ])
            ->action(function (Appointment $record, array $data): void {
                $previousStart = $record->starts_at->copy();

                $moved = app(AppointmentSlotService::class)
                    ->move($record, CarbonImmutable::parse($data['starts_at']));

                if (! $moved) {
                    Notification::make()
                        ->danger()
                        ->title('Créneau indisponible')
                        ->body('Un autre rendez-vous vient d\'occuper ce créneau.')
                        ->send();

                    return;
                }

                Mail::to($record->customer_email)
                    ->send(new AppointmentRescheduled($record->fresh('service'), $previousStart));

                Notification::make()->success()->title('Rendez-vous déplacé')->send();
            });
    }

    /**
     * Créneaux réservables d'une date, plus le créneau actuel du rendez-vous
     * lorsqu'il tombe ce jour-là : sans lui, le créneau occupé par le
     * rendez-vous qu'on déplace serait absent de sa propre liste.
     *
     * @return array<string, string>
     */
    private static function slotOptions(Appointment $record, mixed $date): array
    {
        if (blank($date)) {
            return [];
        }

        $day = CarbonImmutable::parse($date)->startOfDay();

        $options = app(AppointmentSlotService::class)
            ->slotsForDate($record->service, $day)
            ->mapWithKeys(fn (array $slot) => [
                $slot['start']->toDateTimeString() => $slot['label'],
            ])
            ->all();

        $current = CarbonImmutable::parse($record->starts_at);

        if ($current->isSameDay($day)) {
            $options[$current->toDateTimeString()] = $current->format('H:i')
                .' (créneau actuel)';
            ksort($options);
        }

        return $options;
    }
}
