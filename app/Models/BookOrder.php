<?php

namespace App\Models;

use App\Enums\BookOrderStatus;
use Database\Factories\BookOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

#[Fillable([
    'reference',
    'token',
    'product_id',
    'customer_first_name',
    'customer_last_name',
    'customer_email',
    'amount_cents',
    'currency',
    'status',
    'stripe_payment_intent_id',
    'paid_at',
    'refunded_at',
    'coaching_appointment_id',
])]
class BookOrder extends Model
{
    /** @use HasFactory<BookOrderFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'currency' => 'EUR',
        'status' => 'pending',
    ];

    protected static function booted(): void
    {
        static::creating(function (BookOrder $order): void {
            $order->reference ??= self::generateReference();
            $order->token ??= self::generateToken();
        });
    }

    protected function casts(): array
    {
        return [
            'status' => BookOrderStatus::class,
            'amount_cents' => 'integer',
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    /**
     * Référence lisible, communiquée au client. Ne sert jamais de clé d'accès
     * à une URL publique — c'est le rôle de `generateToken()`.
     */
    public static function generateReference(): string
    {
        return 'LIV-'.Str::upper(Str::random(8));
    }

    /**
     * Secret d'accès aux pages publiques de la commande (paiement,
     * confirmation, téléchargement, réservation du coaching).
     */
    public static function generateToken(): string
    {
        return Str::random(48);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function coachingAppointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'coaching_appointment_id');
    }

    /**
     * Durée de validité d'un lien de téléchargement. Assez long pour qu'un
     * acheteur retrouve son email des semaines plus tard, assez court pour
     * qu'un lien ayant fuité cesse de circuler.
     */
    public const DOWNLOAD_LINK_DAYS = 30;

    public function customerName(): string
    {
        return trim($this->customer_first_name.' '.$this->customer_last_name);
    }

    /**
     * URL de téléchargement signée et datée. Régénérée à chaque affichage :
     * la page de remerciement et le renvoi d'email produisent toujours un
     * lien frais, un lien expiré ne condamne donc jamais un achat.
     */
    public function downloadUrl(): string
    {
        return URL::temporarySignedRoute(
            'book.download',
            now()->addDays(self::DOWNLOAD_LINK_DAYS),
            ['order' => $this->token],
        );
    }

    public function isPaid(): bool
    {
        return $this->status->grantsAccess();
    }

    /**
     * La formule accompagnée est celle qui embarque une heure de coaching. On
     * la reconnaît au slug du produit, seul contrat stable entre le catalogue
     * et le tunnel d'achat.
     */
    public function includesCoaching(): bool
    {
        return $this->product?->slug === 'livre-coaching';
    }

    /**
     * Le lien de réservation envoyé par email est à usage unique : il ne vaut
     * que pour une commande payée, avec coaching, pas encore consommée.
     */
    public function canBookCoaching(): bool
    {
        return $this->isPaid()
            && $this->includesCoaching()
            && $this->coaching_appointment_id === null;
    }
}
