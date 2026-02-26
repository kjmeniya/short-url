<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\User;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\SettingsService;
use App\Services\NotificationService;

class BlogController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected $settingsService;
    protected NotificationService $notificationService;

    public function __construct(SettingsService $settingsService, NotificationService $notificationService)
    {
        $this->settingsService = $settingsService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Handle select data request for dropdowns
            if ($request->has('select_data')) {
                $blogs = Blog::select('id', 'title')
                    ->where('status', 'published')
                    ->orderBy('title')
                    ->get();

                return response()->json($blogs);
            }

            $query = Blog::with(['author'])
                ->select('blogs.*');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('featured')) {
                $query->where('is_featured', $request->featured);
            }

            if ($request->filled('author')) {
                $query->where('author_id', $request->author);
            }


            // Apply date filter using trait
            $query = $this->applyDateFilter($query, $request);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="' . route('admin.blogs.show', $row->id) . '">
                                    <i class="icon-sm me-2" data-lucide="eye"></i>View
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="' . route('admin.blogs.edit', $row->id) . '">
                                    <i class="icon-sm me-2" data-lucide="edit"></i>Edit
                                </a>
                            </li>';

                    if ($row->status === 'published') {
                        $actions .= '<li>
                                <a class="dropdown-item" href="' . route('admin.blogs.show', $row->slug) . '" target="_blank">
                                    <i class="icon-sm me-2" data-lucide="external-link"></i>View Live
                                </a>
                            </li>';
                    }

                    $actions .= '<li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger delete-blog" href="javascript:void(0)" data-id="' . $row->id . '">
                                    <i class="icon-sm me-2" data-lucide="trash-2"></i>Delete
                                </a>
                            </li>
                        </ul>
                    </div>';
                    return $actions;
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'published' => 'success',
                        'draft' => 'warning',
                        'archived' => 'secondary'
                    ];
                    $color = $badges[$row->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('featured_badge', function ($row) {
                    return $row->is_featured
                        ? '<span class="badge bg-primary">Featured</span>'
                        : '<span class="badge bg-light text-dark">Regular</span>';
                })
                ->addColumn('author_name', function ($row) {
                    return $row->author ? $row->author->name : 'N/A';
                })
                ->addColumn('excerpt_preview', function ($row) {
                    return Str::limit(strip_tags($row->excerpt), 50);
                })
                ->addColumn('featured_image', function ($row) {
                    $hasRealImage = $row->featured_image ? url($row->featured_image) : asset('build/images/others/placeholder.jpg');
                    return '<img src="' . $hasRealImage . '" class="rounded ratio ratio-1x1" style="width: 100px; height: auto;">';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('M d, Y g:i A');
                })
                ->rawColumns(['action', 'status_badge', 'featured_badge', 'featured_image'])
                ->make(true);
        }

        // Get statistics and authors for filters
        $stats = $this->getBlogStats();
        $authors = User::whereHas('blogs')->get(['id', 'name']);
        $maxUploadSize = $this->settingsService->get('max_upload_size', 10);

        $viewData = $this->withSeo(
            compact('stats', 'authors', 'maxUploadSize'),
            'Blog Management',
            'Manage blog posts, articles, and content for your website.',
            'blog management, articles, posts, content management'
        );

        return view('admin.blogs.index', $viewData);
    }

    /**
     * Get blog statistics.
     */
    protected function getBlogStats(): array
    {
        $total = Blog::count();
        $published = Blog::where('status', 'published')->count();
        $draft = Blog::where('status', 'draft')->count();
        $archived = Blog::where('status', 'archived')->count();
        $featured = Blog::where('is_featured', true)->count();
        $thisMonth = Blog::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'archived' => $archived,
            'featured' => $featured,
            'this_month' => $thisMonth,
        ];
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $maxUploadSize = $this->settingsService->get('max_upload_size', 10);

        $viewData = $this->withSeo(
            compact('maxUploadSize'),
            'Create Blog Post',
            'Create new blog post or article for your website.',
            'create blog post, new article, blog creation'
        );

        return view('admin.blogs.create', $viewData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get dynamic upload size from settings
        $maxUploadSize = $this->settingsService->get('max_upload_size', 10) * 1024; // Convert MB to KB

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|min:3',
            'slug' => 'required|string|max:255|min:3|unique:blogs,slug|regex:/^[a-z0-9-]+$/',
            'excerpt' => 'required|string|max:500|min:10',
            'content' => 'required|string|min:50',
            'featured_image' => "nullable|image|mimes:jpeg,png,jpg,gif,webp|max:{$maxUploadSize}",
            'featured_image_cropped' => 'nullable|string',
            'featured_image_remove' => 'nullable|boolean',
            'status' => 'required|string|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'published_at' => 'nullable|date|after_or_equal:today',
        ], [
            'title.required' => 'The title field is required.',
            'title.min' => 'The title must be at least 3 characters.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'slug.required' => 'The slug field is required.',
            'slug.min' => 'The slug must be at least 3 characters.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'slug.unique' => 'This slug is already taken.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'excerpt.required' => 'The excerpt field is required.',
            'excerpt.min' => 'The excerpt must be at least 10 characters.',
            'excerpt.max' => 'The excerpt may not be greater than 500 characters.',
            'content.required' => 'The content field is required.',
            'content.min' => 'The content must be at least 50 characters.',
            'featured_image.image' => 'The featured image must be an image.',
            'featured_image.mimes' => 'The featured image must be a file of type: jpeg, png, jpg, gif, webp.',
            'featured_image.max' => 'The featured image may not be greater than 2MB.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The selected status is invalid.',
            'meta_title.max' => 'The meta title may not be greater than 255 characters.',
            'meta_description.max' => 'The meta description may not be greater than 500 characters.',
            'meta_keywords.max' => 'The meta keywords may not be greater than 255 characters.',
            'published_at.date' => 'The published date is not a valid date.',
            'published_at.after_or_equal' => 'The published date must be today or a future date.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below and try again.');
        }

        $validated = $validator->validated();
        $validated['author_id'] = Auth::id();
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        // Handle featured image upload
        if ($request->boolean('featured_image_remove')) {
            // Remove featured image
            $validated['featured_image'] = null;
        } elseif ($request->filled('featured_image_cropped')) {
            // Handle cropped image (base64 data)
            try {
                $validated['featured_image'] = $this->saveCroppedImage($request->input('featured_image_cropped'));
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(['featured_image' => 'Error processing cropped image: ' . $e->getMessage()])
                    ->withInput();
            }
        } elseif ($request->hasFile('featured_image')) {
            // Handle direct file upload
            $validated['featured_image'] = $request->file('featured_image')
                ->store('blog/featured-images', 'public');
        }

        // Set published_at if status is published and no date is set
        if ($validated['status'] === 'published' && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        // Remove the base64 data and remove flag from the validated array before saving
        unset($validated['featured_image_cropped'], $validated['featured_image_remove']);

        $blog = Blog::create($validated);

        // Send notification to admins asynchronously
        dispatch(function () use ($blog) {
            app(\App\Services\NotificationService::class)->sendToAdmins(
                'blog_created',
                'New Blog Post Created',
                "Blog post '{$blog->title}' has been created by " . Auth::user()->name,
                [
                    'blog_id' => $blog->id,
                    'blog_title' => $blog->title,
                    'blog_status' => $blog->status,
                    'author' => Auth::user()->name,
                    'url' => route('admin.blogs.show', $blog->id)
                ]
            );
        })->afterResponse();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $blog = Blog::findOrFail($id);
        $blog->load(['author']);

        $viewData = $this->withSeo(
            compact('blog'),
            'Blog Post Details',
            "View details for {$blog->title} blog post.",
            'blog post details, article view, blog information'
        );

        return view('admin.blogs.show', $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $blog = Blog::findOrFail($id);
        $maxUploadSize = $this->settingsService->get('max_upload_size', 10);

        $viewData = $this->withSeo(
            compact('blog', 'maxUploadSize'),
            'Edit Blog Post',
            "Edit {$blog->title} blog post.",
            'edit blog post, update article, blog editing'
        );

        return view('admin.blogs.edit', $viewData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        // Get dynamic upload size from settings
        $maxUploadSize = $this->settingsService->get('max_upload_size', 10) * 1024; // Convert MB to KB

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255|min:3',
            'slug' => 'required|string|max:255|min:3|unique:blogs,slug,' . $blog->id . '|regex:/^[a-z0-9-]+$/',
            'excerpt' => 'required|string|max:500|min:10',
            'content' => 'required|string|min:50',
            'featured_image' => "nullable|image|mimes:jpeg,png,jpg,gif,webp|max:{$maxUploadSize}",
            'featured_image_cropped' => 'nullable|string',
            'featured_image_remove' => 'nullable|boolean',
            'status' => 'required|string|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'published_at' => 'nullable|date',
        ], [
            'title.required' => 'The title field is required.',
            'title.min' => 'The title must be at least 3 characters.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'slug.required' => 'The slug field is required.',
            'slug.min' => 'The slug must be at least 3 characters.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'slug.unique' => 'This slug is already taken.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'excerpt.required' => 'The excerpt field is required.',
            'excerpt.min' => 'The excerpt must be at least 10 characters.',
            'excerpt.max' => 'The excerpt may not be greater than 500 characters.',
            'content.required' => 'The content field is required.',
            'content.min' => 'The content must be at least 50 characters.',
            'featured_image.image' => 'The featured image must be an image.',
            'featured_image.mimes' => 'The featured image must be a file of type: jpeg, png, jpg, gif, webp.',
            'featured_image.max' => 'The featured image may not be greater than 2MB.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The selected status is invalid.',
            'meta_title.max' => 'The meta title may not be greater than 255 characters.',
            'meta_description.max' => 'The meta description may not be greater than 500 characters.',
            'meta_keywords.max' => 'The meta keywords may not be greater than 255 characters.',
            'published_at.date' => 'The published date is not a valid date.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below and try again.');
        }

        $validated = $validator->validated();
        $validated['updated_by'] = Auth::id();

        // Handle featured image upload
        if ($request->boolean('featured_image_remove')) {
            // Remove featured image
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $validated['featured_image'] = null;
        } elseif ($request->filled('featured_image_cropped')) {
            // Handle cropped image (base64 data)
            try {
                // Delete old image if exists
                if ($blog->featured_image) {
                    Storage::disk('public')->delete($blog->featured_image);
                }

                $validated['featured_image'] = $this->saveCroppedImage($request->input('featured_image_cropped'));
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withErrors(['featured_image' => 'Error processing cropped image: ' . $e->getMessage()])
                    ->withInput();
            }
        } elseif ($request->hasFile('featured_image')) {
            // Handle direct file upload
            // Delete old image if exists
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }

            $validated['featured_image'] = $request->file('featured_image')
                ->store('blog/featured-images', 'public');
        }

        // Set published_at if status is published and no date is set
        if ($validated['status'] === 'published' && !$validated['published_at'] && $blog->status !== 'published') {
            $validated['published_at'] = now();
        }

        // Remove the base64 data and remove flag from the validated array before saving
        unset($validated['featured_image_cropped'], $validated['featured_image_remove']);

        // Store original status for comparison
        $originalStatus = $blog->status;

        $blog->update($validated);

        // Send appropriate notification based on status change asynchronously
        dispatch(function () use ($blog, $validated, $originalStatus) {
            $notificationService = app(\App\Services\NotificationService::class);

            if (isset($validated['status']) && $originalStatus !== $validated['status'] && $validated['status'] === 'published') {
                $notificationService->sendToAdmins(
                    'blog_published',
                    'Blog Post Published',
                    "Blog post '{$blog->title}' has been published by " . Auth::user()->name,
                    [
                        'blog_id' => $blog->id,
                        'blog_title' => $blog->title,
                        'author' => Auth::user()->name,
                        'url' => route('admin.blogs.show', $blog->id)
                    ]
                );
            } else {
                $notificationService->sendToAdmins(
                    'blog_updated',
                    'Blog Post Updated',
                    "Blog post '{$blog->title}' has been updated by " . Auth::user()->name,
                    [
                        'blog_id' => $blog->id,
                        'blog_title' => $blog->title,
                        'author' => Auth::user()->name,
                        'url' => route('admin.blogs.show', $blog->id)
                    ]
                );
            }
        })->afterResponse();

        return redirect()->route('admin.blogs.index')
            ->with('success', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $blog = Blog::findOrFail($id);

            // Store blog info before deletion
            $blogTitle = $blog->title;
            $blogId = $blog->id;

            // Delete featured image if exists
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }

            $blog->delete();

            // Send notification to admins
            $this->notificationService->sendToAdmins(
                'blog_deleted',
                'Blog Post Deleted',
                "Blog post '{$blogTitle}' has been deleted by " . Auth::user()->name,
                [
                    'blog_id' => $blogId,
                    'blog_title' => $blogTitle,
                    'deleted_by' => Auth::user()->name,
                    'url' => route('admin.blogs.index')
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Blog post deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting blog post: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle bulk actions for blog posts.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:publish,draft,archive,feature,unfeature,delete',
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'required|integer|exists:blogs,id'
        ], [
            'action.required' => 'Action is required.',
            'action.in' => 'Invalid action selected.',
            'ids.required' => 'Please select at least one blog post.',
            'ids.min' => 'Please select at least one blog post.',
            'ids.max' => 'You can only process up to 100 blog posts at once.',
            'ids.*.exists' => 'One or more selected blog posts do not exist.',
        ]);

        $action = $request->action;
        $ids = $request->ids;
        $count = count($ids);

        try {
            switch ($action) {
                case 'publish':
                    Blog::whereIn('id', $ids)->update([
                        'status' => 'published',
                        'published_at' => now()
                    ]);
                    $message = "{$count} blog posts published successfully.";
                    break;

                case 'draft':
                    Blog::whereIn('id', $ids)->update(['status' => 'draft']);
                    $message = "{$count} blog posts moved to draft successfully.";
                    break;

                case 'archive':
                    Blog::whereIn('id', $ids)->update(['status' => 'archived']);
                    $message = "{$count} blog posts archived successfully.";
                    break;

                case 'feature':
                    Blog::whereIn('id', $ids)->update(['is_featured' => true]);
                    $message = "{$count} blog posts marked as featured successfully.";
                    break;

                case 'unfeature':
                    Blog::whereIn('id', $ids)->update(['is_featured' => false]);
                    $message = "{$count} blog posts removed from featured successfully.";
                    break;

                case 'delete':
                    $blogs = Blog::whereIn('id', $ids)->get();
                    foreach ($blogs as $blog) {
                        if ($blog->featured_image) {
                            Storage::disk('public')->delete($blog->featured_image);
                        }
                        $blog->delete();
                    }
                    $message = "{$count} blog posts deleted successfully.";
                    break;
            }

            // Send notification to admins for bulk actions
            $currentUser = Auth::user();
            $actionTypes = [
                'publish' => 'blog_bulk_published',
                'draft' => 'blog_bulk_drafted',
                'archive' => 'blog_bulk_archived',
                'feature' => 'blog_bulk_featured',
                'unfeature' => 'blog_bulk_unfeatured',
                'delete' => 'blog_bulk_deleted'
            ];

            $notificationType = $actionTypes[$action] ?? 'blog_bulk_action';
            $actionName = ucfirst($action);

            $this->notificationService->sendToAdmins(
                $notificationType,
                "Blog Posts Bulk {$actionName}",
                "Bulk {$action} action performed on {$count} blog posts by {$currentUser->name}",
                [
                    'action' => $action,
                    'count' => $count,
                    'performed_by' => $currentUser->name,
                    'url' => route('admin.blogs.index')
                ]
            );

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export blog posts.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'json');
        $blogs = Blog::with('author')->get();

        // Send notification to admins
        $currentUser = Auth::user();
        $this->notificationService->sendToAdmins(
            'blog_exported',
            'Blog Posts Exported',
            "Blog posts have been exported in {$format} format by {$currentUser->name}",
            [
                'format' => $format,
                'count' => $blogs->count(),
                'exported_by' => $currentUser->name,
                'url' => route('admin.blogs.index')
            ]
        );

        if ($format === 'csv') {
            $filename = 'blog_posts_' . now()->format('Y_m_d_H_i_s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($blogs) {
                $file = fopen('php://output', 'w');

                // Get dynamic columns
                $tableName = (new Blog())->getTable();
                $columns = Schema::getColumnListing($tableName);

                // Create header row with column names
                $headers = array_map('ucfirst', $columns);
                fputcsv($file, $headers);

                foreach ($blogs as $blog) {
                    $row = [];
                    foreach ($columns as $column) {
                        $value = $blog->getAttribute($column);

                        if ($value === null) {
                            $row[] = '';
                        } elseif (is_bool($value)) {
                            $row[] = $value ? 'Yes' : 'No';
                        } elseif ($value instanceof \Carbon\Carbon) {
                            $row[] = $value->format('Y-m-d H:i:s');
                        } else {
                            $row[] = $value;
                        }
                    }
                    fputcsv($file, $row);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($format === 'sql') {
            $filename = 'blog_posts_' . now()->format('Y_m_d_H_i_s') . '.sql';
            $headers = [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($blogs) {
                echo "-- Blog Posts Export\n";
                echo "-- Generated on: " . now()->format('Y-m-d H:i:s') . "\n\n";

                // Get dynamic table structure
                $tableName = (new Blog())->getTable();
                $columns = Schema::getColumnListing($tableName);

                echo "-- Table structure for table `{$tableName}`\n";
                echo $this->generateCreateTableStatement($tableName) . "\n\n";

                echo "-- Dumping data for table `{$tableName}`\n";

                if ($blogs->count() > 0) {
                    $columnsList = '`' . implode('`, `', $columns) . '`';
                    echo "INSERT INTO `{$tableName}` ({$columnsList}) VALUES\n";

                    $blogData = [];
                    foreach ($blogs as $blog) {
                        $values = [];
                        foreach ($columns as $column) {
                            $value = $blog->getAttribute($column);

                            if ($value === null) {
                                $values[] = 'NULL';
                            } elseif (is_bool($value)) {
                                $values[] = $value ? '1' : '0';
                            } elseif (is_numeric($value)) {
                                $values[] = $value;
                            } elseif ($value instanceof \Carbon\Carbon) {
                                $values[] = $this->escapeSqlString($value->format('Y-m-d H:i:s'));
                            } else {
                                $values[] = $this->escapeSqlString($value);
                            }
                        }
                        $blogData[] = '(' . implode(', ', $values) . ')';
                    }

                    echo implode(",\n", $blogData) . ";\n";
                } else {
                    echo "-- No data to export\n";
                }
            };

            return response()->stream($callback, 200, $headers);
        }

        // JSON export
        $filename = 'blog_posts_' . now()->format('Y_m_d_H_i_s') . '.json';

        // Get dynamic columns
        $tableName = (new Blog())->getTable();
        $columns = Schema::getColumnListing($tableName);

        $data = $blogs->map(function ($blog) use ($columns) {
            $blogData = [];
            foreach ($columns as $column) {
                $value = $blog->getAttribute($column);

                if ($value instanceof \Carbon\Carbon) {
                    $blogData[$column] = $value->format('Y-m-d H:i:s');
                } else {
                    $blogData[$column] = $value;
                }
            }
            return $blogData;
        });

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Import blog posts.
     */
    public function import(Request $request)
    {
        // Get dynamic upload size from settings
        $maxUploadSize = $this->settingsService->get('max_upload_size', 10) * 1024; // Convert MB to KB

        // Custom validation for file types since SQL might not be recognized by MIME
        $file = $request->file('file');
        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a file to import.'
            ], 422);
        }

        $allowedExtensions = ['json', 'csv', 'sql'];
        $fileExtension = strtolower($file->getClientOriginalExtension());

        if (!in_array($fileExtension, $allowedExtensions)) {
            return response()->json([
                'success' => false,
                'message' => 'The file must be a JSON, CSV, or SQL file.'
            ], 422);
        }

        $request->validate([
            'file' => "required|file|max:{$maxUploadSize}",
            'overwrite' => 'nullable|boolean'
        ], [
            'file.required' => 'Please select a file to import.',
            'file.file' => 'The uploaded file is not valid.',
            'file.max' => 'The file size must not exceed ' . $this->settingsService->get('max_upload_size', 10) . 'MB.',
        ]);

        $file = $request->file('file');
        $overwrite = $request->boolean('overwrite');
        $errors = [];
        $imported = 0;

        try {
            if ($file->getClientOriginalExtension() === 'csv') {
                $data = array_map('str_getcsv', file($file->path()));
                $headers = array_shift($data);

                // Get valid columns from the table
                $tableName = (new Blog())->getTable();
                $validColumns = Schema::getColumnListing($tableName);

                foreach ($data as $row) {
                    if (count($headers) === count($row)) {
                        $blogData = array_combine($headers, $row);

                        // Filter out invalid columns and normalize column names
                        $filteredData = [];
                        foreach ($blogData as $key => $value) {
                            $normalizedKey = strtolower(str_replace(' ', '_', $key));
                            if (in_array($normalizedKey, $validColumns)) {
                                $filteredData[$normalizedKey] = $value === '' ? null : $value;
                            }
                        }

                        if (!empty($filteredData)) {
                            $result = $this->importBlogPost($filteredData, $overwrite);
                            if ($result['success']) {
                                $imported++;
                            } else {
                                $errors[] = $result['error'];
                            }
                        } else {
                            $errors[] = 'No valid columns found in CSV row';
                        }
                    } else {
                        $errors[] = 'CSV row has mismatched column count';
                    }
                }
            } elseif ($file->getClientOriginalExtension() === 'sql') {
                $sqlContent = file_get_contents($file->path());
                $result = $this->importFromSql($sqlContent, $overwrite);
                $imported = $result['imported'];
                $errors = $result['errors'];
            } else {
                $jsonData = json_decode(file_get_contents($file->path()), true);

                foreach ($jsonData as $blogData) {
                    $result = $this->importBlogPost($blogData, $overwrite);
                    if ($result['success']) {
                        $imported++;
                    } else {
                        $errors[] = $result['error'];
                    }
                }
            }

            // Send notification to admins
            $currentUser = Auth::user();
            $this->notificationService->sendToAdmins(
                'blog_imported',
                'Blog Posts Imported',
                "{$imported} blog posts have been imported by {$currentUser->name}",
                [
                    'imported_count' => $imported,
                    'error_count' => count($errors),
                    'imported_by' => $currentUser->name,
                    'url' => route('admin.blogs.index')
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "{$imported} blog posts imported successfully.",
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import a single blog post.
     */
    private function importBlogPost(array $data, bool $overwrite): array
    {
        try {
            // Validate required fields
            if (empty($data['title'])) {
                return ['success' => false, 'error' => 'Title is required'];
            }

            $slug = $data['slug'] ?? Str::slug($data['title']);

            if (empty($slug)) {
                return ['success' => false, 'error' => 'Could not generate slug from title'];
            }

            $existingBlog = Blog::where('slug', $slug)->first();

            if ($existingBlog && !$overwrite) {
                return ['success' => false, 'error' => "Blog with slug '{$slug}' already exists"];
            }

            // Prepare blog data with validation
            $blogData = [
                'title' => $data['title'],
                'slug' => $slug,
                'excerpt' => $data['excerpt'] ?? '',
                'content' => $data['content'] ?? '',
                'status' => in_array($data['status'] ?? '', ['draft', 'published', 'archived']) ? $data['status'] : 'draft',
                'is_featured' => $this->parseBooleanValue($data['is_featured'] ?? false),
                'author_id' => $data['author_id'] ?? Auth::id(),
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'views_count' => is_numeric($data['views_count'] ?? 0) ? (int)$data['views_count'] : 0,
                'reading_time' => is_numeric($data['reading_time'] ?? null) ? (int)$data['reading_time'] : null,
            ];

            // Handle timestamps
            if (!empty($data['published_at']) && $data['published_at'] !== 'NULL') {
                try {
                    $blogData['published_at'] = \Carbon\Carbon::parse($data['published_at']);
                } catch (\Exception $e) {
                    // Invalid date, skip it
                }
            }

            if (!empty($data['created_at']) && $data['created_at'] !== 'NULL') {
                try {
                    $blogData['created_at'] = \Carbon\Carbon::parse($data['created_at']);
                } catch (\Exception $e) {
                    // Invalid date, skip it
                }
            }

            if (!empty($data['updated_at']) && $data['updated_at'] !== 'NULL') {
                try {
                    $blogData['updated_at'] = \Carbon\Carbon::parse($data['updated_at']);
                } catch (\Exception $e) {
                    // Invalid date, skip it
                }
            }

            // Create or update blog
            if ($existingBlog && $overwrite) {
                $existingBlog->update($blogData);
                return ['success' => true, 'action' => 'updated'];
            } else {
                Blog::create($blogData);
                return ['success' => true, 'action' => 'created'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Import error: ' . $e->getMessage()];
        }
    }

    /**
     * Escape string for SQL export.
     */
    private function escapeSqlString($value)
    {
        if ($value === null) {
            return 'NULL';
        }

        $escaped = str_replace(
            ["\\", "'", "\n", "\r", "\t"],
            ["\\\\", "\\'", "\\n", "\\r", "\\t"],
            $value
        );

        return "'" . $escaped . "'";
    }

    /**
     * Parse boolean value from various formats.
     */
    private function parseBooleanValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'on']);
        }

        return (bool) $value;
    }

    /**
     * Generate CREATE TABLE IF NOT EXISTS statement dynamically.
     */
    private function generateCreateTableStatement($tableName)
    {
        try {
            // Get the actual CREATE TABLE statement from the database
            $result = DB::select("SHOW CREATE TABLE `{$tableName}`");
            if (!empty($result)) {
                $createSQL = $result[0]->{'Create Table'};

                // Modify to CREATE TABLE IF NOT EXISTS
                $createSQL = preg_replace(
                    '/^CREATE TABLE /i',
                    'CREATE TABLE IF NOT EXISTS ',
                    $createSQL
                );

                return $createSQL . ';';
            }
        } catch (\Exception $e) {
            // Fallback: generate basic CREATE TABLE IF NOT EXISTS from column info
            if (Schema::hasTable($tableName)) {
                $columns = Schema::getColumnListing($tableName);
                $columnDefinitions = [];

                foreach ($columns as $column) {
                    $columnType = Schema::getColumnType($tableName, $column);
                    $columnDefinitions[] = "  `{$column}` {$columnType}";
                }

                return "CREATE TABLE IF NOT EXISTS `{$tableName}` (\n" .
                    implode(",\n", $columnDefinitions) . "\n" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            }
        }

        return "-- Could not generate CREATE TABLE IF NOT EXISTS statement for `{$tableName}`";
    }

    /**
     * Import blog posts from SQL file.
     */

    private function importFromSql($sqlContent, $overwrite)
    {
        $imported = 0;
        $errors = [];

        try {
            $tableName = (new Blog())->getTable();

            // Validate table name to prevent SQL injection
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
                throw new \Exception("Invalid table name");
            }

            $columns = Schema::getColumnListing($tableName);

            // Match all INSERT INTO statements for the table
            $pattern = "/INSERT INTO `{$tableName}`\s*\(([^)]+)\)\s*VALUES\s*(.*?);/is";
            preg_match_all($pattern, $sqlContent, $matches, PREG_SET_ORDER);

            if (empty($matches)) {
                $errors[] = "No valid INSERT statements found for `{$tableName}`";
            }

            foreach ($matches as $match) {
                $columnList = array_map('trim', explode(',', str_replace('`', '', $match[1])));
                $valuesString = $match[2];

                // Parse each group of values
                preg_match_all('/\(([^)]+)\)/', $valuesString, $valueMatches);

                foreach ($valueMatches[1] as $valueSet) {
                    try {
                        $values = $this->parseSqlValues($valueSet);

                        if (count($values) === count($columnList)) {
                            $blogData = [];
                            for ($i = 0; $i < count($columnList); $i++) {
                                $value = $values[$i];
                                $blogData[$columnList[$i]] = strtoupper($value) === 'NULL' ? null : $value;
                            }

                            $result = $this->importBlogPost($blogData, $overwrite);
                            if ($result['success']) {
                                $imported++;
                            } else {
                                $errors[] = $result['error'];
                            }
                        } else {
                            $errors[] = "Invalid SQL format: expected " . count($columnList) . " columns, got " . count($values);
                        }
                    } catch (\Exception $e) {
                        $errors[] = 'Error parsing SQL row: ' . $e->getMessage();
                    }
                }
            }
        } catch (\Exception $e) {
            $errors[] = 'Error parsing SQL file: ' . $e->getMessage();
        }

        return [
            'imported' => $imported,
            'errors' => $errors
        ];
    }


    /**
     * Parse SQL values from a value set string.
     */
    private function parseSqlValues($valueSet)
    {
        $values = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';
        $escaped = false;

        for ($i = 0; $i < strlen($valueSet); $i++) {
            $char = $valueSet[$i];

            if ($escaped) {
                $current .= $char;
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if (!$inQuotes && ($char === "'" || $char === '"')) {
                $inQuotes = true;
                $quoteChar = $char;
                continue;
            }

            if ($inQuotes && $char === $quoteChar) {
                $inQuotes = false;
                continue;
            }

            if (!$inQuotes && $char === ',') {
                $values[] = $this->normalizeSqlValue($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if ($current !== '') {
            $values[] = $this->normalizeSqlValue($current);
        }

        return $values;
    }

    /**
     * Normalize SQL value (strip quotes, unescape).
     */
    private function normalizeSqlValue($value)
    {
        $value = trim($value);

        // Remove surrounding quotes
        if ((str_starts_with($value, "'") && str_ends_with($value, "'")) ||
            (str_starts_with($value, '"') && str_ends_with($value, '"'))
        ) {
            $value = substr($value, 1, -1);
        }

        // Unescape SQL escape sequences
        $value = str_replace(
            ["\\n", "\\r", "\\t", "\\\\", "\\'", '\\"'],
            ["\n", "\r", "\t", "\\", "'", '"'],
            $value
        );

        return $value;
    }

    /**
     * Save cropped image from base64 data
     */
    private function saveCroppedImage($base64Data, $folder = 'blog/featured-images')
    {
        try {
            // Extract image type and data
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                $imageType = $matches[1];
                $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
                $imageData = base64_decode($imageData);

                // Generate unique filename with proper extension
                $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $path = $folder . '/' . $filename;

                // Save to storage
                Storage::disk('public')->put($path, $imageData);

                return $path;
            }

            throw new \InvalidArgumentException('Invalid image data format');
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Error saving cropped image: ' . $e->getMessage());
        }
    }
}
