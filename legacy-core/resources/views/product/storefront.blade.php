@inject('_settings', 'globalSettings')
@extends('layouts.store')
@section('angular-controller')
ng-controller="StoreController"
@endsection
@section('content')
@if ($store->showStoreBanner())
    @if (cache('is_live_streaming.'.session('store_owner.public_id'), null))
        <cp-live-video-public
            :stream-obj="{{ $liveVideo['stream'] }}"
            :video-obj="{{ $liveVideo['video'] }}"
            :inventory-obj="{{ $liveVideo['inventory'] }}"
            :store-owner="{{ isset($store->rep) ? $store->rep : json_encode(['id' => 1]) }}"></cp-live-video-public>
    @else
        @if ($store->settings['show_store_banner'])
        <section class="home-blog">
            <div class="cp-left-col main-img">
                <img src="{{ $store->settings['banner_image_1'] or ''}}" class="main-img" />
            </div>
            <div class="cp-left-col side-img">
                <img src="{{ $store->settings['banner_image_2'] or ''}}" class="side-img" />
                <img src="{{ $store->settings['banner_image_3'] or ''}}" class="side-img" />
            </div>
        </section>
        @endif
    @endif
@endif
@if (!isset($store->rep) || !$store->rep->settings->hide_products)
    <section class="category-carousel">
    @if ($store->showStoreBanner())
        <ul class="category-list" slick settings="catSlider" init-onload="true">
            @foreach ($store->categories as $category)
                @if ($category->show_on_store)
                    <li class="categories">
                        <a href="/store?category={{$category->id}}">
                            <div class="category-image">
                                @if(isset($category->media[0]))
                                    <img src="{{$category->media[0]->url_sm}}">
                                @endif
                            </div>
                            <span>{{ $category->name }}</span><br/>
                            <span>{{ $category->header }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
        @else
        <ul class="category-list indiv-category" ng-class="{bold: $index == selectedIndex}">
            @foreach ($store->categories as $category)
                @if ($category->show_on_store)
                    <li class="categories">
                        <a href="/store?category={{$category->id}}">
                            <div class="category-image">
                                @if(isset($category->media[0]))
                                    <img src="{{$category->media[0]->url_sm}}">
                                @endif
                            </div>
                            <span>{{ $category->name }}</span><br/>
                            <span>{{ $category->header }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    @endif
    </section>
    <div class="page-wrapper">
    @if (!$store->showStoreBanner())
    <section class="sub-by-category" style="display: none;">
        <ul>
        @if ($store->subCategories)
         @foreach ($store->subCategories as $subCategory)
            <li>
                <a href="/store?category={{ $subCategory->id }}"><span>{{ $subCategory->name }}</span><br/></a>
            </li>
         @endforeach
         @endif
        </ul>
    </section>
    @endif
    @include('product.partials.sort_form')
    @include('product.partials.products')
    {{ $store->products->appends($store->queryStrs)->links() }}
    </div>
@endif
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <script>
    (function () {
        document.body.removeAttribute("ng-cloak");
    }())
    </script>
@endsection
