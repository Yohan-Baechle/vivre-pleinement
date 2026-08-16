<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum BookOrderStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'En attente de paiement',
            self::Paid => 'Payée',
            self::Refunded => 'Remboursée',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Refunded => 'info',
        };
    }

    /**
     * Seule une commande payée donne droit au téléchargement du livre et à la
     * séance de coaching.
     */
    public function grantsAccess(): bool
    {
        return $this === self::Paid;
    }
}
