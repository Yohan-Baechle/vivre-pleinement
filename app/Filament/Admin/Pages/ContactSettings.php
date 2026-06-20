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
class ContactSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Coordonnées';

    protected static ?string $title = 'Coordonnées & réseaux sociaux';

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.admin.pages.contact-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'contact_email' => Settings::get('contact_email'),
            'contact_phone' => Settings::get('contact_phone'),
            'social_instagram' => Settings::get('social_instagram'),
            'social_facebook' => Settings::get('social_facebook'),
            'social_youtube' => Settings::get('social_youtube'),
            'social_tiktok' => Settings::get('social_tiktok'),
            'comments_enabled' => Settings::boolean('comments_enabled', true),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Coordonnées publiques')
                    ->description('Affichées sur la page Contact et le pied de page du site.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Email de contact')
                            ->email()
                            ->required()
                            ->helperText('Adresse affichée aux visiteurs pour vous écrire.'),

                        TextInput::make('contact_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->placeholder('06 12 34 56 78')
                            ->helperText('Laissez vide pour masquer le téléphone du site.'),
                    ]),

                Section::make('Réseaux sociaux')
                    ->description('Collez l\'adresse complète de chaque profil. Les réseaux laissés vides n\'apparaissent pas sur le site.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('social_instagram')
                            ->label('Instagram')
                            ->url()
                            ->prefixIcon(Heroicon::OutlinedCamera)
                            ->placeholder('https://instagram.com/...'),

                        TextInput::make('social_facebook')
                            ->label('Facebook')
                            ->url()
                            ->placeholder('https://facebook.com/...'),

                        TextInput::make('social_youtube')
                            ->label('YouTube')
                            ->url()
                            ->placeholder('https://youtube.com/@...'),

                        TextInput::make('social_tiktok')
                            ->label('TikTok')
                            ->url()
                            ->placeholder('https://tiktok.com/@...'),
                    ]),

                Section::make('Blog')
                    ->description('Réglages globaux des commentaires d\'articles.')
                    ->schema([
                        Toggle::make('comments_enabled')
                            ->label('Autoriser les commentaires sur le blog')
                            ->onColor('success')
                            ->helperText('Interrupteur général. Chaque article peut aussi être fermé individuellement. Les nouveaux commentaires sont toujours validés par vous avant publication.'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Settings::setMany([
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? '',
            'social_instagram' => $data['social_instagram'] ?? '',
            'social_facebook' => $data['social_facebook'] ?? '',
            'social_youtube' => $data['social_youtube'] ?? '',
            'social_tiktok' => $data['social_tiktok'] ?? '',
            'comments_enabled' => ! empty($data['comments_enabled']) ? '1' : '0',
        ]);

        Notification::make()->success()->title('Coordonnées enregistrées')->send();
    }
}
