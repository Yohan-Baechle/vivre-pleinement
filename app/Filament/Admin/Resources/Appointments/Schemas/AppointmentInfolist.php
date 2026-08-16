<?php

namespace App\Filament\Admin\Resources\Appointments\Schemas;

use App\Models\Appointment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Séance')
                ->columns(3)
                ->schema([
                    TextEntry::make('starts_at')
                        ->label('Date & heure')
                        ->dateTime('l d F Y à H:i'),

                    TextEntry::make('service.name')
                        ->label('Prestation'),

                    TextEntry::make('reference')
                        ->label('Référence')
                        ->copyable(),

                    TextEntry::make('status')
                        ->label('Statut')
                        ->badge(),

                    TextEntry::make('payment_status')
                        ->label('Paiement')
                        ->badge(),

                    TextEntry::make('channel')
                        ->label('Format')
                        ->badge(),

                    TextEntry::make('meeting_url')
                        ->label('Lien de la séance')
                        ->placeholder('Lien par défaut')
                        ->url(fn (Appointment $record) => $record->meeting_url, shouldOpenInNewTab: true)
                        ->columnSpanFull(),
                ]),

            Section::make('Client')
                ->columns(3)
                ->schema([
                    TextEntry::make('customer_full_name')
                        ->label('Nom'),

                    TextEntry::make('customer_email')
                        ->label('Email')
                        ->copyable(),

                    TextEntry::make('customer_phone')
                        ->label('Téléphone')
                        ->placeholder('Non renseigné')
                        ->copyable(),

                    TextEntry::make('notes')
                        ->label('Message laissé à la réservation')
                        ->placeholder('Aucun message')
                        ->prose()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
