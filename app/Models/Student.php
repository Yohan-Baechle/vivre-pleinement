<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Notifications\StudentResetPassword;
use App\Notifications\StudentVerifyEmail;
use Database\Factories\StudentFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Student extends Authenticatable implements MustVerifyEmail
{
    use Billable;

    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'anonymized_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Formations auxquelles l'élève a accès (inscription active).
     *
     * @return BelongsToMany<Course, $this>
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot(['status', 'purchased_at'])
            ->wherePivot('status', EnrollmentStatus::Active->value)
            ->withTimestamps();
    }

    /**
     * @return HasMany<LessonProgress, $this>
     */
    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Détermine si l'élève possède une inscription active à la formation.
     */
    public function hasAccessTo(Course $course): bool
    {
        return $this->enrollments()
            ->where('course_id', $course->id)
            ->where('status', EnrollmentStatus::Active)
            ->exists();
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    /**
     * Utilise le broker et la route de réinitialisation propres à l'espace élève.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new StudentResetPassword($token));
    }

    /**
     * Utilise la notification et la route de vérification propres à l'espace élève.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new StudentVerifyEmail);
    }

    /**
     * @param  Builder<Student>  $query
     */
    public function scopeNotAnonymized(Builder $query): void
    {
        $query->whereNull('anonymized_at');
    }
}
