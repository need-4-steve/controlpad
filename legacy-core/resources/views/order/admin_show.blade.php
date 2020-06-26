@extends('layouts.boss')
@section('angular-controller')
ng-controller="OrderController as order"
@endsection
@section('sub-title')
     Invoice ID: {{ $order->receipt_id }} &nbsp;
     <img src="/barcode/img/{{$order->receipt_id}}" alt="barcode"/>
@stop
@section('content')
    <div class="row masonry" ng-cloak ng-init="order.showOrder('{{$order->receipt_id}}')">
        <div class="col">
            <div class="panel panel-default" >
                <div class="panel-heading">
                    <h2 div class="panel-title">Order Details</h2>
                </div>
                    <table class="table table-striped">
                        <tr>
                            <th>Order Date:</th>
                            <td>@{{order.order.created_at | myDateFormat}}</td>
                        </tr>
                        <tr>
                            <th>Shipping:</th>
                            <td>@{{order.order.total_shipping | currency}}</td>
                        </tr>
                        <tr>
                            <th>Tax:</th>
                            <td>@{{order.order.total_tax | currency}}</td>
                        </tr>
                        <tr>
                            <th>Subtotal Price:</th>
                            <td>@{{order.order.subtotal_price | currency}}</td>
                        </tr>
                        <tr>
                            <th><strong>Total Price:</strong></th>
                            <td>@{{order.order.total_price | currency}}</td>
                        </tr>
                    </table>
            </div>
            <div class="panel panel-default" >
                <div class="panel-heading">
                    <h2 div class="panel-title">Products Ordered</h2>
                </div>
                <table class="table table-striped" ng-repeat="orders in order track by $index">
                        <tr ng-repeat="lines in orders.lines">
                            <th>Name:</th>
                            <td>@{{lines.name}}</td>
                            <th>Print:</th>
                            <td>@{{lines.item.print}}</td>
                            <th>Size:</th>
                            <td>@{{lines.item.size}}</td>
                            <th>Quantity:</th>
                            <td>@{{lines.quantity}}</td>
                            <th>Price:</th>
                            <td>@{{lines.price | currency}}</td>
                        </tr>
                </table>
            </div>
        </div>
        @include('_helpers.notes_mod',array('primary_record'=>$primary_record))
    </div>
@endsection
