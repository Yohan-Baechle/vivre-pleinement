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
])]
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

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
     * Rendez-vous qui occupent un créneau (pour détecter la double-réservation).
     *
     * @param  Builder<Appointment>  $query
     */
    public function scopeBlocking(Builder $query): void
    {
        $query->whereIn('status', AppointmentStatus::blocking());
    }

    public static function generateReference(): string
    {
        return 'RDV-'.Str::upper(Str::random(8));
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    /**
     * Indique si le client peut encore gérer (annuler/reprogrammer) ce rendez-vous.
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
