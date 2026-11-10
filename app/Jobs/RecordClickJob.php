<?php

namespace App\Jobs;

use App\Models\Clicks;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;


class RecordClickJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $shortLinkId,
        public ?string $referrer,
        public ?string $userAgent,
        public ?string $ip,
    ) {

    }

    public function handle(): void
    {
        Clicks::create([
            'short_link_id' => $this->shortLinkId,
            'referrer'      => $this->referrer,
            'user_agent'    => $this->userAgent,
            'ip'            => $this->ip,
        ]);
    }
}
