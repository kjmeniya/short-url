<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'route_name',
        'method',
        'category',
    ];

    /**
     * Get the roles for the permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Get permissions grouped by category.
     */
    public static function getByCategory(): array
    {
        return static::orderBy('category')
            ->orderBy('display_name')
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    /**
     * Get available permission categories.
     */
    public static function getCategories(): array
    {
        return static::distinct('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by method.
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('method', $method);
    }
}
