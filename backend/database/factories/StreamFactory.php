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
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => Str::slug(fake()->unique()->sentence()),
            'description' => fake()->paragraph(),
            'thumbnail' => null,
            'status' => 'ended',
            'visibility' => 'public',
            'stream_key' => Str::random(20),
            'scheduled_at' => now(),
        ];
    }
}
