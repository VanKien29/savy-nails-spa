@props(['title'])
<details {{ $attributes->class(['accordion']) }}><summary>{{ $title }}<span aria-hidden="true">+</span></summary><div>{{ $slot }}</div></details>
