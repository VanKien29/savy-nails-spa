@props(['items'=>[]])
<nav aria-label="Đường dẫn" class="breadcrumb"><ol>@foreach($items as $item)<li>@if(isset($item['url']))<a href="{{ $item['url'] }}">{{ $item['label'] }}</a>@else<span aria-current="page">{{ $item['label'] }}</span>@endif</li>@endforeach</ol></nav>
