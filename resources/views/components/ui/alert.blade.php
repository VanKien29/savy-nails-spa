@props(['error'=>false])
<div {{ $attributes->class(['alert','alert-error'=>$error])->merge(['role'=>$error?'alert':'status']) }}>{{ $slot }}</div>
