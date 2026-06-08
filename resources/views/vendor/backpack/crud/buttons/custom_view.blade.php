@php
$translation = $entry->translations()->where('language', 'vi')->first();
$slug = $translation ? $translation->slug : null;
@endphp

@if ($slug)
<a href="{{ route('post.detail', ['slug' => $slug]) }}"
    class="btn btn-sm btn-link"
    target="_blank"
    title="Xem bài viết trên website"
    data-toggle="tooltip">
    <i class="la la-external-link-alt"></i> Xem trên web
</a>
@endif