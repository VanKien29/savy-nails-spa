@props(['name'=>'arrow'])
<svg {{ $attributes->merge(['width'=>20,'height'=>20,'viewBox'=>'0 0 24 24','fill'=>'none','stroke'=>'currentColor','stroke-width'=>'1.5','stroke-linecap'=>'round','stroke-linejoin'=>'round','aria-hidden'=>'true']) }}>
@switch($name)
@case('phone')<path d="M8 3H4a1 1 0 0 0-1 1c0 9.4 7.6 17 17 17a1 1 0 0 0 1-1v-4l-5-2-2 2a13 13 0 0 1-6-6l2-2Z"/>@break
@case('pin')<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>@break
@case('calendar')<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18m-13 4h3m3 0h3m-9 3h3"/>@break
@case('instagram')<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".7" fill="currentColor"/>@break
@case('facebook')<path d="M14 22v-9h3l.5-4H14V7c0-1.5.5-2 2-2h2V1h-3c-4 0-6 2-6 6v2H6v4h3v9"/>@break
@case('google')<path d="M21 12h-9m9 0c0 6-7 10-12 7A8 8 0 1 1 18 6M21 12v4"/>@break
@case('close')<path d="m6 6 12 12M6 18 18 6"/>@break
@case('arrow-up')<path d="M12 20V4m-6 6 6-6 6 6"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('sparkle')<path d="m12 2 2.6 7.4L22 12l-7.4 2.6L12 22l-2.6-7.4L2 12l7.4-2.6Z"/>@break
@case('check')<path d="m5 12 4 4L19 6"/>@break
@case('search')<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>@break
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@default<path d="M5 19 19 5M5 5h14v14"/>
@endswitch
</svg>
