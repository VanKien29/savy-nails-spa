@props(['href'=>null,'variant'=>'primary'])
@php($variant = in_array($variant,['primary','dark','outline','light']) ? $variant : 'primary')
@if($href)<a href="{{ $href }}" {{ $attributes->class(['button','button-'.$variant]) }}>{{ $slot }}</a>@else<button {{ $attributes->class(['button','button-'.$variant])->merge(['type'=>'button']) }}>{{ $slot }}</button>@endif
