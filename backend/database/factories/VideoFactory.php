<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
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
            'video_url' => 'videos/test.mp4',
            'duration' => rand(60, 3600),
            'visibility' => 'public',
            'published_at' => now(),
        ];
    }
}
