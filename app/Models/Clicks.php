<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clicks extends Model
{
    use HasFactory;

    protected $table = 'clicks';

    protected $fillable = [
        'short_link_id',
        'referrer',
        'user_agent',
        'ip',
    ];

    public function shortLink(): BelongsTo
    {
        return $this->belongsTo(ShortLink::class);
    }
}
