<div style="text-align:center;font-weight:bold;border-style:solid;border-width:1px;border-bottom:0px;padding:5px;">
    Order Details
</div>
<table style="width:100%;border-style:solid;border-width:1px;">
    <thead>
        <tr style="text-align:left;">
        <th></th>
        <th>Product</th>
        <th>Variant</th>
        <th>Option</th>
        <th>Quantity</th>
        <th>Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subscription->lines as $line)
            @foreach($line->items as $item)
                <tr style="text-align:left;">
                    <td><img src="{{ substr_replace($item->img_url, '-url_xxs.', strrpos($item->img_url, '.'), strlen('.')) }}"></td>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->variant_name }}</td>
                    <td>{{ $item->option }}</td>
                    <td>{{ $line->quantity }}</td>
                    <td>${{ $line->price }}</td>
                </tr>
            @endforeach
        @endforeach
        <tr>
            <td colspan="4"></td>
            <td style="font-weight:bold;">Subtotal: </td>
            <td>${{ money_format('%.2n', $subscription->subtotal) }}</td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td style="font-weight:bold;">Discount:</td>
            <td>${{ $subscription->discount }} ({{$subscription->percent_discount}}%)</td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td style="font-weight:bold;">Tax:</td>
            <td>TBD</td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td style="font-weight:bold;">Shipping:</td>
            <td>TBD</td>
        </tr>
        <tr>
            <td colspan="4"></td>
            <td style="font-weight:bold;">Total:</td>
            <td>${{ ($subscription->subtotal - $subscription->discount) }}</td>
        </tr>
    </tbody>
</table>
@if(isset($buyer->card))
<table style="text-align:left;width:100%;border-style:solid;border-width:1px;border-top:0px">
    <thead>
        <th><span style="padding:5px;">Payment Method</span></th>
        <th><span style="padding:5px;">Expiration</span></th>
    </thead>
    <tr>
        <td width="50%"><span style="padding:5px;">{{ $buyer->card->card_type }} ending in {{ str_replace('*', '', $buyer->card->card_digits) }}</span></td>
        <td width="50%"><span style="padding:5px;">{{ substr_replace($buyer->card->expiration, '/', 2, 0) }}</span></td>
    </tr>
</table>
@else
    <div style="padding:5px;border-style:solid;border-width:1px;border-top:0px"> Card not on file. Please login and update card information.</div>
@endif