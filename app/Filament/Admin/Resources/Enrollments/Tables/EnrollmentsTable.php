<?php

namespace App\Filament\Admin\Resources\Enrollments\Tables;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('student.name')
                    ->label('Élève')
                    ->searchable()
                    ->description(fn ($record): ?string => $record->student?->email),
                TextColumn::make('course.title')
                    ->label('Formation')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('amount_paid_cents')
                    ->label('Montant')
                    ->money('eur', divideBy: 100)
                    ->sortable(),
                TextColumn::make('purchased_at')
                    ->label('Acheté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('–'),
                TextColumn::make('stripe_payment_intent_id')
                    ->label('Stripe')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Voir →' : '–')
                    ->url(fn ($record): ?string => $record->stripe_payment_intent_id
                        ? 'https://dashboard.stripe.com/payments/'.$record->stripe_payment_intent_id
                        : null, shouldOpenInNewTab: true)
                    ->color('primary'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(EnrollmentStatus::class),
                SelectFilter::make('course_id')
                    ->label('Formation')
                    ->options(fn () => Course::orderBy('title')->pluck('title', 'id')),
            ]);
    }
}
