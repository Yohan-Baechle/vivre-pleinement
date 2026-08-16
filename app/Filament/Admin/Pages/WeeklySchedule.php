<?php

namespace App\Filament\Admin\Pages;

use App\Models\AppointmentService;
use App\Models\Availability;
use App\Support\Weekdays;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Édition de l'horaire hebdomadaire type en une seule page : un bloc par
 * jour, des plages horaires ajoutables à la volée, et un aperçu du nombre de
 * créneaux réellement proposés au client.
 *
 * @property-read Schema $form
 */
class WeeklySchedule extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Horaires de la semaine';

    protected static ?string $title = 'Horaires de la semaine';

    protected static string|UnitEnum|null $navigationGroup = 'Rendez-vous';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.admin.pages.weekly-schedule';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'appointment_service_id' => null,
            'days' => $this->scheduleState(null),
        ]);
    }

    public function getSubheading(): ?string
    {
        return 'Les plages définies ici sont découpées en créneaux selon la '
            .'durée de chaque prestation. Un jour fermé conserve ses plages '
            .'sans les proposer à la réservation.';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Prestation concernée')
                    ->description('Un horaire commun à toutes les prestations, '
                        .'ou un horaire dédié à l\'une d\'elles.')
                    ->schema([
                        Select::make('appointment_service_id')
                            ->hiddenLabel()
                            ->options(fn () => AppointmentService::query()
                                ->orderBy('name')
                                ->pluck('name', 'id'))
                            ->placeholder('Toutes les prestations')
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('days', $this->scheduleState(
                                    $this->normalizeServiceId($state)
                                ));
                            }),
                    ]),

                ...array_map(
                    fn (int $day) => $this->daySection($day),
                    Weekdays::orderedKeys(),
                ),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('applyPreset')
                ->label('Appliquer un horaire type')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('gray')
                ->modalHeading('Horaire type')
                ->modalDescription('Remplace les plages des jours cochés. '
                    .'Rien n\'est enregistré tant que vous ne validez pas la '
                    .'page.')
                ->modalSubmitActionLabel('Appliquer')
                ->schema([
                    CheckboxList::make('days')
                        ->label('Jours concernés')
                        ->options(Weekdays::labels())
                        ->default([1, 2, 3, 4, 5])
                        ->columns(3)
                        ->required(),
                    TimePicker::make('morning_start')
                        ->label('Matin — de')
                        ->seconds(false)
                        ->default('09:00'),
                    TimePicker::make('morning_end')
                        ->label('Matin — à')
                        ->seconds(false)
                        ->default('12:00')
                        ->after('morning_start'),
                    TimePicker::make('afternoon_start')
                        ->label('Après-midi — de')
                        ->seconds(false)
                        ->default('14:00'),
                    TimePicker::make('afternoon_end')
                        ->label('Après-midi — à')
                        ->seconds(false)
                        ->default('18:00')
                        ->after('afternoon_start'),
                ])
                ->action(fn (array $data) => $this->applyPreset($data)),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $serviceId = $this->normalizeServiceId(
            $data['appointment_service_id'] ?? null
        );

        $created = DB::transaction(function () use ($data, $serviceId): int {
            $this->scopedQuery($serviceId)->delete();

            $rows = [];

            foreach (Weekdays::orderedKeys() as $day) {
                $dayState = $data['days'][self::dayKey($day)] ?? [];
                $isOpen = (bool) ($dayState['is_open'] ?? false);

                foreach ($dayState['ranges'] ?? [] as $range) {
                    $start = self::normalizeTime($range['start_time'] ?? null);
                    $end = self::normalizeTime($range['end_time'] ?? null);

                    if ($start === null || $end === null) {
                        continue;
                    }

                    $rows[] = [
                        'appointment_service_id' => $serviceId,
                        'day_of_week' => $day,
                        'start_time' => $start,
                        'end_time' => $end,
                        'is_active' => $isOpen,
                    ];
                }
            }

            foreach ($rows as $row) {
                Availability::create($row);
            }

            return count($rows);
        });

        Notification::make()
            ->success()
            ->title('Horaires enregistrés')
            ->body($created === 0
                ? 'Aucune plage horaire : aucun créneau ne sera proposé.'
                : trans_choice(
                    '{1} :count plage horaire enregistrée.'
                        .'|]1,*[ :count plages horaires enregistrées.',
                    $created,
                    ['count' => $created],
                ))
            ->send();
    }

    /**
     * Bloc d'édition d'un jour : l'interrupteur ouvert/fermé, les plages
     * horaires et le nombre de créneaux qui en découle.
     */
    private function daySection(int $day): Section
    {
        $key = self::dayKey($day);
        $path = "days.{$key}";

        return Section::make(Weekdays::label($day))
            ->headerActions([$this->copyDayAction($day)])
            ->schema([
                Toggle::make("{$path}.is_open")
                    ->label('Ouvert à la réservation')
                    ->inline(false)
                    ->onColor('success')
                    ->live(),

                Text::make(fn (Get $get): string => $get("{$path}.is_open")
                    ? $this->daySummary($get("{$path}.ranges") ?? [])
                    : 'Jour fermé : les plages sont conservées mais aucun '
                        .'créneau n\'est proposé.')
                    ->size(TextSize::Small),

                Repeater::make("{$path}.ranges")
                    ->hiddenLabel()
                    ->hidden(fn (Get $get) => ! $get("{$path}.is_open"))
                    ->dehydratedWhenHidden()
                    ->schema([
                        TimePicker::make('start_time')
                            ->label('De')
                            ->seconds(false)
                            ->required()
                            ->live(onBlur: true),
                        TimePicker::make('end_time')
                            ->label('À')
                            ->seconds(false)
                            ->required()
                            ->after('start_time')
                            ->live(onBlur: true),
                    ])
                    ->columns(2)
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->addActionLabel('Ajouter une plage horaire')
                    ->itemLabel(fn (array $state): ?string => $this
                        ->rangeLabel($state))
                    ->rules([fn (): Closure => $this->noOverlapRule()]),
            ]);
    }

    /**
     * Recopie les plages d'un jour vers d'autres jours de la semaine, sans
     * toucher à la base tant que la page n'est pas enregistrée.
     */
    private function copyDayAction(int $day): Action
    {
        $key = self::dayKey($day);

        $targets = collect(Weekdays::labels())
            ->except($day)
            ->all();

        return Action::make("copy_{$key}")
            ->label('Copier vers…')
            ->icon(Heroicon::OutlinedDocumentDuplicate)
            ->link()
            ->color('gray')
            ->modalHeading('Copier les plages du '.mb_strtolower(
                Weekdays::label($day)
            ))
            ->modalSubmitActionLabel('Copier')
            ->schema([
                CheckboxList::make('targets')
                    ->label('Vers les jours')
                    ->options($targets)
                    ->columns(2)
                    ->required(),
            ])
            ->action(function (array $data) use ($key): void {
                $ranges = $this->data['days'][$key]['ranges'] ?? [];

                foreach ($data['targets'] as $target) {
                    $this->data['days'][self::dayKey((int) $target)] = [
                        'is_open' => true,
                        'ranges' => self::rekey($ranges),
                    ];
                }

                Notification::make()
                    ->success()
                    ->title('Plages copiées')
                    ->body('Enregistrez la page pour les appliquer.')
                    ->send();
            });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyPreset(array $data): void
    {
        $ranges = collect([
            [
                'start_time' => self::normalizeTime($data['morning_start'] ?? null),
                'end_time' => self::normalizeTime($data['morning_end'] ?? null),
            ],
            [
                'start_time' => self::normalizeTime($data['afternoon_start'] ?? null),
                'end_time' => self::normalizeTime($data['afternoon_end'] ?? null),
            ],
        ])
            ->filter(fn (array $range) => $range['start_time'] !== null
                && $range['end_time'] !== null)
            ->values()
            ->all();

        foreach ($data['days'] as $day) {
            $this->data['days'][self::dayKey((int) $day)] = [
                'is_open' => $ranges !== [],
                'ranges' => self::rekey($ranges),
            ];
        }

        Notification::make()
            ->success()
            ->title('Horaire type appliqué')
            ->body('Enregistrez la page pour l\'appliquer.')
            ->send();
    }

    /**
     * Refuse deux plages qui se chevauchent le même jour : elles produiraient
     * des créneaux en double dans le calendrier public.
     */
    private function noOverlapRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $ranges = collect(is_array($value) ? $value : [])
                ->map(fn ($range) => [
                    'start' => self::minutes($range['start_time'] ?? null),
                    'end' => self::minutes($range['end_time'] ?? null),
                ])
                ->filter(fn (array $range) => $range['start'] !== null
                    && $range['end'] !== null)
                ->sortBy('start')
                ->values();

            foreach ($ranges as $index => $range) {
                $next = $ranges->get($index + 1);

                if ($next !== null && $next['start'] < $range['end']) {
                    $fail('Deux plages de ce jour se chevauchent : le client '
                        .'verrait le même horaire proposé deux fois.');

                    return;
                }
            }
        };
    }

    /**
     * État initial du formulaire, reconstruit depuis les disponibilités
     * enregistrées pour la prestation choisie.
     *
     * @return array<string, array{is_open: bool, ranges: array<string, array{
     *     start_time: string,
     *     end_time: string,
     * }>}>
     */
    private function scheduleState(?int $serviceId): array
    {
        $byDay = $this->scopedQuery($serviceId)
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        $state = [];

        foreach (Weekdays::orderedKeys() as $day) {
            $rows = $byDay->get($day, collect());

            $state[self::dayKey($day)] = [
                'is_open' => $rows->contains(
                    fn (Availability $row) => (bool) $row->is_active
                ),
                'ranges' => self::rekey($rows
                    ->map(fn (Availability $row) => [
                        'start_time' => self::normalizeTime($row->start_time),
                        'end_time' => self::normalizeTime($row->end_time),
                    ])
                    ->values()
                    ->all()),
            ];
        }

        return $state;
    }

    /**
     * @return Builder<Availability>
     */
    private function scopedQuery(?int $serviceId)
    {
        return Availability::query()->when(
            $serviceId === null,
            fn ($query) => $query->whereNull('appointment_service_id'),
            fn ($query) => $query->where('appointment_service_id', $serviceId),
        );
    }

    /**
     * Libellé d'une plage, complété du nombre de créneaux qu'elle produit
     * pour la prestation de référence.
     *
     * @param  array<string, mixed>  $state
     */
    private function rangeLabel(array $state): ?string
    {
        $start = self::normalizeTime($state['start_time'] ?? null);
        $end = self::normalizeTime($state['end_time'] ?? null);

        if ($start === null || $end === null) {
            return null;
        }

        $slots = $this->slotCount($start, $end);

        return "{$start} – {$end}"
            .($slots === null ? '' : " · {$slots} créneaux");
    }

    /**
     * @param  array<int|string, mixed>  $ranges
     */
    private function daySummary(array $ranges): string
    {
        $service = $this->referenceService();

        if ($service === null) {
            return 'Créez une prestation pour voir le nombre de créneaux.';
        }

        $total = collect($ranges)->sum(function ($range): int {
            $start = self::normalizeTime($range['start_time'] ?? null);
            $end = self::normalizeTime($range['end_time'] ?? null);

            if ($start === null || $end === null) {
                return 0;
            }

            return $this->slotCount($start, $end) ?? 0;
        });

        if ($total === 0) {
            return 'Aucun créneau proposé pour l\'instant.';
        }

        return trans_choice(
            '{1} :count créneau|]1,*[ :count créneaux',
            $total,
            ['count' => $total],
        )." de {$service->duration_minutes} min « {$service->name} »";
    }

    /**
     * Nombre de créneaux obtenus en découpant une plage, selon la durée et le
     * battement de la prestation de référence. Reproduit le calcul de
     * AppointmentSlotService.
     */
    private function slotCount(string $start, string $end): ?int
    {
        $service = $this->referenceService();

        if ($service === null) {
            return null;
        }

        $span = (self::minutes($end) ?? 0) - (self::minutes($start) ?? 0);
        $duration = max(1, $service->duration_minutes);
        $step = max(1, $service->duration_minutes + $service->buffer_minutes);

        if ($span < $duration) {
            return 0;
        }

        return (int) floor(($span - $duration) / $step) + 1;
    }

    /**
     * Prestation servant d'étalon à l'aperçu : celle qui est sélectionnée, ou
     * à défaut la première prestation active.
     */
    private function referenceService(): ?AppointmentService
    {
        $serviceId = $this->normalizeServiceId(
            $this->data['appointment_service_id'] ?? null
        );

        if ($serviceId !== null) {
            return AppointmentService::query()->find($serviceId);
        }

        return AppointmentService::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->first();
    }

    private function normalizeServiceId(mixed $state): ?int
    {
        return filled($state) ? (int) $state : null;
    }

    private static function dayKey(int $day): string
    {
        return "day_{$day}";
    }

    /**
     * Réindexe une liste de plages sur des clés uniques, seul format que le
     * Repeater sait suivre d'un rendu à l'autre.
     *
     * @param  array<int|string, mixed>  $ranges
     * @return array<string, mixed>
     */
    private static function rekey(array $ranges): array
    {
        return collect($ranges)
            ->values()
            ->mapWithKeys(fn ($range) => [(string) Str::uuid() => $range])
            ->all();
    }

    private static function normalizeTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }

    private static function minutes(mixed $value): ?int
    {
        $time = self::normalizeTime($value);

        if ($time === null || ! str_contains($time, ':')) {
            return null;
        }

        [$hour, $minute] = array_pad(explode(':', $time), 2, '0');

        return ((int) $hour * 60) + (int) $minute;
    }
}
