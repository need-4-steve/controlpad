@extends('layouts.store')
@section('meta')
    <meta property="og:url" content="/store/product/{{ $product->slug }}" />
    <meta property="og:type" content="product" />
    <meta property="og:title" content="{{ $product->name }}" />
@endsection
@section('angular-controller')
ng-controller="StoreController"
@endsection
@section('content')
<div class="page-wrapper" ng-controller="ProductShowController">
    @include('_helpers.errors')
    @include('_helpers.message')
    <span class='warning-message' ng-if="minMaxError"> @{{ errorMessage }} </span>
    <div class="success-modal" ng-show="successMessage">
        <div class="box">
            <p>Successfully added to cart!</p>
            <div class="buttons">
                <a href="/store" >Continue Shopping</a>
                <a href="/cart">Go to Cart</a>
            </div>
        </div>
    </div>
    <section class="product-show">
            @if (request()->has('event-id'))
                <div ng-init='eventId = {{ request()->get('event-id')}}'></div>
            @endif
            <div class="product-images product-images-box" ng-init='
                product = {{json_encode($product)}};
                cartLineObj.item_id = selectedItem.id;
                variants = {{json_encode($product->variants)}};
                selectedVariant = variants[0];
                items = selectedVariant.items;
                selectedItem = items[0];
                images = (selectedVariant.images.length) ? selectedVariant.images : product.images;
                mainImage = images[0];
                description = (selectedVariant.description.length) ? selectedVariant.description : product.long_description;'>

            <div class="image-block">
                <img class="large-image" ng-src="@{{ mainImage.url }}" alt="">
                <div class="image-bar" ng-show="images.length > 1">
                    <ul>
                        <li ng-repeat="image in images track by image.id">
                            <img class="small-image" ng-src="@{{ image.url }}" alt="" ng-click="selectImage(image)">
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="product-details product-details-box">
            <span class='warning-message' ng-if="inventoryError"> @{{ inventoryMessage }} </span>


            <form>
                <div class="left-col left-col3">
                <b><h2>{{ $product->name }}</h2></b>
                @if(is_null($store_owner))
                        <p class="price">@{{ selectedItem.retail_price | currency }}</p>
                    @else
                        @if(app('globalSettings')->getGlobal('rep_custom_prices', 'show') && !is_null(selectedItem.inventory_price))
                            <p class="price">@{{ selectedItem.inventory_price | currency }}</p>
                        @else
                            <p class="price">@{{ selectedItem.retail_price | currency }}</p>
                        @endif
                    @endif
                <div class="line" ng-if="variants.length > 1">

                        <div class=select-wrapper>
                            <label>{{$product->variant_label}}:</label>
                            <select ng-change="selectItems(selectedVariant)" ng-options="variant as variant.name for variant in variants track by variant.id" ng-model="selectedVariant">
                            </select>
                        </div>
                     </div>
                    <div class="line" ng-if="selectedVariant">
                            <div class=select-wrapper>
                                <label ng-if="selectedVariant">@{{ selectedVariant.option_label }}:</label>
                                <select ng-change="itemSelected(selectedItem)" ng-options="item as item.option for item in items track by item.id" ng-model="selectedItem">
                                </select>
                            </div>
                     </div>
                     <div class="line">
                        <div class="quantity-wrapper">
                        <label>Quantity: </label>
                        <input
                            type="number"
                            ng-model="cartLineObj.quantity"
                            ng-change="checkInv()"
                            value="1"
                            min="1"
                            autofocus />
                        </div>
                    </div>

                    <button ng-click="addToCart()" class="add-to-cart clear-both-float-left">Add to Cart</button>
                    <div class="left-col left-col2">
                <ul class="description">
                <li><p>@{{ description }}</p></li>
                </ul>
            </div>
                    <p class="back clear-both-float-left">
                        <a class="linkback" href="javascript:history.back(-1);"><i class="lnr lnr-arrow-left"></i>Back to Browse</a>
                    </p>
                </div>
            </form>
        </div>
    </section>
</div>
<style type="text/css">
.header .menu-title{
    top:5px !important;
}
.product-show .image-block .large-image{
    max-width: 100%;
    max-height: 100%;
}
.clear-both-float-left
{
    float:left;clear:both;
}
.price
{
    font-weight:300 !important;
    font-size:32px !important;
}
.product-images-box{ float:left; width:40%;}
.product-details-box{ float:right; width:50% !important;}
.product-show{max-width:900px !important;}
.product-details .left-col2 {

    width: 100% !important;
    clear:both;
}
.product-details .left-col3 {
    margin: 0 auto;
	width:100% !important;
	clear: both;
}
.product-details .quantity-wrapper {
    float: left !important;
    height: 40px;
    line-height: 40px;
    text-indent:0px !important;
}
.linkback{
    display: inline-block;
}
.lnr{
    vertical-align: middle !important;
    margin-right: 4px !important;
}
.product-details .select-wrapper{
text-indent:0px !important;
}
.product-images .main-img{
	width:350px !important;
}
.product-details button{
	float:left !important;
	margin-bottom: 30px;
	margin-top: 30px;
}
.product-details .select-wrapper {
    float: left !important;
}

@media screen and (max-width: 1287px) {


}
@media (min-width: 1025px) and (max-width: 1287px) {
	.product-images-box{ float:left; width:45%;   float:left !important; box-sizing: border-box;}
.product-details-box{ float:right; width:45% !important;  float:right !important; text-align:left;}

}

@media screen and (max-width: 1024px) {

	.product-details-box {

    float: left !important;
    margin: 0 !important;
    width: 100% !important; padding: 20px 0px !important;box-sizing: border-box;
}
.product-details {
    float: none;
    margin: 75px auto 25px;
    max-width: inherit !important;
    width: 100% !important;
}
.product-details{padding:10px 20px !important;}
.product-details ul p {
    font-weight: 100;
    margin: 6px 0  !important;
}
.product-images-box {

    float: left !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
}
.product-show {


}
.header .header-options li{}
.header .header-options li{margin:0 !important;}

}
.header .header-options li{margin:0 !important;}
@media screen and (max-width: 650px)
{
.product-details h2 {
    text-align: left !important;
}}
</style>
@endsection
