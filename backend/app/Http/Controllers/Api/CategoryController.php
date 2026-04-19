<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::latest()->get();

        return response()->json([
            'message' => 'Categories retrieved successfully',
            'data' => [
                'categories' => CategoryResource::collection($categories),
            ],
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {

        $category = Category::create($request->validated());

        return response()->json([
            'message' => 'Category created successfully',
            'data' => [
                'category' => new CategoryResource($category),
            ],
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {

        $category->update($request->validated());

        return response()->json([
            'message' => 'Category updated successfully',
            'data' => [
                'category' => new CategoryResource($category),
            ],
        ]);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }


}
