{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
    @foreach ($videos as $video)
        <url>
            <loc>{{ route('videos.show', $video) }}</loc>
            <video:video>
                <video:thumbnail_loc>{{ $video->thumbnail() }}</video:thumbnail_loc>
                <video:title>{{ $video->title }}</video:title>
                <video:description>{{ \Illuminate\Support\Str::limit($video->description ?? $video->title, 2048) }}</video:description>
                <video:content_loc>{{ $video->youtubeUrl() }}</video:content_loc>
                <video:player_loc>{{ $video->embedUrl() }}</video:player_loc>
                @if ($video->duration_seconds)
                    <video:duration>{{ $video->duration_seconds }}</video:duration>
                @endif
                @if ($video->published_at)
                    <video:publication_date>{{ $video->published_at->toAtomString() }}</video:publication_date>
                @endif
                @if ($video->view_count)
                    <video:view_count>{{ $video->view_count }}</video:view_count>
                @endif
                <video:family_friendly>yes</video:family_friendly>
                <video:requires_subscription>no</video:requires_subscription>
                <video:live>no</video:live>
            </video:video>
        </url>
    @endforeach
</urlset>
