<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\AppointmentLifecycleService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UpcomingAppointments extends TableWidget
{
    protected static ?int $sort = 3;

    protected static ?string $heading = 'Prochains rendez-vous (7 jours)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->paginated(false)
            ->emptyStateIcon(Heroicon::OutlinedCalendarDays)
            ->emptyStateHeading('Aucune séance dans les 7 jours')
            ->emptyStateDescription('Les réservations à venir s\'afficheront ici.')
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Date & heure')
                    ->dateTime('D d/m · H:i'),

                TextColumn::make('customer_full_name')
                    ->label('Client')
                    ->description(fn (Appointment $record) => filled($record->notes)
                        ? 'Message : '.Str::limit($record->notes, 70)
                        : null),

                TextColumn::make('service.name')
                    ->label('Prestation'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
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

                Action::make('open')
                    ->label('Ouvrir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Appointment $record) => route('filament.admin.resources.appointments.edit', $record)),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $now = CarbonImmutable::now();

        return Appointment::query()
            ->with('service')
            ->blocking()
            ->whereBetween('starts_at', [$now, $now->addDays(7)])
            ->orderBy('starts_at');
    }
}
