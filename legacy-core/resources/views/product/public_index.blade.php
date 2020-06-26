@extends('layouts.store')
@section('angular-controller')
ng-controller="StoreController"
@endsection
@section('content')

<div class="page-wrapper">
<section class="sub-header-cat" ng-class="{'fixedCat': fixedCat}">
    <ul class="parent-categories" slick settings="catSlider" init-onload="true" ng-if="catHierarchy.length">
        <li ng-repeat="category in catHierarchy" class="categories">
            <div class="category-image">
                <img ng-src="@{{category.media[0].url_sm}}">
            </div>
            <a href="/store/@{{category.id}}" ng-bind="category.name"></a>
        </li>
    </ul>
</section>
@include('product.partials.sort_form')
@include('product.partials.products')
@include('pagination.custom', ['paginated' => $store->products->appends($store->queryStrs)])
</div>
@endsection
