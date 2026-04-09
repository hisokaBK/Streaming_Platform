<?php

namespace Database\Factories;

use App\Models\Stream;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Stream>
 */
class StreamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),

            'status' => 'live',
            'stream_key' => Str::random(32),

            'started_at' => now(),
            'ended_at' => null,
        ];
    }
}
