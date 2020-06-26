@inject('_settings', 'globalSettings')
@extends('layouts.' . $layout)
@section('sub-title')
@endsection
@section('content')
    <script>
      angular.module('app')
        .controller('ReceiptController', ReceiptController)
          ReceiptController.$inject = ['$scope', '$window'];
          function ReceiptController($scope, $window){
              $scope.order = JSON.parse($window.localStorage.getItem('recentOrder'));
              $scope.lines = $scope.order.lines;
          }
    </script>
    <div class="page-wrapper" ng-controller="ReceiptController">
        <div ng-show="!order">No order found</div>
        <div class="invoice-wrapper" ng-show="order">
            <div class="invoice-header">
                <h1>THANK YOU<br> for your order</h1>
            </div>
                <div class="invoice-header">
                    <h2>Receipt ID: @{{ order.receipt_id }}</h2>
                    Ordered from @{{(order.store_owner_user_id == {!! config('site.apex_user_id') !!}) ? '{!! Config::get('site.company_name') !!}' : '{!! session()->get('store_owner.full_name') !!}'}}
                </div>
                <section class="invoice-table">
                    <table class="cart-table">
                        <tbody class="cart-body">
                            <thead>
                                <th data-label="image"></th>
                                <th data-label="title">Title</th>
                                <th data-label="Size">Size</th>
                                <th data-label="quantity">Quantity</th>
                                <th data-label="price">Price</th>
                            </thead>
                            <tr ng-repeat="line in lines">
                                <td class="preview" data-label="image">
                                   <img src="@{{line.items[0].img_url}}" class="preview">
                                </td>
                                <td data-label="Title">
                                    <span>@{{line.items[0].variant_name ? line.items[0].variant_name : line.items[0].product_name}}</span>
                                </td>
                                <td data-label="Size">
                                    <label class="qty">Size:</label>
                                    <span>@{{line.items[0].option}}</span>
                                <td data-label="Quantity">
                                <label class="qty">Quantity:</label>
                                <span>@{{line.quantity}}</span>
                                </td>
                                <td data-label="Price">
                                    <span>@{{(line.price - line.discount_amount) | currency}}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section class="invoice-subtotal">
                    <div class="line-wrapper">
                        <label>Subtotal</label>
                        <span>@{{order.subtotal_price | currency}}</span>
                    </div>
                    <div class="line-wrapper">
                        <label>Discount</label>
                        <span>@{{-order.total_discount | currency}}</span>
                    </div>
                    <div class="line-wrapper">
                        <label>Shipping</label>
                        <span>@{{order.total_shipping | currency}}</span>
                    </div>
                    <div class="line-wrapper" v-show="//TODO">
                        <label>Sales Tax</label>
                        <span>@{{order.total_tax | currency}}</span>
                    </div>
                    <div class="line-wrapper">
                        <label>Total Price</label>
                        <span>@{{order.total_price | currency}}</span>
                    </div>
                </section>
                <br/>
                * Packages will ship between 3-5 days.
        </div>
    </div>
@endsection
