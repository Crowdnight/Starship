<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportConversation extends Model
{
    public const CATEGORIES = [
        'suggestion' => 'Saran',
        'bug' => 'Laporan Bug',
        'question' => 'Pertanyaan',
        'other' => 'Lainnya',
    ];

    protected $fillable = [
        'user_id',
        'subject',
        'category',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['other'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class);
    }
}
