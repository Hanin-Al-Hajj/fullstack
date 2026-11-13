<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class SeoController extends Controller
{
    // /sitemap.xml: every page a visitor can open, so search engines find all products.
    public function sitemap()
    {
        $urls = collect(['/', '/shop', '/categories', '/about', '/contact', '/support', '/privacy-policy'])
            ->map(fn ($path) => ['loc' => url($path), 'lastmod' => null]);

        $categories = Category::where('is_active', true)->withCount('children')->get()
            ->map(fn (Category $c) => [
                'loc' => $c->children_count > 0 ? url('/categories/'.$c->slug) : url('/shop?category='.$c->slug),
                'lastmod' => $c->updated_at,
            ]);

        $products = Product::where('is_active', true)->whereNotNull('published_at')->get(['slug', 'updated_at'])
            ->map(fn (Product $p) => ['loc' => url('/products/'.$p->slug), 'lastmod' => $p->updated_at]);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls->concat($categories)->concat($products) as $u) {
            $xml .= '  <url><loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>';
            if ($u['lastmod']) {
                $xml .= '<lastmod>'.$u['lastmod']->toAtomString().'</lastmod>';
            }
            $xml .= "</url>\n";
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }

    // /robots.txt: keeps private pages (cart, account, admin...) out of search results.
    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /account',
            'Disallow: /orders',
            'Disallow: /order/',
            'Disallow: /addresses',
            'Disallow: /wishlist',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /locale/',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain']);
    }
}
