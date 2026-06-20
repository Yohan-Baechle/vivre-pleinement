<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case NotRequired = 'not_required';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Unpaid => 'En attente',
            self::Paid => 'Payé',
            self::NotRequired => 'Gratuit',
            self::Refunded => 'Remboursé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Unpaid => 'warning',
            self::Paid => 'success',
            self::NotRequired => 'gray',
            self::Refunded => 'info',
        };
    }
}
