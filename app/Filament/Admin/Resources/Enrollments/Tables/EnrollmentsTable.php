<?php

namespace App\Filament\Admin\Resources\Enrollments\Tables;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\CoursePaymentService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->emptyStateHeading('Aucune vente')
            ->emptyStateDescription('Les achats de formations apparaîtront '
                .'ici, avec le lien vers le paiement Stripe correspondant.')
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
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total encaissé')
                            ->money('eur', divideBy: 100)
                    ),
                TextColumn::make('purchased_at')
                    ->label('Acheté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('–'),
                TextColumn::make('stripe_payment_intent_id')
                    ->label('Paiement')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Ouvrir dans Stripe' : '–')
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
                SelectFilter::make('periode')
                    ->label('Période')
                    ->options([
                        'month' => 'Ce mois-ci',
                        'last_month' => 'Le mois dernier',
                        'year' => 'Cette année',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'month' => $query->whereBetween('purchased_at', [
                            CarbonImmutable::now()->startOfMonth(),
                            CarbonImmutable::now()->endOfMonth(),
                        ]),
                        'last_month' => $query->whereBetween('purchased_at', [
                            CarbonImmutable::now()->subMonth()->startOfMonth(),
                            CarbonImmutable::now()->subMonth()->endOfMonth(),
                        ]),
                        'year' => $query->whereBetween('purchased_at', [
                            CarbonImmutable::now()->startOfYear(),
                            CarbonImmutable::now()->endOfYear(),
                        ]),
                        default => $query,
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir la vente'),

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
