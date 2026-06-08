@extends('langding.index')
@section('title', 'Home')
@section('langding_content')
    @foreach ($homepageBlocks ?? [] as $block)
        @php($sectionType = $block->section_type)
        @if ($sectionType && view()->exists('langding.home.blocks.' . $sectionType))
            @include('langding.home.blocks.' . $sectionType)
        @endif
    @endforeach
@endsection
