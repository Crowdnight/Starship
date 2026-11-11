<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $casts = [
        'edited_at' => 'datetime',
    ];

    protected $fillable = [
        'note_id',
        'user_id',
        'parent_comment_id',
        'content',
    ];

    public function wasEdited(): bool
    {
        return $this->edited_at !== null
            || ($this->updated_at && $this->created_at && $this->updated_at->gt($this->created_at));
    }

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_comment_id');
    }
}
