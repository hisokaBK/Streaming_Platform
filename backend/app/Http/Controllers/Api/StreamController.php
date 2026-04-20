<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\StreamResource;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Stream\StoreStreamRequest;
use App\Http\Requests\Stream\UpdateStreamRequest;
use App\Services\LiveKitService;
use App\Models\Stream;
use App\Models\Subscription;
use App\Models\Notification;
use App\Models\Category;
use App\Models\Video;
use App\Models\Comment;

class StreamController extends Controller
{
    public function index(): JsonResponse
    {
        $streams = Stream::with([
                'user.profile',
                'categories',
                'reactions.user.profile',
            ])
            ->withCount(['comments', 'reactions'])
            ->latest()
            ->paginate(10);

        if ($streams->isEmpty()) {
            return response()->json([
                'message' => 'No streams found.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Streams retrieved successfully.',
            'data' => StreamResource::collection($streams),
            'meta' => [
                'current_page' => $streams->currentPage(),
                'last_page' => $streams->lastPage(),
                'per_page' => $streams->perPage(),
                'total' => $streams->total(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $stream = Stream::with([
                'user.profile',
                'categories',
                'comments.user.profile',
                'reactions.user.profile',
            ])
            ->withCount([
                'comments',
                'reactions',
            ])
            ->find($id);

        if (!$stream) {
            return response()->json([
                'message' => 'Stream not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Stream retrieved successfully.',
            'data' => new StreamResource($stream),
        ]);
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
                'room_name' => 'stream-' . Str::uuid(),
                'thumbnail' => null,
                'started_at' => now(),
                'ended_at' => null,
                'current_viewers' => 0,
            ]);

            if ($request->filled('category_ids')) {
                $stream->categories()->sync($request->category_ids);
            }

            if ($status === 'live') {
                $followerIds = Subscription::where('streamer_id', auth()->id())
                    ->pluck('subscriber_id');

                foreach ($followerIds as $followerId) {
                    Notification::create([
                        'user_id' => $followerId,
                        'type' => 'stream_live',
                        'actor_user_id' => auth()->id(),
                        'stream_id' => $stream->id,
                        'content' => auth()->user()->name . ' started a new live stream: ' . $stream->title,
                        'is_read' => false,
                    ]);
                }
            }

            return $stream->load([
                'user.profile',
                'categories',
                'reactions.user.profile',
            ])->loadCount([
                'comments',
                'reactions',
            ]);
        });

        return response()->json([
            'message' => 'Stream created successfully.',
            'data' => new StreamResource($stream),
        ], 201);
    }

    public function update(UpdateStreamRequest $request, Stream $stream): JsonResponse
    {
        if ($stream->status === 'ended') {
            return response()->json([
                'message' => 'Ended stream cannot be updated.',
            ], 422);
        }

        DB::transaction(function () use ($request, $stream) {
            $newStatus = $request->status ?? $stream->status;

            $data = [
                'title' => $request->has('title') ? $request->title : $stream->title,
                'description' => $request->has('description') ? $request->description : $stream->description,
                'status' => $newStatus,
                'thumbnail' => $request->has('thumbnail') ? $request->thumbnail : $stream->thumbnail,
            ];

            if ($stream->status !== 'live' && $newStatus === 'live') {
                $data['started_at'] = now();
            }

            if ($newStatus === 'ended' && is_null($stream->ended_at)) {
                $data['ended_at'] = now();
            }

            $stream->update($data);

            if ($request->has('category_ids')) {
                $stream->categories()->sync($request->category_ids ?? []);
            }
        });

        $stream->load([
                'user.profile',
                'categories',
                'reactions.user.profile',
            ])
            ->loadCount([
                'comments',
                'reactions',
            ]);

        return response()->json([
            'message' => 'Stream updated successfully.',
            'data' => new StreamResource($stream),
        ]);
    }

    public function edit(Stream $stream): JsonResponse
    {
        $stream->load([
            'user.profile',
            'categories',
            'reactions.user.profile',
        ])->loadCount([
            'comments',
            'reactions',
        ]);

        return response()->json([
            'message' => 'Stream edit data retrieved successfully.',
            'data' => new StreamResource($stream),
        ]);
    }

    public function end(Stream $stream): JsonResponse
    {
        if ($stream->status === 'ended') {
            return response()->json([
                'message' => 'Stream is already ended.',
            ], 422);
        }

        $stream = DB::transaction(function () use ($stream) {
            $stream->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);

            $stream->load([
                'user.profile',
                'categories',
                'reactions.user.profile',
            ]);

            $video = Video::firstOrCreate(
                [
                    'stream_id' => $stream->id,
                ],
                [
                    'user_id' => $stream->user_id,
                    'title' => $stream->title,
                    'description' => $stream->description,
                    'url' => '',
                    'duration' => 0,
                ]
            );

            if ($stream->categories->isNotEmpty()) {
                $video->categories()->sync(
                    $stream->categories->pluck('id')->toArray()
                );
            }

            Comment::where('stream_id', $stream->id)
                ->whereNull('video_id')
                ->update([
                    'video_id' => $video->id,
                ]);

            return $stream->fresh()
                ->load([
                    'user.profile',
                    'categories',
                    'reactions.user.profile',
                ])
                ->loadCount([
                    'comments',
                    'reactions',
                ]);
        });

        return response()->json([
            'message' => 'Stream ended successfully and replay video created.',
            'data' => new StreamResource($stream),
        ]);
    }

    public function filterByCategory(Category $category): JsonResponse
    {
        $streams = Stream::with([
                'user.profile',
                'categories',
                'reactions.user.profile',
            ])
            ->withCount(['comments', 'reactions'])
            ->whereHas('categories', function ($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->latest()
            ->paginate(10);

        if ($streams->isEmpty()) {
            return response()->json([
                'message' => 'No streams found for this category.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Streams filtered by category successfully.',
            'data' => StreamResource::collection($streams),
            'meta' => [
                'current_page' => $streams->currentPage(),
                'last_page' => $streams->lastPage(),
                'per_page' => $streams->perPage(),
                'total' => $streams->total(),
            ],
        ]);
    }

    public function studioToken(Stream $stream, LiveKitService $liveKitService): JsonResponse
    {
        if ((int) auth()->id() !== (int) $stream->user_id) {
            return response()->json([
                'message' => 'Unauthorized. You are not the owner of this stream.',
            ], 403);
        }

        if (!$stream->room_name) {
            return response()->json([
                'message' => 'Stream room is not configured.',
            ], 422);
        }

        $token = $liveKitService->createBroadcasterToken($stream, auth()->user());

        return response()->json([
            'message' => 'Broadcaster token generated successfully.',
            'data' => [
                'token' => $token,
                'room_name' => $stream->room_name,
                'url' => env('LIVEKIT_URL'),
            ],
        ]);
    }

    public function viewerToken(Stream $stream, LiveKitService $liveKitService): JsonResponse
    {
        if (!$stream->room_name) {
            return response()->json([
                'message' => 'Stream room is not configured.',
            ], 422);
        }

        $token = $liveKitService->createViewerToken($stream, auth()->user());

        return response()->json([
            'message' => 'Viewer token generated successfully.',
            'data' => [
                'token' => $token,
                'room_name' => $stream->room_name,
                'url' => env('LIVEKIT_URL'),
            ],
        ]);
    }
}
