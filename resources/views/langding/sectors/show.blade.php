@extends('langding.index')
@section('title', $sectorTranslation->meta_title ?? $sectorTranslation->name ?? 'Sector')
@section('langding_content')
    @foreach ($sectorBlocks ?? [] as $block)
        @php($sectionType = $block->section_type)
        @if ($sectionType === 'footer')
            @continue
        @endif
        @if ($sectionType && view()->exists('langding.home.blocks.' . $sectionType))
            @include('langding.home.blocks.' . $sectionType)
        @endif
    @endforeach
@endsection
