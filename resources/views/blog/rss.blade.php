{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>Vivre Pleinement - Le blog</title>
        <link>{{ route('blog.index') }}</link>
        <description>Articles, outils et ressources pour comprendre et apaiser les troubles anxieux. Par Laura Baechlé.</description>
        <language>fr-FR</language>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('blog.rss') }}" rel="self" type="application/rss+xml" />
        @foreach ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('blog.show', $post) }}</link>
                <guid isPermaLink="true">{{ route('blog.show', $post) }}</guid>
                <pubDate>{{ $post->published_at?->toRfc2822String() }}</pubDate>
                <dc:creator>Laura Baechlé</dc:creator>
                @foreach ($post->categories as $cat)
                    <category>{{ $cat->name }}</category>
                @endforeach
                <description>{!! '<![CDATA['.$post->cleanExcerpt().']]>' !!}</description>
                <content:encoded>{!! '<![CDATA['.($post->content ?? '').']]>' !!}</content:encoded>
            </item>
        @endforeach
    </channel>
</rss>
