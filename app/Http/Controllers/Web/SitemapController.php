<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Home page
            $xml .= $this->urlEntry(url('/'), null, 'daily', '1.0');

            // Vacancies list
            $xml .= $this->urlEntry(url('/vacancies'), null, 'hourly', '0.9');

            // Individual vacancies (slug-based URLs)
            $vacancies = Vacancy::active()
                ->select('slug', 'updated_at')
                ->whereNotNull('slug')
                ->orderByDesc('updated_at')
                ->limit(5000)
                ->get();

            foreach ($vacancies as $vacancy) {
                $xml .= $this->urlEntry(
                    url('/vacancies/' . $vacancy->slug),
                    $vacancy->updated_at->toW3cString(),
                    'weekly',
                    '0.8'
                );
            }

            // Category pages
            $categories = Category::where('is_active', true)
                ->whereNull('parent_id')
                ->select('slug')
                ->get();

            foreach ($categories as $category) {
                $xml .= $this->urlEntry(
                    url('/vacancies?category=' . $category->slug),
                    null,
                    'daily',
                    '0.7'
                );
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function urlEntry(string $loc, ?string $lastmod, string $changefreq, string $priority): string
    {
        $xml = '<url>';
        $xml .= '<loc>' . htmlspecialchars($loc) . '</loc>';
        if ($lastmod) {
            $xml .= '<lastmod>' . $lastmod . '</lastmod>';
        }
        $xml .= '<changefreq>' . $changefreq . '</changefreq>';
        $xml .= '<priority>' . $priority . '</priority>';
        $xml .= '</url>';
        return $xml;
    }
}
