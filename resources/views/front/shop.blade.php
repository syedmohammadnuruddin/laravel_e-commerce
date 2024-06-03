@extends('front.layouts.app')

@section('content')
@php
    $priceMin = $priceMin ?? 0;
    $priceMax = $priceMax ?? 1000;
@endphp

<!-- Breadcrumb Section -->
<section class="section-5 pt-3 pb-3 mb-3 bg-white">
    <div class="container">
        <div class="light-font">
            <ol class="breadcrumb primary-color mb-0">
                <li class="breadcrumb-item"><a class="white-text" href="{{route('front.index')}}">Home</a></li>
                <li class="breadcrumb-item active">Shop</li>
            </ol>
        </div>
    </div>
</section>

<!-- Shop Section -->
<section class="section-6 pt-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 sidebar">
                <!-- Categories -->
                <div class="sub-title">
                    <h2>Categories</h2>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="accordion accordion-flush" id="accordionExample">
                            @if ($sideCategories->isNotEmpty())
                                @foreach ($sideCategories as $key => $category)
                                <div class="accordion-item">
                                    @if ($category->sub_category->isNotEmpty())
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne-{{$key}}" aria-expanded="false" aria-controls="collapseOne-{{$key}}">
                                            {{$category->name}}
                                        </button>
                                    </h2>
                                    @else
                                    <a href="{{route('front.shop', $category->slug)}}" class="nav-item nav-link {{($categorySelected == $category->id) ? 'text-primary' : ''}}">{{$category->name}}</a>
                                    @endif

                                    @if ($category->sub_category->isNotEmpty())
                                    <div id="collapseOne-{{$key}}" class="accordion-collapse collapse {{($categorySelected == $category->id) ? 'show' : ''}}" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <div class="navbar-nav">
                                                @foreach ($category->sub_category as $subCategory)
                                                <a href="{{route('front.shop', [$category->slug, $subCategory->slug])}}" class="nav-item nav-link {{($subCategorySelected == $subCategory->id) ? 'text-primary' : ''}}">{{$subCategory->name}}</a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Brands -->
                <div class="sub-title mt-5">
                    <h2>Brand</h2>
                </div>

                <div class="card">
                    <div class="card-body">
                        @if ($brands->isNotEmpty())
                            @foreach ($brands as $brand)
                            <div class="form-check mb-2">
                                <input {{(in_array($brand->id, $brandsArray)) ? 'checked' : ''}} class="form-check-input brand-label" type="checkbox" name="brand[]" value="{{$brand->id}}" id="brand-{{$brand->id}}">
                                <label class="form-check-label" for="brand-{{$brand->id}}">
                                    {{$brand->name}}
                                </label>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Price Filter -->
                <div class="sub-title mt-5">
                    <h2>Price</h2>
                </div>

                <div class="card">
                    <div class="card-body">
                        <input type="text" class="js-range-slider" name="my_range" value="" />
                    </div>
                </div>
            </div>

            <!-- Products Section -->
            <div class="col-md-9">
                <div class="row pb-3">
                    <div class="col-12 pb-1">
                        <div class="d-flex align-items-center justify-content-end mb-4">
                            <div class="ml-2">
                                <div class="btn-group">
                                    <select id="sort" class="form-control">
                                        <option value="">Sorting</option>
                                        <option value="latest">Latest</option>
                                        <option value="price_high">Price High</option>
                                        <option value="price_low">Price Low</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    

                    @if ($products->isNotEmpty())
                        @foreach ($products as $product)
                        @php
                            $productImage = $product->product_images->first();
                        @endphp
                        <div class="col-md-4">
                            <div class="card product-card">
                                <div class="product-image position-relative">
                                    <a href="{{route('front.product', $product->slug)}}" class="product-img">
                                        @if(!empty($productImage->image))
                                        <img class="card-img-top" src="{{asset('uploads/product/small/'.$productImage->image)}}">
                                        @else
                                        <img class="card-img-top" src="{{asset('admin-assets/img/default-150x150.png')}}">
                                        @endif
                                    </a>
                                    <a class="wishlist" href="222"><i class="far fa-heart"></i></a>
                                    <div class="product-action">
                                        <a class="btn btn-dark" href="javascript:void(0);" onclick="addToCart({{$product->id}})">
                                            <i class="fa fa-shopping-cart"></i> Add To Cart
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body text-center mt-3">
                                    <a class="h6 link" href="product.php">{{$product->title}}</a>
                                    <div class="price mt-2">
                                        <span class="h5"><strong>${{$product->price}}</strong></span>
                                        @if ($product->compare_price != "")
                                        <span class="h6 text-underline"><del>${{$product->compare_price}}</del></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @endif

                    <!-- Pagination -->
                    <div class="col-md-12 pt-5">
                        {{ $products->withQueryString()->links() }}
                        {{-- <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-end">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                </li>
                                <li class="page-item"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('customJs')
<script>
    $(document).ready(function() {
        // Select the option based on the 'sort' query parameter
        var sortValue = "{{ request()->get('sort') }}";
        $("#sort").val(sortValue);

        // Initialize the range slider
        const rangeSlider = $(".js-range-slider").ionRangeSlider({
            type: "double",
            min: 0,
            max: 1000,
            from: {{ json_encode($priceMin) }}, // Set initial 'from' value
            to: {{ json_encode($priceMax) }}, // Set initial 'to' value
            step: 10,
            skin: "round",
            max_postfix: "+",
            prefix: "$",
            onFinish: function(data) {
                apply_filters(); // Apply filters when slider finishes
            }
        });

        // Get the slider instance
        var slider = $(".js-range-slider").data("ionRangeSlider");

        // Trigger filter application when brand selection changes
        $(".brand-label").change(function() {
            apply_filters();
        });

        // Trigger filter application when sorting selection changes
        $("#sort").change(function(){
            apply_filters();
        });

        // Function to apply filters
        function apply_filters() {
            var brands = [];
            // Collect selected brand values
            $(".brand-label:checked").each(function() {
                brands.push($(this).val());
            });

            // Get current URL and prepare for modification
            var url = window.location.href.split('?')[0];
            var separator = url.includes('?') ? '&' : '?';
            var price_min = slider.result.from; // Get slider 'from' value
            var price_max = slider.result.to; // Get slider 'to' value

            // Append price range to URL
            url += separator + 'price_min=' + price_min + '&price_max=' + price_max;

            // Append selected brands to URL
            if (brands.length > 0) {
                url += '&brand=' + brands.join(',');
            }

            // Append selected sorting option to URL
            var sort = $("#sort").val();
            if (sort) {
                url += '&sort=' + sort;
            }

            // Redirect to the modified URL
            window.location.href = url;
        }
    });
</script>

 
@endsection
