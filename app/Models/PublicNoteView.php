<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicNoteView extends Model
{
    protected $fillable = [
        'note_id',
        'viewer_key',
    ];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }
}
