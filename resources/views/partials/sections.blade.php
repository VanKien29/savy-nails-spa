@if(($sections->first()['type'] ?? null) !== 'hero')<div class="container page-title"><h1>{{ $page->title }}</h1></div>@endif
@foreach($sections as $section)
    <x-sections.render :section="$section" :page="$page" :media="$media" :settings="$settings" :is-demo="$isDemo" :first="$loop->first" />
@endforeach
