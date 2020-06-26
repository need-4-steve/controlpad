<div style="text-align:center;font-weight:bold;border-style:solid;border-width:1px;border-bottom:0px;padding:5px;">
    Customer Details
</div>
<table style="width:100%;text-align:left;border-style:solid;border-width:1px;">
    <thead>
        <th style="padding:5px;">
            Billing Address
        </th>
        <th style="padding:5px;">
            Shipping Address
        </th>
    </thead>
    <tr>
        <td style="width:50%;vertical-align:top;padding:5px;">
            @if(isset($buyer->billing_address->name))
            <div>
                {{$buyer->billing_address->name}}
            </div>
            @endif
            <div >
                {{$buyer->billing_address->line_1}}
            </div>
            <div>
                {{$buyer->billing_address->line_2}}
            </div>
            <div>
                {{$buyer->billing_address->city}} {{$buyer->billing_address->state}} {{$buyer->billing_address->zip}}
            </div>
        </td>
        <td style="width:50%;vertical-align:top;padding:5px;">
            @if(isset($buyer->shipping_address->name))
            <div>
                {{$buyer->shipping_address->name}}
            </div>
            @endif
            <div>
                {{$buyer->shipping_address->line_1}}
            </div>
            <div>
                {{$buyer->shipping_address->line_2}}
            </div>
            <div>
                {{$buyer->shipping_address->city}} {{$buyer->shipping_address->state}} {{$buyer->shipping_address->zip}}
            </div>
        </td>
    </tr>
</table>