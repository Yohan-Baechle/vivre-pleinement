<?php

namespace App\Filament\Admin\Pages;

use App\Support\Settings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class BookingSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Réglages';

    protected static ?string $title = 'Réglages des rendez-vous';

    protected static string|UnitEnum|null $navigationGroup = 'Rendez-vous';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.admin.pages.booking-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'meet_url' => Settings::get('meet_url'),
            'notify_email' => Settings::get('notify_email'),
            'reminder_24h_enabled' => Settings::boolean('reminder_24h_enabled', true),
            'reminder_1h_enabled' => Settings::boolean('reminder_1h_enabled', true),
            'followup_enabled' => Settings::boolean('followup_enabled', true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Visioconférence')
                    ->description('Le lien envoyé au client dans les emails et la page de confirmation.')
                    ->schema([
                        TextInput::make('meet_url')
                            ->label('Lien Google Meet par défaut')
                            ->url()
                            ->placeholder('https://meet.google.com/xxx-xxxx-xxx')
                            ->helperText('Créez un lien permanent sur meet.google.com puis collez-le ici. Modifiable par rendez-vous si besoin.'),
                    ]),

                Section::make('Notifications')
                    ->schema([
                        TextInput::make('notify_email')
                            ->label('Email de notification')
                            ->email()
                            ->required()
                            ->helperText('Adresse qui reçoit les nouvelles réservations.'),
                    ]),

                Section::make('Rappels automatiques')
                    ->description('Emails envoyés automatiquement aux clients avant et après leur rendez-vous.')
                    ->columns(1)
                    ->schema([
                        Toggle::make('reminder_24h_enabled')
                            ->label('Rappel la veille (24 h avant)')
                            ->onColor('success'),
                        Toggle::make('reminder_1h_enabled')
                            ->label('Rappel 1 h avant')
                            ->onColor('success'),
                        Toggle::make('followup_enabled')
                            ->label('Message de suivi après le rendez-vous')
                            ->onColor('success'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::setMany([
            'meet_url' => $data['meet_url'] ?? '',
            'notify_email' => $data['notify_email'],
            'reminder_24h_enabled' => ! empty($data['reminder_24h_enabled']) ? '1' : '0',
            'reminder_1h_enabled' => ! empty($data['reminder_1h_enabled']) ? '1' : '0',
            'followup_enabled' => ! empty($data['followup_enabled']) ? '1' : '0',
        ]);

        Notification::make()->success()->title('Réglages enregistrés')->send();
    }
}
