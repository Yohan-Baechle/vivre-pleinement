<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UpcomingAppointments extends TableWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Prochains rendez-vous (7 jours)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->paginated(false)
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Date & heure')
                    ->dateTime('D d/m · H:i'),

                TextColumn::make('customer_full_name')
                    ->label('Client'),

                TextColumn::make('service.name')
                    ->label('Prestation'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.appointments.edit', $record)),
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
