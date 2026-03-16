<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap', 3600, function () {
            $vacancies = Vacancy::active()
                ->select('id', 'updated_at')
                ->orderByDesc('updated_at')
                ->limit(5000)
                ->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Home page
            $xml .= '<url>';
            $xml .= '<loc>' . url('/') . '</loc>';
            $xml .= '<changefreq>daily</changefreq>';
            $xml .= '<priority>1.0</priority>';
            $xml .= '</url>';

            // Vacancies list
            $xml .= '<url>';
            $xml .= '<loc>' . url('/vacancies') . '</loc>';
            $xml .= '<changefreq>hourly</changefreq>';
            $xml .= '<priority>0.9</priority>';
            $xml .= '</url>';

            // Individual vacancies
            foreach ($vacancies as $vacancy) {
                $xml .= '<url>';
                $xml .= '<loc>' . url('/vacancies/' . $vacancy->id) . '</loc>';
                $xml .= '<lastmod>' . $vacancy->updated_at->toW3cString() . '</lastmod>';
                $xml .= '<changefreq>weekly</changefreq>';
                $xml .= '<priority>0.8</priority>';
                $xml .= '</url>';
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
