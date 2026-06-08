    <div id="box-category" class="box-category" style="background: url('{{ asset('langding_nano/imgs/Slection3.png') }}') no-repeat center center;background-size: cover;">
        <div class="container-fluid">
            <div class="scroll-animate" data-animate="fadeInUp">
                <div class="title-with-line fw-500 fs-18 text-center text-light-red">{{ __('messages.products') }}</div>
                <h2 class="font-hanzel fs-32 fw-400 text-center mt-2 text-white">{{ __('messages.top_tire_manufacturer') }}</h2>
            </div>
            <div class="tabs-scroll-wrap">
                <ul class="nav nav-tabs tabs-scroll" id="box-category-title" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fs-14 font-hanzel" id="cat-all" data-bs-toggle="tab"
                            data-bs-target="#cat-all-pane" type="button" role="tab" aria-controls="cat-all-pane"
                            aria-selected="true">{{ __('messages.all_products') }}</button>
                    </li>
                    @foreach ($categories as $index => $category)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fs-14 font-hanzel" id="cat-{{ $category->id }}" data-bs-toggle="tab"
                                data-bs-target="#cat-{{ $category->id }}-pane" type="button" role="tab"
                                aria-controls="cat-{{ $category->id }}-pane"
                                aria-selected="false">{{ $category->category_name ?? $category->code }}</button>
                        </li>
                    @endforeach
                </ul>
                <span class="tabs-scroll-hint" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <polyline points="9 6 15 12 9 18"></polyline>
                    </svg>
                </span>
            </div>
        </div>
        <div class="tab-content" id="box-category-content">
            {{-- Tab "Tất cả" - hiển thị tất cả child categories --}}
            <div class="tab-pane fade show active" id="cat-all-pane" role="tabpanel" aria-labelledby="cat-all"
                tabindex="0">
                <div class="box-category-slider">
                    @forelse($allCategoryRows as $rowIndex => $categoryRow)
                        <div class="box-category-slider-item">
                            <div class="container-fluid">
                                <div class="row">
                                    @foreach ($categoryRow as $childCategory)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="product-item text-center scroll-animate" data-animate="fadeInUp">
                                                <!-- <h3 class="product-item-title text-uppercase text-red fs-18 font-hanzel">
                                            {{ $childCategory->category_name ?? $childCategory->code }}
                                        </h3> -->
                                                <!-- <div class="product-item-intro fs-16 fw-300 mx-auto line-2 mt-3">
                                            {{ $childCategory->category_description ?? '' }}
                                        </div> -->
                                                <div class="product-item-img position-relative mx-auto">
                                                    <a
                                                        href="{{ route('category', ['slug' => $childCategory->category_translation_slug ?? $childCategory->id]) }}">
                                                        <img src="{{ $childCategory->category_image ?? asset('langding/imgs/product.png') }}"
                                                            alt="{{ $childCategory->category_name ?? 'Category' }}"
                                                            class="img-fluid mx-auto" width="383">
                                                    </a>
                                                </div>
                                                <div class="product-item-view mt-3">
                                                    <a class="text-white fs-16 d-flex align-items-center justify-content-center gap-2"
                                                        href="{{ route('category', ['slug' => $childCategory->category_translation_slug ?? $childCategory->id]) }}">
                                                        <span>{{ __('messages.detail') }}</span>
                                                        <svg width="11" height="12" viewBox="0 0 11 12"
                                                            fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M2.00109 1.37635L9.28725 0.738899M9.28725 0.738899L9.92471 8.02506M9.28725 0.738899L0.738909 10.9264"
                                                                stroke="#ffffff" stroke-width="1.47765"
                                                                stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="container-fluid">
                            <div class="text-center py-5">
                                <p class="fs-18">{{ __('messages.no_categories') ?? 'Không có danh mục nào' }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Tab cho từng parent category - hiển thị child categories của parent đó --}}
            @foreach ($categories as $category)
                <div class="tab-pane fade" id="cat-{{ $category->id }}-pane" role="tabpanel"
                    aria-labelledby="cat-{{ $category->id }}" tabindex="0">
                    <div class="box-category-slider">
                        @if (isset($categoryChildRows[$category->id]) && count($categoryChildRows[$category->id]) > 0)
                            @foreach ($categoryChildRows[$category->id] as $rowIndex => $categoryRow)
                                <div class="box-category-slider-item">
                                    <div class="container-fluid">
                                        <div class="row">
                                            @foreach ($categoryRow as $childCategory)
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="product-item text-center scroll-animate"
                                                        data-animate="fadeInUp">
                                                        <h3
                                                            class="media-item-big-content px-4 text-uppercase text-red fs-18 font-hanzel">
                                                            {{ $childCategory->category_name ?? $childCategory->code }}
                                                        </h3>
                                                        <!-- <div class="product-item-intro fs-16 fw-300 mx-auto line-2 mt-3">
                                            {{ $childCategory->category_description ?? '' }}
                                        </div> -->
                                                        <div class="product-item-img position-relative mx-auto">
                                                            <img src="{{ $childCategory->category_image ?? asset('langding/imgs/product.png') }}"
                                                                alt="{{ $childCategory->category_name ?? 'Category' }}"
                                                                class="img-fluid mx-auto" width="383">
                                                        </div>

                                                        <div class="product-item-view mt-3">
                                                            <a class="text-white fs-16 d-flex align-items-center justify-content-center gap-2"
                                                                href="{{ route('category', ['slug' => $childCategory->category_translation_slug ?? $childCategory->id]) }}">
                                                                <span>{{ __('messages.detail') }}</span>
                                                                <svg width="11" height="12" viewBox="0 0 11 12"
                                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M2.00109 1.37635L9.28725 0.738899M9.28725 0.738899L9.92471 8.02506M9.28725 0.738899L0.738909 10.9264"
                                                                        stroke="#ffffff" stroke-width="1.47765"
                                                                        stroke-linecap="round" stroke-linejoin="round" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                        
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="container-fluid">
                                <div class="text-center py-5">
                                    <p class="fs-18 text-white">{{ __('messages.no_categories') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
