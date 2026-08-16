<?php

namespace App\Filament\Admin\Resources\Enrollments\Schemas;

use App\Models\Enrollment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EnrollmentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Vente')
                ->icon(Heroicon::OutlinedBanknotes)
                ->columns(3)
                ->schema([
                    TextEntry::make('course.title')
                        ->label('Formation')
                        ->columnSpan(2),

                    TextEntry::make('status')
                        ->label('Statut')
                        ->badge(),

                    TextEntry::make('amount_paid_cents')
                        ->label('Montant')
                        ->money('eur', divideBy: 100),

                    TextEntry::make('purchased_at')
                        ->label('Acheté le')
                        ->dateTime('d/m/Y à H:i')
                        ->placeholder('Non payé'),

                    TextEntry::make('stripe_payment_intent_id')
                        ->label('Paiement Stripe')
                        ->placeholder('Aucun (accès offert)')
                        ->url(fn (Enrollment $record) => $record->stripe_payment_intent_id
                            ? 'https://dashboard.stripe.com/payments/'.$record->stripe_payment_intent_id
                            : null, shouldOpenInNewTab: true),
                ]),

            Section::make('Élève')
                ->icon(Heroicon::OutlinedUser)
                ->columns(2)
                ->schema([
                    TextEntry::make('student.name')
                        ->label('Nom'),

                    TextEntry::make('student.email')
                        ->label('Email')
                        ->copyable(),
                ]),
        ]);
    }
}
