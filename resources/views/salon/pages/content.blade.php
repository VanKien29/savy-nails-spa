@extends('salon.layouts.site')
@section('content')
<div class="ks-container cms-page-title"><h1>{{ $page->title }}</h1></div>
@foreach($sections as $section)
    @include('salon.partials.content-section', ['section' => $section])
@endforeach
@endsection
