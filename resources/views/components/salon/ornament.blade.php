@props(['kind'=>'curve'])
<svg {{ $attributes->class(['kl-line-art']) }} viewBox="0 0 620 320" fill="none" aria-hidden="true" data-draw-art>
@if($kind==='orbit')
    <path pathLength="1" d="M605 160C605 242 474 306 310 306S15 242 15 160 146 14 310 14s295 64 295 146Z"/>
    <path pathLength="1" d="M554 46c52 61-14 162-149 225S120 334 68 273 82 111 217 48 502-15 554 46Z"/>
@else
    <path pathLength="1" d="M-20 247C65 247 83 43 218 43s156 243 275 243 118-122 159-122"/>
    <path pathLength="1" d="M-20 271C65 271 93 64 221 64s149 244 272 244 129-122 169-122"/>
@endif
</svg>
