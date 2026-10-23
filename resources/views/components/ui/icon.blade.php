@props(['name'=>'arrow'])
<svg {{ $attributes->merge(['width'=>24,'height'=>24,'viewBox'=>'0 0 24 24','fill'=>'none','stroke'=>'currentColor','stroke-width'=>'1.5','aria-hidden'=>'true']) }}>@switch($name)@case('check')<path d="m5 12 4 4L19 6"/>@break @case('plus')<path d="M12 5v14M5 12h14"/>@break @default<path d="M5 19 19 5M5 5h14v14"/>@endswitch</svg>
