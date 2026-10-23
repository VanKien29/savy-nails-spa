@props(['settings'])
<aside class="floating-contact position-{{ $settings['contact_position'] }}" aria-label="Liên hệ nhanh">
    @foreach($settings['contacts'] as $contact)
        @if($contact['enabled'])
        <a class="contact-pill {{ !$contact['desktop'] ? 'hide-desktop' : '' }} {{ !$contact['mobile'] ? 'hide-mobile' : '' }}" style="--contact-color:{{ $contact['color'] }}" href="{{ $contact['url'] }}" data-contact="{{ $contact['type'] }}" @if($contact['type'] !== 'phone') target="_blank" rel="noopener noreferrer" @endif aria-label="Liên hệ qua {{ $contact['type'] }}">{{ ['phone'=>'Gọi','zalo'=>'Zalo','messenger'=>'Chat','whatsapp'=>'WA'][$contact['type']] }}</a>
        @endif
    @endforeach
    @if($settings['back_to_top'])<a class="back-top" href="#top" aria-label="Về đầu trang">↑</a>@endif
</aside>
