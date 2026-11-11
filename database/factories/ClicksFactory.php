<?php

namespace Database\Factories;

use App\Models\Model;
use App\Models\ShortLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class ClicksFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'short_link_id' => ShortLink::factory(),
            'referrer'      => 'https://new.com',
            'user_agent'    => 'TestAgent/1.0',
            'ip'            => '127.0.0.1',
        ];
    }
}
