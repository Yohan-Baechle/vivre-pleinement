<?php

namespace App\Models;

use App\Enums\AppointmentChannel;
use App\Enums\AppointmentStatus;
use App\Enums\PaymentStatus;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'appointment_service_id',
    'reference',
    'token',
    'customer_first_name',
    'customer_last_name',
    'customer_email',
    'customer_phone',
    'channel',
    'notes',
    'meeting_url',
    'starts_at',
    'ends_at',
    'status',
    'price_cents',
    'payment_status',
    'cancelled_at',
    'reminded_24h_at',
    'reminded_1h_at',
    'followed_up_at',
    'stripe_payment_intent_id',
])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'confirmed',
        'price_cents' => 0,
        'payment_status' => 'unpaid',
        'channel' => 'video',
    ];

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment): void {
            $appointment->reference ??= self::generateReference();
            $appointment->token ??= self::generateToken();
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => AppointmentStatus::class,
            'channel' => AppointmentChannel::class,
            'payment_status' => PaymentStatus::class,
            'price_cents' => 'integer',
            'cancelled_at' => 'datetime',
            'reminded_24h_at' => 'datetime',
            'reminded_1h_at' => 'datetime',
            'followed_up_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AppointmentService, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(AppointmentService::class, 'appointment_service_id');
    }

    /**
     * Rendez-vous qui occupent un créneau (pour détecter la
     * double-réservation).
     *
     * @param  Builder<Appointment>  $query
     */
    public function scopeBlocking(Builder $query): void
    {
        $query->whereIn('status', AppointmentStatus::blocking());
    }

    /**
     * Référence lisible affichée au client et reprise dans les e-mails.
     *
     * Purement descriptive : le `Str::upper` réduit l'alphabet de 62 à 36
     * valeurs avec une distribution biaisée, elle ne doit donc jamais servir de
     * clé d'accès à une URL publique — c'est le rôle de `generateToken()`.
     */
    public static function generateReference(): string
    {
        return 'RDV-'.Str::upper(Str::random(8));
    }

    /**
     * Secret d'accès aux pages publiques du rendez-vous (confirmation, .ics,
     * paiement, gestion). C'est la seule valeur qui autorise l'accès.
     */
    public static function generateToken(): string
    {
        return Str::random(48);
    }

    /**
     * Indique si le client peut encore gérer (annuler/reprogrammer) ce
     * rendez-vous.
     */
    public function isManageable(): bool
    {
        return $this->status->isCancellable()
            && $this->starts_at->isFuture();
    }

    public function isPending(): bool
    {
        return $this->status === AppointmentStatus::Pending;
    }

    protected function customerFullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->customer_first_name.' '.($this->customer_last_name ?? '')),
        );
    }
}
