@extends('salon.layouts.site')
@section('content')
@foreach($sections as $block)
    @if(array_key_exists($block['id'] ?? '', config('cms.home_sections')))
        @include('salon.blocks.'.$block['id'], [collect(['hero'=>'hero','intro'=>'intro','collection'=>'collection','ritual'=>'ritual','lookbook'=>'edit','journey'=>'journey','questions'=>'questions','finale'=>'finale'])->get($block['id']) => $block])
    @else
        @include('salon.partials.content-section', ['section' => $block])
    @endif
@endforeach
@endsection
