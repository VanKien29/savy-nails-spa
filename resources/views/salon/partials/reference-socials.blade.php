@foreach($settings['social_links'] as $social)
    <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $social['label'] }}">{{ $social['label'] }}</a>
@endforeach
