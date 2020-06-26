@inject('_settings', 'globalSettings')
<table class="table">
    <thead>
        <tr>
            <th class='product-name'>Product</th>
            <th>Variant</th>
            <th>Option</th>
            <th>Quantity</th>
            <th class='product-price'>Price</th>
        </tr>
    </thead>
    <tbody>
        <tr ng-repeat='line in $parent.checkout.lines'>
            <td class="product-name">
                @{{ line.items[0].product_name }}
            </td>
            <td>
                @{{ line.items[0].variant_name }}
            </td>
            <td class="product-size">
                @{{ line.items[0].option }}
            </td>
            <td class="qty">
                @{{ line.quantity }}
            </td>
            <td class="product-price">
                <div ng-bind="(line.price - line.discount) * line.quantity | currency"></div>
            </td>
        </tr>
    </tbody>
</table>
<div class="confirm-totals">
    <div class="totals-right-col">
        <div class="total-line">
            <h4>
                Your Subtotal
            </h4>
            <div class="price-col">
                <span ng-bind="$parent.checkout.subtotal | currency"></span>
            </div>
        </div>
        <div class="total-line" ng-if="$parent.checkout.discount">
            <h4>
                Your Discount
            </h4>
            <div class="price-col">
                <span ng-bind="-$parent.checkout.discount | currency"></span>
            </div>
        </div>
        @if ($_settings->getGlobal('tax_calculation', 'show'))
            <div class="total-line">
                <h4>
                    <div>Tax</div>
                </h4>
                <div class="price-col">
                    <span ng-bind="$parent.checkout.tax | currency"></span>
                </div>
            </div>
        @endif
        <div class="total-line">
            <h4>Shipping</h4>
            <div class="price-col">
                    <span ng-bind="$parent.checkout.shipping | currency"></span>
            </div>
        </div>

        <div class="total-line total-price">
            <h4>Your Total</h4>
            <div class="price-col">
                <strong id="total">@{{ $parent.checkout.total | currency }}</strong><br>
                <a href="{{$_settings->getGlobal('return_policy', 'value')}}" class="site-blue" target="_blank">Return Policy</a><br>
            </div>
        </div>
    </div>
</div>
