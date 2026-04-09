<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Message;
use App\Models\Reaction;
use App\Models\Stream;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory(10)->create();

        $users->each(function ($user): void {
            $user->profile()->create([
                'avatar' => null,
                'background_image' => null,
                'bio' => fake()->sentence(),
            ]);
        });

        $categories = Category::factory(5)->create();

        $streams = Stream::factory(15)->make()->each(function ($stream) use ($users, $categories): void {
            $stream->user_id = $users->random()->id;
            $stream->save();

            $stream->categories()->attach(
                $categories->random(rand(1, 2))->pluck('id')->toArray()
            );
        });

        $videos = $streams->map(function ($stream) use ($categories) {
            $video = Video::factory()->create([
                'user_id' => $stream->user_id,
                'stream_id' => $stream->id,
            ]);

            if (method_exists($video, 'categories')) {
                $video->categories()->attach(
                    $categories->random(rand(1, 2))->pluck('id')->toArray()
                );
            }

            return $video;
        });

        foreach ($videos as $video) {
            Comment::factory(3)->create([
                'user_id' => $video->user_id,
                'video_id' => $video->id,
            ]);
        }

        Message::factory(20)->make()->each(function ($message) use ($users): void {
            $sender = $users->random()->id;
            $receiver = $users->where('id', '!=', $sender)->random()->id;

            $message->sender_id = $sender;
            $message->receiver_id = $receiver;
            $message->save();
        });

        foreach ($streams as $stream) {
            $randomUsers = $users->random(rand(1, 5));

            foreach ($randomUsers as $user) {
                Reaction::factory()->create([
                    'user_id' => $user->id,
                    'stream_id' => $stream->id,
                ]);
            }
        }

        foreach (range(1, 20) as $i) {
            $subscriber = $users->random()->id;
            $streamer = $users->where('id', '!=', $subscriber)->random()->id;

            Subscription::firstOrCreate([
                'subscriber_id' => $subscriber,
                'streamer_id' => $streamer,
            ]);
        }
    }
}
