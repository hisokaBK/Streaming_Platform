<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Resources\VideoResource;
use App\Http\Requests\Video\StoreVideoRequest;

use App\Models\Video;
use App\Models\Stream;
use App\Models\Category;


class VideoController extends Controller
{
    public function index(): JsonResponse
    {
        $videos = Video::with([
                'user.profile',
                'stream.reactions.user.profile',
                'categories'
            ])
            ->withCount(['comments'])
            ->latest()
            ->paginate(10);

        $videos->getCollection()->each(function ($video) {
            $video->stream?->loadCount('reactions');
        });

        if ($videos->isEmpty()) {
            return response()->json([
                'message' => 'No videos found.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Videos retrieved successfully.',
            'data' => VideoResource::collection($videos),
            'meta' => [
                'current_page' => $videos->currentPage(),
                'last_page' => $videos->lastPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
            ],
        ]);
    }

    public function show($id): JsonResponse
    {
        $video = Video::with([
                'user.profile',
                'stream.reactions.user.profile',
                'categories',
                'comments.user.profile',
            ])
            ->withCount('comments')
            ->find($id);

        if (!$video) {
            return response()->json([
                'message' => 'Video not found.',
            ], 404);
        }

        $video->stream?->loadCount('reactions');

        return response()->json([
            'message' => 'Video retrieved successfully.',
            'data' => new VideoResource($video),
        ]);
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

    public function filterByCategory(Category $category): JsonResponse
    {
        $videos = Video::with([
                'user.profile',
                'stream.reactions.user.profile',
                'categories',
            ])
            ->withCount(['comments'])
            ->whereHas('categories', function ($query) use ($category) {
                $query->where('categories.id', $category->id);
            })
            ->latest()
            ->paginate(10);

        $videos->getCollection()->each(function ($video) {
            $video->stream?->loadCount('reactions');
        });

        if ($videos->isEmpty()) {
            return response()->json([
                'message' => 'No videos found for this category.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Videos filtered by category successfully.',
            'data' => VideoResource::collection($videos),
            'meta' => [
                'current_page' => $videos->currentPage(),
                'last_page' => $videos->lastPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
            ],
        ]);
    }
}
