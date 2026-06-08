@extends('langding.index')

@section('title', $post['title'] ?? __('messages.post_detail'))
@section('meta_description', $post['excerpt'] ?? '')

@section('langding_content')

<div class="page-posts-category style-other">
    <div class="box-media">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="fs-15 text-black">{{ __('messages.home') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('post.category') }}" class="fs-15 text-black">{{ __('messages.news') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $post['title'] ?? __('messages.post_detail') }}</li>
                </ol>
            </nav>
            <h2 class="font-hanzel fs-35 mt-4 fw-400 text-center main-title">{{ $post['title'] ?? '' }}</h2>
            <div class="box-posts-content">
                {{-- Featured Image --}}


                {{-- Post Meta --}}
                {{-- <div class="d-flex align-items-center gap-4 mt-3 content-meta">
                    <div class="d-flex align-items-center gap-2">
                        <svg width="25" height="24" viewBox="0 0 25 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12.5 0C5.60753 0 3.35943e-05 5.04667 3.35943e-05 11.25C3.35943e-05 13.4183 0.685867 15.5187 1.9867 17.335C1.74045 20.0587 1.08045 22.0808 0.122117 23.0387C-0.00454973 23.1654 -0.0362164 23.3592 0.0437836 23.5192C0.114617 23.6621 0.26045 23.75 0.4167 23.75C0.435867 23.75 0.455034 23.7487 0.474617 23.7458C0.643367 23.7221 4.56337 23.1579 7.39878 21.5212C9.00878 22.1708 10.7238 22.5 12.5 22.5C19.3925 22.5 25 17.4533 25 11.25C25 5.04667 19.3925 0 12.5 0ZM6.6667 12.9167C5.74753 12.9167 5.00003 12.1692 5.00003 11.25C5.00003 10.3308 5.74753 9.58333 6.6667 9.58333C7.58587 9.58333 8.33337 10.3308 8.33337 11.25C8.33337 12.1692 7.58587 12.9167 6.6667 12.9167ZM12.5 12.9167C11.5809 12.9167 10.8334 12.1692 10.8334 11.25C10.8334 10.3308 11.5809 9.58333 12.5 9.58333C13.4192 9.58333 14.1667 10.3308 14.1667 11.25C14.1667 12.1692 13.4192 12.9167 12.5 12.9167ZM18.3334 12.9167C17.4142 12.9167 16.6667 12.1692 16.6667 11.25C16.6667 10.3308 17.4142 9.58333 18.3334 9.58333C19.2525 9.58333 20 10.3308 20 11.25C20 12.1692 19.2525 12.9167 18.3334 12.9167Z"
                                fill="#CF171C" />
                        </svg>
                        <span class="fs-20 text-black-50 fw-700">{{ $post['view_count'] ?? 200 }} {{ __('messages.views') }}</span>
                    </div>
                </div> --}}

                {{-- Post Title --}}
                {{-- <h1 class="mb-0 fs-30 fw-700">{{ $post['title'] ?? __('messages.untitled') }}</h1> --}}

                {{-- Post Content --}}
                <div class="box-posts-editor">
                    {!! $post['content'] ?? '' !!}
                </div>

                {{-- Categories & Share --}}
                <!-- <div class="categories d-lg-flex align-items-center justify-content-between">
                    <div class="categories-items d-lg-flex align-items-center">
                        <span class="fs-24 fw-700">{{ __('messages.categories') }}:</span>
                        <div class="d-flex align-items-center">
                            @if(!empty($categories))
                            @foreach($categories as $category)
                            <a href="javascript:void(0)" class="cat-item fs-16">{{ $category['name'] }}</a>
                            @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="share d-flex align-items-center gap-3">
                        <span class="fs-16 fw-500 text-black-50 me-3">{{ __('messages.share') }}:</span>
                        <svg width="23" height="24" viewBox="0 0 23 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18.2812 15.5625C16.8918 15.5625 15.6675 16.246 14.8984 17.2854L8.24953 13.8808C8.35992 13.5045 8.4375 13.1146 8.4375 12.7031C8.4375 12.145 8.32308 11.6145 8.12541 11.1264L15.0838 6.93919C15.8581 7.848 16.9964 8.4375 18.2812 8.4375C20.6076 8.4375 22.5 6.54511 22.5 4.21875C22.5 1.89239 20.6076 0 18.2812 0C15.9549 0 14.0625 1.89239 14.0625 4.21875C14.0625 4.75486 14.1729 5.26341 14.356 5.73553L7.37695 9.93506C6.60323 9.05325 5.48142 8.48438 4.21875 8.48438C1.89239 8.48438 0 10.3768 0 12.7031C0 15.0295 1.89239 16.9219 4.21875 16.9219C5.63109 16.9219 6.87614 16.2183 7.64227 15.1497L14.2693 18.5432C14.1472 18.9373 14.0625 19.3477 14.0625 19.7812C14.0625 22.1076 15.9549 24 18.2812 24C20.6076 24 22.5 22.1076 22.5 19.7812C22.5 17.4549 20.6076 15.5625 18.2812 15.5625Z"
                                fill="black" />
                        </svg>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    {{-- Related Posts --}}
    <div class="category-search-list">
        <div class="container-fluid">
            <h2 class="font-hanzel fs-40 mt-4 fw-400 text-center">{{ __('messages.related_posts') }}</h2>
            <div class="posts-list">
                <div class="row">
                    @if(!empty($relatedPosts) && count($relatedPosts) > 0)
                    @foreach($relatedPosts as $relatedPost)
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <div class="posts-item">
                            <a href="{{ route('post.detail', ['slug' => $relatedPost['slug']]) }}" class="link-overlay">
                                <div class="bg-img-cover"
                                    style="background-image: url('{{ $relatedPost['image'] ?? asset('langding/imgs/no-image.png') }}');">
                                </div>
                            </a>
                            <div class="posts-item-content">
                                <div class="d-flex align-items-center gap-2 fw-500">
                                    <a href="#" class="link-to-category fs-12 text-red">{{ __('messages.casumina_news') }}</a>
                                    <div class="calendar d-flex align-items-center gap-1 text-red">
                                        <img src="{{ asset('langding/imgs/calendar-white.svg') }}" alt="Calendar"
                                            class="img-fluid" width="18">
                                        <span class="fs-12">
                                            {{ \Carbon\Carbon::parse($relatedPost['published_at'] ?? $relatedPost['created_at'])->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                                <h3 class="posts-title line-3 fs-20 fw-700">
                                    <a class="text-black" href="{{ route('post.detail', ['slug' => $relatedPost['slug']]) }}">
                                        {{ $relatedPost['title'] ?? __('messages.untitled') }}
                                    </a>
                                </h3>
                                <div class="posts-footer d-flex align-items-center justify-content-between">
                                    <div class="posts-author d-flex align-items-center gap-2">
                                        {{-- <div class="posts-author-img d-flex align-items-center justify-content-center" style="background: #F52618; border-radius: 50%; width: 40px; height: 40px;">
                                            <i class="bi bi-person-circle text-white" style="font-size: 24px;"></i>
                                        </div> --}}
                                        <!-- <div class="posts-author-info">
                                            <div class="fs-16 fw-600">Admin</div>
                                            <div class="fs-14 fw-300">Casumina</div>
                                        </div> -->
                                    </div>
                                    <a href="{{ route('post.detail', ['slug' => $relatedPost['slug']]) }}">
                                        <svg width="80" height="32" viewBox="0 0 80 32" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <rect y="15" width="60.1622" height="2" fill="#F52618" />
                                            <circle cx="59.1348" cy="16" r="16" fill="#F52618" />
                                            <path
                                                d="M63.6783 16.6283L55.7637 24.5403C55.4164 24.8867 54.8537 24.8867 54.5056 24.5403C54.1583 24.1939 54.1583 23.6312 54.5056 23.2848L61.7924 16.0006L54.5064 8.71637C54.1591 8.36996 54.1591 7.80729 54.5064 7.46C54.8537 7.11359 55.4173 7.11359 55.7646 7.46L63.6792 15.3719C64.0211 15.7148 64.0211 16.2863 63.6783 16.6283Z"
                                                fill="white" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="col-12 text-center py-5">
                        <p class="text-muted fs-18">{{ __('messages.no_related_posts') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('langding.components.promotion-slide')
</div>
@endsection