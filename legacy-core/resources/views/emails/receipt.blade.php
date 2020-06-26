@extends('emails.layouts.basic')
@section('body')
<div class="receipt-wrapper" style="min-width: 400px; margin: 10 auto;  max-width: 800px; border-radius: 2px; text-align: center; font-family: Fira Sans Ultralight, sans-serif, Verdana, sans-serif; font-size: 1.35em;">
    <div class="header" style="min-height: 80px; width: 100%;">
        <img style="height: auto" src="https://s3-us-west-2.amazonaws.com/controlpad/cp-logo-bk.png" alt="{{ config('site.company_name') }}">
    </div>
    <h4 style="font-size: 1.2em; font-weight: 200">Receipt for {{$user_name or 'Customer'}}</h4>
    <div class="clear" style="margin-top: 25px"></div>
    <div style="width: 100%; border-bottom: 1px solid #333333;"></div>
    <div class="content">
        <p style="font-size: .8em; font-weight: 300; line-height: 1.3em;"> Thank you for your purchase! If you need anything or have any questions about the product or payment, please let us know.</p>
        <div style="float: left; margin-left: 30px; text-align: left;">
            <p style="font-size: .7em;"><strong>Ship To:</strong></p>
            <p style="font-size: .6em";>{{$user_name or 'Customer'}}</p>
            <p style="font-size: .6em";>{{$order->addresses[0]->address_1}}</p>
            <p style="font-size: .6em";>{{$order->addresses[0]->city}}, {{$order->addresses[0]->state}}, {{$order->addresses[0]->zip}}</p>
        </div>
        <div class="description Table" style="float: right">
            <table>
                <tr>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Total: </td>
                    <td>${{$order->subtotal_price}}</td>
                </tr>
                <tr>
                    <td>Order Id: </td>
                    <td>{{$order->id}}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="clear" style="margin-top: 25px"></div>
    <div class="footer">
        <p style="font-size: .8em";><?php echo date("F j, Y"); ?></p>
    </div>
</div>
@stop
@section('footer')
    @parent
@stop
