{{-- Executable snippets are supplied by authorized administrators only. --}}
@unless($isDemo || ($isPreview ?? false))
{!! $settings[$placement] ?? '' !!}
@endunless
