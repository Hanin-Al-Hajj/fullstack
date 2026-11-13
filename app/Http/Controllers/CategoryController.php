<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\Seo;
use Inertia\Inertia;

class CategoryController extends Controller
{
    // /categories           -> top-level categories
    // /categories/{slug}    -> that category's subcategories
    public function index(?Category $category = null)
    {
        if ($category) {
            abort_unless($category->is_active, 404);

            // A category with no children has nothing to browse: show its products.
            if (! $category->children()->exists()) {
                return redirect()->route('shop', ['category' => $category->slug]);
            }
        }

        $query = $category ? $category->children() : Category::whereNull('parent_id');

        $items = $query
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('_lft')
            ->withCount('children')
            ->with('media')
            ->get()
            ->map(fn (Category $c) => [
                'slug' => $c->slug,
                'name' => $c->name,
                'image_url' => $c->getFirstMediaUrl() ?: null,
                'has_children' => $c->children_count > 0,
            ]);
 
                    Seo::set([
            'title' => $category ? Seo::t($category->name) : Seo::tr('Categories', 'الفئات'),
        ]);
        
        return Inertia::render('Categories', [
            'category' => $category?->only(['slug', 'name']),
            'parent' => $category?->parent?->only(['slug', 'name']),
            'items' => $items,
        ]);
    }
}