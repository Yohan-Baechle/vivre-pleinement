<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Support\SiteContact;
use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'post_id',
    'parent_id',
    'author_name',
    'author_email',
    'author_url',
    'author_ip',
    'content',
    'status',
    'posted_at',
])]
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory;

    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'posted_at' => 'datetime',
        ];
    }

    /**
     * Indique si le commentaire émane de l'auteure du site (par adresse e-mail).
     */
    public function isFromAuthor(): bool
    {
        $authorEmail = SiteContact::email();

        return filled($this->author_email)
            && mb_strtolower(trim($this->author_email)) === mb_strtolower(trim($authorEmail));
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * @return BelongsTo<Comment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
