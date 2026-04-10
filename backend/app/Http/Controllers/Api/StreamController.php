<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Stream;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StreamResource;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Stream\StoreStreamRequest;
use App\Http\Requests\Stream\UpdateStreamRequest;

class StreamController extends Controller
{
    public function index()
    {
        $streams = Stream::with(['user', 'categories'])
            ->withCount(['comments', 'reactions'])
            ->latest()
            ->paginate(10);

        return StreamResource::collection($streams);
    }

    public function store(StoreStreamRequest $request): JsonResponse
    {
        $stream = DB::transaction(function () use ($request) {
            $status = $request->status ?? 'live';

            $stream = Stream::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'status' => $status,
                'stream_key' => Str::random(32),
                'started_at' => now(),
                'ended_at' => null,
            ]);

            if ($request->filled('category_ids')) {
                $stream->categories()->sync($request->category_ids);
            }

            return $stream->load(['user', 'categories'])
                ->loadCount(['comments', 'reactions']);
        });

        return response()->json([
            'message' => 'Stream created successfully.',
            'data' => new StreamResource($stream),
        ], 201);
    }

    public function show(Stream $stream): JsonResponse
    {
        $stream->load(['user', 'categories'])
            ->loadCount(['comments', 'reactions']);

        return response()->json([
            'data' => new StreamResource($stream),
        ]);
    }



}
