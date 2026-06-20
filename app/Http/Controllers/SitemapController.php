<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\Video;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = Cache::remember('sitemap.urls', now()->addHour(), function () {
            $items = collect([
                ['loc' => route('home'), 'changefreq' => 'weekly', 'priority' => '1.0'],
                ['loc' => route('book.show'), 'changefreq' => 'weekly', 'priority' => '0.95'],
                ['loc' => route('booking.index'), 'changefreq' => 'weekly', 'priority' => '0.9'],
                ['loc' => route('blog.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
                ['loc' => route('contact'), 'changefreq' => 'monthly', 'priority' => '0.6'],
                ['loc' => route('legal.mentions'), 'changefreq' => 'yearly', 'priority' => '0.2'],
                ['loc' => route('legal.privacy'), 'changefreq' => 'yearly', 'priority' => '0.2'],
                ['loc' => route('legal.cookies'), 'changefreq' => 'yearly', 'priority' => '0.2'],
                ['loc' => route('legal.cgv'), 'changefreq' => 'yearly', 'priority' => '0.2'],
            ]);

            Post::query()
                ->published()
                ->orderByDesc('updated_at')
                ->get(['slug', 'updated_at'])
                ->each(function ($post) use ($items) {
                    $items->push([
                        'loc' => route('blog.show', $post),
                        'lastmod' => $post->updated_at?->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.8',
                    ]);
                });

            Category::query()->get(['slug', 'updated_at'])
                ->each(function ($cat) use ($items) {
                    $items->push([
                        'loc' => route('blog.category', $cat->slug),
                        'lastmod' => $cat->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                    ]);
                });

            Tag::query()->get(['slug', 'updated_at'])
                ->each(function ($tag) use ($items) {
                    $items->push([
                        'loc' => route('blog.tag', $tag->slug),
                        'lastmod' => $tag->updated_at?->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.4',
                    ]);
                });

            Video::query()
                ->published()
                ->orderByDesc('updated_at')
                ->get()
                ->each(function ($video) use ($items) {
                    $items->push([
                        'loc' => route('videos.show', $video),
                        'lastmod' => $video->updated_at?->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.7',
                    ]);
                });

            $items->push([
                'loc' => route('videos.index'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ]);

            return $items->all();
        });

        return response()
            ->view('sitemap', ['urls' => collect($urls)])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function videos(): Response
    {
        $videos = Cache::remember(
            'sitemap.videos',
            now()->addHour(),
            fn () => Video::query()
                ->published()
                ->orderByDesc('published_at')
                ->get(),
        );

        return response()
            ->view('sitemap-videos', ['videos' => $videos])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
