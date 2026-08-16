<?php

namespace App\Filament\Admin\Resources\DateOverrides\Pages;

use App\Filament\Admin\Resources\DateOverrides\DateOverrideResource;
use App\Models\DateOverride;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListDateOverrides extends ListRecords
{
    /**
     * Garde-fou sur la durée d'un blocage saisi d'un coup : au-delà, c'est une
     * erreur de saisie plutôt qu'un vrai congé.
     */
    private const MAX_PERIOD_DAYS = 366;

    protected static string $resource = DateOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->blockPeriodAction(),
            $this->blockTodayAction(),
            CreateAction::make()
                ->label('Bloquer une date')
                ->color('gray'),
        ];
    }

    /**
     * Bloque une période complète en une saisie, plutôt qu'une date à la fois.
     */
    private function blockPeriodAction(): Action
    {
        return Action::make('blockPeriod')
            ->label('Bloquer une période')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->modalHeading('Bloquer une période')
            ->modalDescription('Chaque jour de la période devient '
                .'indisponible à la réservation.')
            ->modalSubmitActionLabel('Bloquer')
            ->schema([
                DatePicker::make('from')
                    ->label('Du')
                    ->native(false)
                    ->required()
                    ->default(CarbonImmutable::now()->toDateString()),
                DatePicker::make('to')
                    ->label('Au (inclus)')
                    ->native(false)
                    ->required()
                    ->afterOrEqual('from')
                    ->default(CarbonImmutable::now()->toDateString()),
                TimePicker::make('start_time')
                    ->label('Heure de début')
                    ->seconds(false)
                    ->helperText('Laissez vide pour bloquer les journées '
                        .'entières.'),
                TimePicker::make('end_time')
                    ->label('Heure de fin')
                    ->seconds(false)
                    ->after('start_time')
                    ->requiredWith('start_time'),
                TextInput::make('reason')
                    ->label('Motif (optionnel)')
                    ->placeholder('Congés, formation, indisponible…')
                    ->columnSpanFull(),
            ])
            ->action(fn (array $data) => $this->blockPeriod($data));
    }

    private function blockTodayAction(): Action
    {
        return Action::make('blockToday')
            ->label("Bloquer aujourd'hui")
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('Plus aucun créneau ne sera réservable '
                .'aujourd\'hui.')
            ->action(fn () => $this->blockPeriod([
                'from' => CarbonImmutable::now()->toDateString(),
                'to' => CarbonImmutable::now()->toDateString(),
                'reason' => 'Indisponible',
            ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function blockPeriod(array $data): void
    {
        $from = CarbonImmutable::parse($data['from'])->startOfDay();
        $to = CarbonImmutable::parse($data['to'])->startOfDay();

        if ($from->diffInDays($to) >= self::MAX_PERIOD_DAYS) {
            Notification::make()
                ->danger()
                ->title('Période trop longue')
                ->body('Bloquez au maximum un an d\'un seul coup.')
                ->send();

            return;
        }

        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;
        $created = 0;

        for ($date = $from; $date->lessThanOrEqualTo($to); $date = $date->addDay()) {
            $exists = DateOverride::query()
                ->whereDate('date', $date->toDateString())
                ->when(
                    $startTime === null,
                    fn ($query) => $query->whereNull('start_time'),
                    fn ($query) => $query->where('start_time', $startTime),
                )
                ->when(
                    $endTime === null,
                    fn ($query) => $query->whereNull('end_time'),
                    fn ($query) => $query->where('end_time', $endTime),
                )
                ->exists();

            if ($exists) {
                continue;
            }

            DateOverride::create([
                'date' => $date->toDateString(),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'reason' => $data['reason'] ?? null,
            ]);

            $created++;
        }

        Notification::make()
            ->success()
            ->title($created === 0
                ? 'Cette période était déjà bloquée'
                : trans_choice(
                    '{1} :count journée bloquée|[2,*] :count journées bloquées',
                    $created,
                    ['count' => $created],
                ))
            ->send();
    }
}
