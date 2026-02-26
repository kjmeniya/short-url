<?php

namespace App\Http\Controllers\Front;

use App\Models\Blog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display the blog index page with paginated published blogs.
     */
    public function blogIndex()
    {
        $blogs = Blog::with(['author'])
            ->published()
            ->orderBy('published_at', 'desc')
            ->paginate(12);

        return view('front.blogs.index', [
            'blogs' => $blogs,
            'title' => 'Blog - Latest Articles & Insights',
            'description' => 'Read our latest articles and insights about legal documents, privacy policies, and business compliance.',
            'keywords' => 'blog, articles, legal documents, privacy policy, terms and conditions, business compliance'
        ]);
    }

    /**
     * Display a single blog post.
     */
    public function showBlog($slug)
    {
        $blog = Blog::with(['author'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment view count
        $blog->incrementViews();

        // Get related blogs (same author or recent)
        $relatedBlogs = Blog::published()
            ->where('id', '!=', $blog->id)
            ->where(function ($query) use ($blog) {
                $query->where('author_id', $blog->author_id)
                    ->orWhere('is_featured', true);
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('front.blogs.show', [
            'blog' => $blog,
            'relatedBlogs' => $relatedBlogs,
            'title' => $blog->meta_title ?? $blog->title,
            'description' => $blog->meta_description ?? $blog->excerpt,
            'keywords' => $blog->meta_keywords ?? 'blog, article'
        ]);
    }
}
