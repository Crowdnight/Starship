<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Note extends Model
{
    protected $casts = [
        'edited_at' => 'datetime',
    ];

    protected $fillable = [
    'user_id',
    'title',
    'content',
    'category',
    'note_color',
    'is_public',
];

    public function wasEdited(): bool
    {
        return $this->edited_at !== null
            || ($this->updated_at && $this->created_at && $this->updated_at->gt($this->created_at));
    }

    public function getForegroundColorAttribute(): string
    {
        $color = $this->note_color;

        if (! is_string($color) || ! preg_match('/\A#[0-9a-fA-F]{6}\z/', $color)) {
            return '#28243a';
        }

        $red = hexdec(substr($color, 1, 2));
        $green = hexdec(substr($color, 3, 2));
        $blue = hexdec(substr($color, 5, 2));
        $brightness = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;

        return $brightness >= 150 ? '#28243a' : '#ffffff';
    }

public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}

public function rootComments(): HasMany
{
    return $this->comments()->whereNull('parent_comment_id');
}

public function buildCommentTree(): Collection
{
    $comments = $this->comments;
    $commentsById = $comments->keyBy('id');

    foreach ($comments as $comment) {
        $comment->setRelation('replies', new Collection());
    }

    foreach ($comments as $comment) {
        $parent = $commentsById->get($comment->parent_comment_id);

        if ($parent) {
            $parent->getRelation('replies')->push($comment);
        }
    }

    return $comments
        ->filter(fn (Comment $comment): bool => $comment->parent_comment_id === null)
        ->values();
}

public function publicViews(): HasMany
{
    return $this->hasMany(PublicNoteView::class);
}

public function reactions(): HasMany
{
    return $this->hasMany(NoteReaction::class);
}

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
