<div class="sample-content-related">
    @foreach($links as $label => $url)
        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }} <span aria-hidden="true">↗</span></a>
    @endforeach
</div>
