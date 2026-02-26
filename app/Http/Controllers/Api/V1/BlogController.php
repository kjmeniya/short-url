<?php

/**
 * BlogController.php
 * Created by Antigravity on 2025-12-20
 */

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends BaseApiController
{
    /**
     * Display a listing of published blogs.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $blogs = Blog::published()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'featured_image',
                'is_featured',
                'published_at',
                'views_count',
                'reading_time',
                'author_id'
            ])
            ->with(['author:id,name,avatar'])
            ->orderBy('published_at', 'desc')
            ->paginate($limit);

        return $this->paginatedResponse($blogs, 'Blogs retrieved successfully');
    }

    /**
     * Display the specified blog by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $blog = Blog::published()
            ->with(['author:id,name,avatar', 'creator:id,name'])
            ->where('slug', $slug)
            ->first();

        if (!$blog) {
            return $this->notFoundResponse('Blog post not found');
        }

        // Increment views count
        $blog->incrementViews();

        // Get related blogs
        $relatedBlogs = $blog->getRelatedPosts(3);
        // Add related blogs to the response
        $response = $blog->toArray();
        $response['related_blogs'] = $relatedBlogs ? $relatedBlogs->toArray() : [];

        return $this->successResponse($response, 'Blog details retrieved successfully');
    }

    /**
     * Display a listing of featured blogs.
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);

        $blogs = Blog::published()
            ->featured()
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'featured_image',
                'is_featured',
                'published_at',
                'views_count',
                'reading_time',
                'author_id'
            ])
            ->with(['author:id,name,avatar'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();

        return $this->successResponse($blogs, 'Featured blogs retrieved successfully');
    }

    /**
     * Search for blogs by keyword.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);

        $search = $request->get('q');
        $limit = $request->get('limit', 10);

        $blogs = Blog::published()
            ->search($search)
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'featured_image',
                'is_featured',
                'published_at',
                'views_count',
                'reading_time',
                'author_id'
            ])
            ->with(['author:id,name,avatar'])
            ->orderBy('published_at', 'desc')
            ->paginate($limit);

        return $this->paginatedResponse($blogs, 'Search results retrieved successfully');
    }
}
