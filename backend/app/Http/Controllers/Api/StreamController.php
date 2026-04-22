<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stream\StoreStreamRequest;
use App\Http\Requests\Stream\UpdateStreamRequest;
use App\Http\Resources\StreamResource;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Stream;
use App\Models\Subscription;
use App\Models\Video;
use App\Services\LiveKitService;
use App\Services\RecordingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StreamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $categoryId = $request->query('category_id');

        $streams = Stream::with($this->baseRelations())
            ->withCount(['comments', 'reactions'])
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $allowedStatuses = ['live', 'ended'];

                if (in_array($status, $allowedStatuses, true)) {
                    $query->where('status', $status);
                }
            })
            ->when($categoryId && $categoryId !== 'all', function ($query) use ($categoryId) {
                $query->whereHas('categories', function ($categoryQuery) use ($categoryId) {
                    $categoryQuery->where('categories.id', (int) $categoryId);
                });
            })
            ->latest()
            ->paginate(10);

        if ($streams->isEmpty()) {
            return response()->json([
                'message' => 'No streams found.',
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
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

    public function filterByCategory(Request $request, Category $category): JsonResponse
    {
        $status = $request->query('status');

        $streams = Stream::with($this->baseRelations())
            ->withCount(['comments', 'reactions'])
            ->whereHas('categories', function ($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $allowedStatuses = ['live', 'ended'];

                if (in_array($status, $allowedStatuses, true)) {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(10);

        if ($streams->isEmpty()) {
            return response()->json([
                'message' => 'No streams found for this category.',
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
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

    public function show($id): JsonResponse
    {
        $stream = Stream::with($this->detailsRelations())
            ->withCount(['comments', 'reactions'])
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

    public function store(StoreStreamRequest $request, RecordingService $recordingService): JsonResponse
    {
        $stream = DB::transaction(function () use ($request) {
            $status = $request->input('status', 'live');

            $thumbnail = $this->resolveThumbnail($request);

            $stream = Stream::create([
                'user_id' => auth()->id(),
                'title' => $request->title,
                'description' => $request->description,
                'status' => $status,
                'stream_key' => Str::random(32),
                'room_name' => 'stream-' . Str::uuid(),
                'thumbnail' => $thumbnail,
                'started_at' => $status === 'live' ? now() : null,
                'ended_at' => null,
                'current_viewers' => 0,
                'recording_status' => $status === 'live' ? 'starting' : null,
            ]);

            if ($request->filled('category_ids')) {
                $stream->categories()->sync($request->category_ids);
            }

            return $stream;
        });

        try {
            if ($stream->status === 'live') {
                $recordingService->ensureRoomExists($stream);

                $recording = $recordingService->startParticipantRecording($stream, auth()->user());

                $stream->update([
                    'recording_egress_id' => $recording['egress_id'],
                    'recording_status' => 'recording',
                    'recording_started_at' => now(),
                ]);

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
        } catch (\Throwable $e) {
            DB::transaction(function () use ($stream) {
                $stream->categories()->detach();
                $stream->delete();
            });

            return response()->json([
                'message' => 'Stream creation failed because recording could not start.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $stream = $this->loadBaseData($stream->fresh());

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
            $newStatus = $request->input('status', $stream->status);

            $data = [
                'title' => $request->has('title') ? $request->title : $stream->title,
                'description' => $request->has('description') ? $request->description : $stream->description,
                'status' => $newStatus,
                'thumbnail' => $this->resolveThumbnail($request, $stream->thumbnail),
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

        $stream = $this->loadBaseData($stream);

        return response()->json([
            'message' => 'Stream updated successfully.',
            'data' => new StreamResource($stream),
        ]);
    }

    public function edit(Stream $stream): JsonResponse
    {
        $stream = $this->loadBaseData($stream);

        return response()->json([
            'message' => 'Stream edit data retrieved successfully.',
            'data' => new StreamResource($stream),
        ]);
    }

    public function end(Stream $stream, RecordingService $recordingService): JsonResponse
    {
        if ($stream->status === 'ended') {
            return response()->json([
                'message' => 'Stream is already ended.',
            ], 422);
        }

        $egressInfo = null;
        $recordingError = null;

        if ($stream->recording_egress_id) {
            try {
                $egressInfo = $recordingService->stopRecording($stream->recording_egress_id);
            } catch (\Throwable $e) {
                $recordingError = $e->getMessage();

                \Log::error('Failed to stop recording while ending stream.', [
                    'stream_id' => $stream->id,
                    'egress_id' => $stream->recording_egress_id,
                    'error' => $recordingError,
                ]);
            }
        }

        try {
            $stream = DB::transaction(function () use ($stream, $recordingService, $egressInfo, $recordingError) {
                $stream->load('categories');

                $stream->update([
                    'status' => 'ended',
                    'ended_at' => now(),
                    'recording_status' => $recordingError ? 'failed' : 'completed',
                    'recording_ended_at' => now(),
                ]);

                $video = Video::updateOrCreate(
                    [
                        'stream_id' => $stream->id,
                    ],
                    [
                        'user_id' => $stream->user_id,
                        'egress_id' => $stream->recording_egress_id,
                        'title' => $stream->title,
                        'description' => $stream->description,
                        'url' => $recordingService->publicRecordingUrl($stream),
                        'duration' => $egressInfo ? $recordingService->extractDurationSeconds($egressInfo) : 0,
                        'recording_status' => $recordingError ? 'failed' : 'completed',
                        'size_bytes' => $egressInfo ? $recordingService->extractSizeBytes($egressInfo) : null,
                        'recorded_at' => now(),
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

                return $stream->fresh();
            });

            $stream = $this->loadBaseData($stream);

            return response()->json([
                'message' => $recordingError
                    ? 'Stream ended, but recording stop returned an error.'
                    : 'Stream ended successfully and replay video created.',
                'data' => new StreamResource($stream),
                'recording_error' => $recordingError,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to end stream.', [
                'stream_id' => $stream->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to end stream.',
                'error' => $e->getMessage(),
            ], 500);
        }
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

    private function baseRelations(): array
    {
        return [
            'user.profile',
            'categories',
            'reactions.user.profile',
        ];
    }

    private function detailsRelations(): array
    {
        return [
            'user.profile',
            'categories',
            'comments.user.profile',
            'reactions.user.profile',
        ];
    }

    private function loadBaseData(Stream $stream): Stream
    {
        return $stream->load($this->baseRelations())
            ->loadCount(['comments', 'reactions']);
    }

    private function resolveThumbnail(Request $request, ?string $currentThumbnail = null): ?string
    {
        if ($request->hasFile('thumbnail')) {
            $this->deleteThumbnailIfLocal($currentThumbnail);

            $storedPath = $request->file('thumbnail')->store('streams/thumbnails', 'public');

            return asset('storage/' . $storedPath);
        }

        if ($request->has('thumbnail')) {
            $thumbnail = $request->input('thumbnail');

            if ($thumbnail === null || $thumbnail === '') {
                $this->deleteThumbnailIfLocal($currentThumbnail);
                return null;
            }

            if (Str::startsWith($thumbnail, ['http://', 'https://'])) {
                return $thumbnail;
            }

            return asset('storage/' . ltrim($thumbnail, '/'));
        }

        return $currentThumbnail;
    }

    private function deleteThumbnailIfLocal(?string $thumbnailUrl): void
    {
        $storagePath = $this->extractStoragePathFromUrl($thumbnailUrl);

        if ($storagePath && Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }
    }

    private function extractStoragePathFromUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (!Str::startsWith($url, ['http://', 'https://'])) {
            return ltrim($url, '/');
        }

        $parsedPath = parse_url($url, PHP_URL_PATH);

        if (!$parsedPath) {
            return null;
        }

        $parsedPath = ltrim($parsedPath, '/');

        if (Str::startsWith($parsedPath, 'storage/')) {
            return Str::after($parsedPath, 'storage/');
        }

        return null;
    }
}
