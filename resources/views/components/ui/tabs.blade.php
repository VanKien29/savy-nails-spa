@props(['items'=>[], 'id'=>'tabs'])
<div class="tabs" data-tabs>
    <div class="tab-buttons" aria-label="Chọn nội dung">@foreach($items as $item)<button type="button" data-tab-index="{{ $loop->index }}" id="{{ $id }}-tab-{{ $loop->index }}">{{ $item['label'] }}</button>@endforeach</div>
    @foreach($items as $item)<section class="tab-panel" data-tab-panel id="{{ $id }}-panel-{{ $loop->index }}"><h3>{{ $item['label'] }}</h3><p>{{ $item['text'] }}</p></section>@endforeach
</div>
