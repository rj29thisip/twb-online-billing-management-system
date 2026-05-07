<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'is_published',
        'created_by',
        'publish_from',
        'publish_to',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'publish_from' => 'date',
        'publish_to'   => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
