<?php

namespace App\Filament\Admin\Resources\Appointments\Tables;

use App\Enums\AppointmentStatus;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmation;
use App\Mail\AppointmentRescheduled;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Services\AppointmentSlotService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
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
                    ->searchable(['customer_first_name', 'customer_last_name']),

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
                ActionGroup::make([
                    Action::make('confirm')
                        ->label('Confirmer')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (Appointment $record) => $record->status === AppointmentStatus::Pending)
                        ->requiresConfirmation()
                        ->action(function (Appointment $record) {
                            $record->update(['status' => AppointmentStatus::Confirmed]);
                            Mail::to($record->customer_email)->send(new AppointmentConfirmation($record));

                            Notification::make()->success()->title('Rendez-vous confirmé')->send();
                        }),

                    Action::make('reschedule')
                        ->label('Déplacer')
                        ->icon('heroicon-o-arrows-right-left')
                        ->color('warning')
                        ->visible(fn (Appointment $record) => $record->isManageable())
                        ->fillForm(fn (Appointment $record) => ['starts_at' => $record->starts_at])
                        ->schema([
                            DateTimePicker::make('starts_at')
                                ->label('Nouveau créneau')
                                ->seconds(false)
                                ->minutesStep(15)
                                ->required(),
                        ])
                        ->action(function (Appointment $record, array $data) {
                            $previousStart = $record->starts_at->copy();

                            $moved = app(AppointmentSlotService::class)
                                ->move($record, CarbonImmutable::parse($data['starts_at']));

                            if (! $moved) {
                                Notification::make()
                                    ->danger()
                                    ->title('Créneau indisponible')
                                    ->body('Un autre rendez-vous occupe déjà ce créneau.')
                                    ->send();

                                return;
                            }

                            Mail::to($record->customer_email)
                                ->send(new AppointmentRescheduled($record->fresh('service'), $previousStart));

                            Notification::make()->success()->title('Rendez-vous déplacé')->send();
                        }),

                    Action::make('cancel')
                        ->label('Annuler')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (Appointment $record) => $record->status->isCancellable())
                        ->requiresConfirmation()
                        ->action(function (Appointment $record) {
                            $record->update([
                                'status' => AppointmentStatus::Cancelled,
                                'cancelled_at' => CarbonImmutable::now(),
                            ]);

                            Mail::to($record->customer_email)->send(new AppointmentCancelled($record));

                            Notification::make()->success()->title('Rendez-vous annulé')->send();
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
}
