@inject('_settings', 'globalSettings')
@extends('layouts.store')
@section('sub-title')
@endsection
@section('content')
    <div class="page-wrapper">
        <div class="invoice-wrapper">
            <div class="invoice-header">
                <h1>
                    THANK YOU<br> for your
                    @if (count($orders) > 1)
                        orders
                    @else
                        order
                    @endif
                </h1>
            </div>
            @foreach ($orders as $order)
                <div class="invoice-header">
                    <h2>Invoice ID: {{ $order->receipt_id }}</h2>
                    Ordered from
                    @if ($order->store_owner_user_id == config('site.apex_user_id'))
                        {{ Config::get('site.company_name') }}
                    @else
                        {{ session()->get('store_owner.full_name') }}
                    @endif
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
                            @foreach($order->lines as $line)
                            <tr v-for="item in eInvoiceRequest.cartItems">
                                <td class="preview" data-label="image">
                                    @if (isset($line->item->product->media[0]->url_xs))
                                   <img src="{{$line->item->product->media[0]->url_xs}}" class="preview">
                                   @endif
                                </td>
                                <td data-label="Title">
                                    <span>{{$line->name}}</span>
                                </td>
                                <td data-label="Size">
                                    <label class="qty">Size:</label>
                                    <span>{{$line->item->size}}</span>
                                <td data-label="Quantity">
                                <label class="qty">Quantity:</label>
                                <span>{{$line->quantity}}</span>
                                </td>
                                @if (isset($line->discount_amount) && $line->discount_amount > 0)
                                    <td data-label="Price">
                                        <span>{{money_format('%.2n', ($line->price - $line->discount_amount))}}</span>
                                    </td>
                                @else
                                    <td data-label="Price">
                                        <span>{{money_format('%.2n', $line->price)}}</span>
                                    </td>

                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
                <section class="invoice-subtotal">
                    <div class="line-wrapper">
                        <label>Subtotal</label>
                        <span>{{money_format('%.2n', $order->subtotal_price)}}</span>
                    </div>
                    <div class="line-wrapper">
                        <label>Discount</label>
                        <span>{{money_format('%.2n', $order->total_discount)}}</span>
                    </div>
                    <div class="line-wrapper">
                        <label>Shipping</label>
                        <span>{{money_format('%.2n', $order->total_shipping)}}</span>
                    </div>
                    @if($_settings->getGlobal('calculate_taxes', 'show'))
                    <div class="line-wrapper">
                        <label>Sales Tax</label>
                        <span>{{money_format('%.2n', $order->total_tax)}}</span>
                    </div>
                    @endif
                    <div class="line-wrapper">
                        <label>Total Price</label>
                        <span>{{money_format('%.2n', $order->total_price)}}</span>
                    </div>
                </section>
                <br/>
                * Packages will ship between 3-5 days.
            @endforeach
        </div>
    </div>
@endsection
