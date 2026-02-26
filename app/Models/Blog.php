<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'is_featured',
        'author_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'published_at',
        'views_count',
        'reading_time',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'views_count' => 'integer',
        'reading_time' => 'integer',
    ];

    protected $dates = [
        'published_at',
        'deleted_at',
    ];

    /**
     * Get the author of the blog post.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the user who created the blog post.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the blog post.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the URL for the blog post.
     */
    public function getUrlAttribute()
    {
        return route('blog.show', $this->slug);
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset($this->featured_image);
        }

        return 'images/blog-placeholder.svg';
    }

    public function getFeaturedImageAttribute()
    {
        $image = $this->attributes['featured_image'] ?? null;
        if ($image) {
            return 'storage/' . $image;
        }
        return 'build/images/others/placeholder.jpg';
    }


    /**
     * Get the featured image URL with fallback to placeholder.
     */
    public function getImageUrlAttribute()
    {
        return $this->featured_image_url;
    }

    /**
     * Check if the blog has a featured image.
     */
    public function hasFeaturedImage()
    {
        return !empty($this->featured_image);
    }

    /**
     * Get the excerpt or generate from content.
     */
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Generate excerpt from content if not provided
        $content = strip_tags($this->content);
        return Str::limit($content, 200);
    }

    /**
     * Calculate and update reading time.
     */
    public function calculateReadingTime()
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $readingTime = ceil($wordCount / 200); // Average reading speed: 200 words per minute

        // Use updateQuietly to avoid triggering events and prevent recursive calls
        $this->updateQuietly(['reading_time' => $readingTime]);

        return $readingTime;
    }

    /**
     * Increment views count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions()
    {
        return [
            'draft' => 'Draft',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
    }

    /**
     * Check if the blog post is published.
     */
    public function isPublished()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    /**
     * Check if the blog post is draft.
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the blog post is archived.
     */
    public function isArchived()
    {
        return $this->status === 'archived';
    }

    /**
     * Get related posts.
     */
    public function getRelatedPosts(int $limit = 3)
    {
        return static::published()
            ->where('id', '!=', $this->id)
            ->select([
                'id',
                'title',
                'slug',
                'excerpt',
                'featured_image',
                'published_at',
            ])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    /**
     * Search blogs by title and content.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%");
        });
    }

    /**
     * Get the next published blog post.
     */
    public function getNextPost()
    {
        return static::published()
            ->where('published_at', '>', $this->published_at)
            ->orderBy('published_at', 'asc')
            ->first();
    }

    /**
     * Get the previous published blog post.
     */
    public function getPreviousPost()
    {
        return static::published()
            ->where('published_at', '<', $this->published_at)
            ->orderBy('published_at', 'desc')
            ->first();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });

        static::updating(function ($blog) {
            if ($blog->isDirty('title') && empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });

        static::saved(function ($blog) {
            // Calculate reading time when content changes (but not during seeding)
            if ($blog->isDirty('content') && !app()->runningInConsole()) {
                $blog->calculateReadingTime();
            }
        });
    }
}
