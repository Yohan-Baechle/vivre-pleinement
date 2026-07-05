<?php

namespace App\Filament\Admin\Resources\Enrollments\Tables;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\CoursePaymentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
            ])
            ->recordActions([
                Action::make('markRefunded')
                    ->label('Marquer remboursé')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Enrollment $record) => $record->status === EnrollmentStatus::Active)
                    ->requiresConfirmation()
                    ->modalHeading('Marquer comme remboursé')
                    ->modalDescription("L'élève perd immédiatement l'accès à la formation. Le remboursement lui-même doit être émis depuis le dashboard Stripe (il déclenche aussi cette révocation automatiquement via le webhook charge.refunded).")
                    ->action(function (Enrollment $record) {
                        app(CoursePaymentService::class)->refund($record);

                        Notification::make()->success()->title('Accès révoqué')->send();
                    }),
            ]);
    }
}
