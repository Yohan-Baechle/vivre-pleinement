<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EnrollmentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Active = 'active';
    case Refunded = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => 'En attente de paiement',
            self::Active => 'Actif',
            self::Refunded => 'Remboursé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'success',
            self::Refunded => 'info',
        };
    }
}
