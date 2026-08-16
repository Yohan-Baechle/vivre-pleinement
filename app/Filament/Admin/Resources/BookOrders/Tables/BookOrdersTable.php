<?php

namespace App\Filament\Admin\Resources\BookOrders\Tables;

use App\Enums\BookOrderStatus;
use App\Mail\BookOrderConfirmation;
use App\Models\BookOrder;
use App\Models\Product;
use App\Services\BookPaymentService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class BookOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->emptyStateIcon(Heroicon::OutlinedBookOpen)
            ->emptyStateHeading('Aucune commande')
            ->emptyStateDescription('Les achats du livre apparaîtront ici, '
                .'avec le lien vers le paiement Stripe correspondant.')
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('customer_last_name')
                    ->label('Client')
                    ->searchable(['customer_first_name', 'customer_last_name', 'customer_email'])
                    ->formatStateUsing(fn (BookOrder $record): string => $record->customerName())
                    ->description(fn (BookOrder $record): string => $record->customer_email),
                TextColumn::make('product.name')
                    ->label('Formule')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('amount_cents')
                    ->label('Montant')
                    ->money('eur', divideBy: 100)
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total encaissé')
                            ->money('eur', divideBy: 100)
                    ),
                IconColumn::make('coaching_appointment_id')
                    ->label('Coaching réservé')
                    ->boolean()
                    ->placeholder('–')
                    ->visible(fn (): bool => Product::query()->where('slug', 'livre-coaching')->exists()),
                TextColumn::make('paid_at')
                    ->label('Payée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('–'),
                TextColumn::make('stripe_payment_intent_id')
                    ->label('Paiement')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Ouvrir dans Stripe' : '–')
                    ->url(fn (BookOrder $record): ?string => $record->stripe_payment_intent_id
                        ? 'https://dashboard.stripe.com/payments/'.$record->stripe_payment_intent_id
                        : null, shouldOpenInNewTab: true)
                    ->color('primary'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(BookOrderStatus::class),
                SelectFilter::make('product_id')
                    ->label('Formule')
                    ->options(fn () => Product::orderBy('name')->pluck('name', 'id')),
            ])
            ->recordActions([
                Action::make('resendConfirmation')
                    ->label('Renvoyer le lien')
                    ->icon('heroicon-o-envelope')
                    ->visible(fn (BookOrder $record): bool => $record->isPaid())
                    ->requiresConfirmation()
                    ->modalHeading('Renvoyer le lien de téléchargement')
                    ->modalDescription('Le client recevra à nouveau son email de confirmation, avec le lien de téléchargement et, le cas échéant, celui de réservation du coaching.')
                    ->action(function (BookOrder $record): void {
                        Mail::to($record->customer_email)
                            ->send(new BookOrderConfirmation($record->load('product')));

                        Notification::make()->success()->title('Email renvoyé')->send();
                    }),

                Action::make('markRefunded')
                    ->label('Marquer remboursée')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (BookOrder $record): bool => $record->isPaid())
                    ->requiresConfirmation()
                    ->modalHeading('Marquer comme remboursée')
                    ->modalDescription('Le lien de téléchargement cesse immédiatement de fonctionner. Le remboursement lui-même doit être émis depuis le dashboard Stripe (il déclenche aussi cette révocation automatiquement via le webhook charge.refunded).')
                    ->action(function (BookOrder $record): void {
                        app(BookPaymentService::class)->refund($record);

                        Notification::make()->success()->title('Téléchargement révoqué')->send();
                    }),
            ]);
    }
}
