@props(['id','label','trigger'=>'Mở hộp thoại'])
<div><button type="button" class="button button-outline" data-open-dialog="{{ $id }}">{{ $trigger }}</button><dialog id="{{ $id }}" class="modal" aria-label="{{ $label }}"><button class="dialog-close" type="button" data-close-dialog aria-label="Đóng">✕</button>{{ $slot }}</dialog></div>
