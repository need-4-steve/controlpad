@extends('layouts.' . $layout)
@inject('_settings', 'globalSettings')
@section('sub-title')
@if($repShopping)
Shopping Cart
@else
<section class="public-cart">
    <h1 class="no-top">Shopping Cart</h1>
    <p> @{{shipping_error}}</p>
</section>
@endif
@endsection
@section('angular-controller')
ng-controller="CartController"
@endsection
@section('content')
    <div ng-init="autoshipUrl = '{!! $autoshipUrl !!}'"/>
    <section class="public-cart" ng-hide="loading">
        <span class='warning-message' ng-if="inventoryError"> @{{ inventoryMessage }} </span>

        <div class="public-cart-container">
            <div class="public-cart-wrapper">
                <table>
                    <thead class="main-table">
                        <tr>
                            <th></th>
                            <th>Product</th>
                            <th>Variant</th>
                            <th>Option</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-if="cart" class="cart-item" ng-repeat="line in cart.lines">
                            <td class="featured-image">
                                <img ng-src="@{{ line.items[0].img_url }}" class="inventory-image">
                            </td>
                            <td data-label="Product">
                                <span ng-bind="line.items[0].product_name"></span>
                            </td>
                            <td data-label="Variant">
                                <span ng-bind="line.items[0].variant_name"></span>
                            </td>
                            <td data-label="Size">
                                <span ng-bind="line.items[0].option"></span>
                            </td>
                            <td data-label="Quantity">
                             <div class="quantity-wrapper">
                                <input type="number" ng-model="line.quantity" ng-change="checkInv(line)" ng-model-options='{ debounce: 500 }'/>
                            </div>
                            </td>
                            <td data-label="Price">
                                <div ng-if="line.discount" class="amount" ng-bind="(line.price - line.discount) * line.quantity | currency"></div>
                                <div ng-if="!line.discount" class="amount" ng-bind="line.price * line.quantity | currency"></div>
                            </td>
                            <td class="">
                                <span><i class="lnr lnr-cross" ng-click="deleteCartLine(line.item_id)"></i></span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p ng-if="emptyCart" class="empty-cart">
                    (Cart is empty)
                </p>
                <div class="proceed-details">
                    <div class="cart-total">
                        <h4 colspan="3" class="align-right">Your SubTotal:</h4>
                        <p>
                            <strong ng-bind="cart.subtotal | currency"></strong>
                        </p>
                    </div>
                    <div class="accepted-cards">
                        <img src="https://s3-us-west-2.amazonaws.com/controlpad/CreditCard-Icons.png">
                    </div>
                    <a ng-if="!emptyCart" ng-click="checkout()" class="checkout-btn">Checkout</a>
                </div>
            </div>
            @if($_settings->getGlobal('autoship_enabled', 'show'))
            <form id="autoship-container">
                <div ng-if="autoshipPlans.length > 0">
                    <h4>Make your cart and autoship and save!</h4>
                    <div ng-repeat="plan in autoshipPlans">
                        <label>
                            <input type="radio" ng-model="model.selectedPlan" ng-value="plan"> @{{ plan.title + ' - ' + plan.description }}
                        </label><br/>
                    </div>
                    <div>
                        <label>
                            <input type="radio" ng-model="model.selectedPlan" ng-value="null"> No thanks.
                        </label><br/>
                    </div>
                </div>
            </form>
            @endif
        </div>
    </section>
    <div class="align-center">
        <img class="loading" src="{{$_settings->getGlobal('loading_icon', 'value')}}" ng-show="loading">
    </div>
@endsection
@section('scripts')
    <script>
        @if(isset($user_id))
            user_id = {{ $user_id }};
        @endif
        method = 'show';
    </script>
@stop
