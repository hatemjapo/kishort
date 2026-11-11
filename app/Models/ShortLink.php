<?php

namespace App\Models;

use App\Jobs\RecordClickJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class ShortLink extends Model
{
    use HasFactory;
    
    /**
     * @var string
     */
    protected $table = 'short_links';

    protected $fillable = [
        'code',
        'target_url',
        'user_id',
        'expires_at',
        'is_active',
        ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    public function clicks(): HasMany
    {
        return $this->hasMany(Clicks::class);
    }

    public function isUsable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function createClick(Request $request): void
    {
        RecordClickJob::dispatch(
            $this->id,
            $request->header('referer'),
            $request->userAgent(),
            $request->ip(),
        );
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
