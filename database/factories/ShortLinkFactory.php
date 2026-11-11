<?php

namespace Database\Factories;

use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShortLink>
 */
class ShortLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'        => Str::random(16),
            'target_url'  => 'https://example.com',
            'user_id'     => User::factory(),
            'is_active'   => true,
            'expires_at'  => now()->addMonth(),
        ];
    }
}
