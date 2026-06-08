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
                                                @include('langding.components.category-card-link', ['category' => $childCategory])
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
                                                        @include('langding.components.category-card-link', ['category' => $childCategory])
                                                        
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
