<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Video;
use App\Models\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Http\Requests\Video\StoreVideoRequest;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::with(['user', 'stream', 'categories'])
            ->withCount(['comments'])
            ->latest()
            ->paginate(10);
    
        $videos->getCollection()->each(function ($video) {
            $video->stream?->loadCount('reactions');
        });
    
        return VideoResource::collection($videos);
    }

    public function show(Video $video): JsonResponse
    {
        $video->load([
            'user',
            'stream',
            'categories',
            'comments.user',
        ])->loadCount('comments');

        $video->stream?->loadCount('reactions');

        return response()->json([
            'data' => new VideoResource($video),
        ]);
    }

    public function store(StoreVideoRequest $request): JsonResponse
    {
        $stream = Stream::with(['categories', 'user'])->findOrFail($request->stream_id);

        if ($stream->status !== 'ended') {
            return response()->json([
                'message' => 'Video can only be created when the stream is ended.'
            ], 422);
        }

        if ((int) $stream->user_id !== (int) auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized. You are not the owner of this stream.'
            ], 403);
        }

        if ($stream->video()->exists()) {
            return response()->json([
                'message' => 'This stream already has a replay video.'
            ], 422);
        }

        $video = DB::transaction(function () use ($request, $stream) {
            $path = $request->file('video')->store('videos', 'public');

            $video = Video::create([
                'user_id' => auth()->id(),
                'stream_id' => $stream->id,
                'title' => $stream->title,
                'description' => $stream->description,
                'url' => asset('storage/' . $path),
                'duration' => $request->duration ?? 0,
            ]);

            $video->categories()->sync(
                $stream->categories->pluck('id')->toArray()
            );

            return $video->load(['user', 'stream', 'categories']);
        });

        return response()->json([
            'message' => 'Replay video created successfully.',
            'data' => new VideoResource($video),
        ], 201);
    }

    public function destroy(Video $video): JsonResponse
    {
        DB::transaction(function () use ($video) {
            $storagePath = str_replace(asset('storage') . '/', '', $video->url);

            if ($storagePath && Storage::disk('public')->exists($storagePath)) {
                Storage::disk('public')->delete($storagePath);
            }

            $video->categories()->detach();
            $video->delete();
        });

        return response()->json([
            'message' => 'Video deleted successfully.',
        ]);
    }
}
