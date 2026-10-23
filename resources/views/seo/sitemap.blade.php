{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($pages as $page)
    <url><loc>{{ $seo->url($page->path()) }}</loc>@if($modified = $seo->lastModified($page))<lastmod>{{ $modified }}</lastmod>@endif</url>
@endforeach
</urlset>
