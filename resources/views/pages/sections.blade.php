@extends('layouts.site')
@section('content')
    @if($showcase ?? false)@include('partials.showcase')@endif
    @include('partials.sections')
@endsection
