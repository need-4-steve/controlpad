@extends('layouts.'.$layout)
@inject('_settings', 'globalSettings')
@section('sub-title')
@endsection
@section('content')
    <style>
        .coupon-applied {
            margin-top: 15px;
            color: #5cb85c;
        }
        .agree {
            color: #0000EE;
        }
        input:disabled {
            background-color: #f5f5f5
        }
        select:disabled {
            background-color: #f5f5f5
        }
        .checkbox-label {
            padding-left: 5px;
        }
    </style>

    <div ng-init="ordersUrl = '{!! $ordersUrl !!}'"/>
    <div ng-init="autoshipUrl = '{!! $autoshipUrl !!}'"/>
    <div ng-init="businessAddress = {{ session()->get('store_owner.businessAddress') }}"/>
    <div ng-init="displayName = '{{$_settings->storeSettings['display_name']}}'"/>
    <div ng-cloak ng-controller="OrderCreateController" class="place-order-page">
        <div class="messages">
            <div class="success-message" ng-if="success">
                <span>@{{ successMessage }}</span>
            </div>
            <div class="warning-message" ng-if="error">
                <div ng-repeat="error in errorMessage track by $index">@{{ error }}</div>
            </div>
        </div>
        <div class="checkout-container" ng-if="checkout">
            <div class="title-row">
                <div class="step-one-nav">
                    <p ng-class="{activeTab: step == 1}"><span>1</span> Fill in your information</p>
                </div>
                <div class=step-two-nav>
                    <p ng-class="{activeTab: step == 2}"><span>2</span> Confirm your order & Payment</p>
                </div>
            </div>
            <div class="information-section" ng-show="step == 1">
                <form name="userForm">
                    <div class="your-information">

                        <h2>Information</h2>
                        <div class="input-group">
                            <input type="text" placeholder="First Name" ng-model="user.firstName" required novalidate>
                        </div>

                        <div class="input-group">
                            <input type="text" placeholder="Last Name" ng-model="user.lastName" required novalidate>
                        </div>

                        <div class="input-group">
                            <input type="email" placeholder="Email" ng-model="user.email" required novalidate>
                        </div>
                        <span ng-if="errorMap['business_address.email']" class="errorText">@{{ errorMap['business_address.email'][0] }}</span>
                        <div class="input-group" ng-show="{{($_settings->getGlobal('self_pickup_reseller', 'show') && session()->get('store_owner.settings.self_pickup'))}}">
                            <input name="selfPickup" type="checkbox" ng-model="selfPickup.enabled" ng-change="useSelfPickup()">
                            <label class="checkbox-label">Pick Up from <label ng-if="displayName">@{{displayName}}</label><label ng-if="!displayName">Seller</label></label>
                        </div>
                    </div>
                    <div id="address-shipping">
                        <h2>Shipping Address</h2>
                        <div class="input-group">
                            <input ng-disabled="selfPickup.enabled" type="text" placeholder="Street address, P.O. Box" name="addresses[shipping][address_1]"  ng-model="addresses.shipping.line_1" ng-change="sameAddress && update()" required novalidate>
                        </div>
                        <div class="input-group">
                           <input ng-disabled="selfPickup.enabled" type="text" placeholder="Apartment, suite, building" name="addresses[shipping][address_2]" ng-model="addresses.shipping.line_2" ng-disabled="sameAddress && update()">
                        </div>
                        <div class="input-group">
                            <input ng-disabled="selfPickup.enabled" type="text" placeholder="City" name="addresses[shipping][city]" ng-model="addresses.shipping.city" ng-disabled="sameAddress && update()" required novalidate>
                        </div>
                       <div class="input-group dropdown">
                            <select ng-disabled="selfPickup.enabled" ng-model="addresses.shipping.state" name="state" required>
                                <option placeholder="State" value="" selected>State</option>
                                <option ng-repeat="state in states track by $index">@{{ state.value }}</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <input ng-disabled="selfPickup.enabled" type="text" placeholder="Zip Code" ng-model="addresses.shipping.zip" ng-disabled="sameAddress && update" required novalidate>
                        </div>
                        <div>
                          <div class="input-group">
                              <input ng-disabled="selfPickup.enabled" name="shipping_same_as_billing" type="checkbox" ng-model="sameAddress" ng-change="sameAddress && updateAddress()">
                              <label class="checkbox-label"> Use same address for Billing</label>
                          </div>
                       </div>
                    </div>
                    <div id="address-billing">
                        <h2>Billing Information</h2>

                        <div class="input-group">
                             <input type="text" placeholder="Street address, P.O. Box" name="addresses[billing][address_1]" ng-model="addresses.billing.line_1" ng-change="sameAddress" required novalidate>
                        </div>

                        <div class="input-group">
                            <input type="text" placeholder="Apartment, suite, building" name="addresses[billing][address_2]" ng-model="addresses.billing.line_2" ng-change="sameAddress">
                        </div>

                        <div class="input-group">
                             <input type="text" placeholder="City" name="addresses[billing][city]" ng-model="addresses.billing.city" ng-change="sameAddress" required novalidate>
                        </div>

                        <div class="input-group dropdown">
                                <select ng-model="addresses.billing.state" name="state" required>
                                    <option  placeholder="State" value="" selected>State</option>
                                    <option ng-repeat="state in states track by $index">@{{ state.value }}</option>
                                </select>
                        </div>
                        <div class="input-group">
                           <input type="text" placeholder="Zip Code" name="addresses[billing][zip]" ng-model="addresses.billing.zip" ng-change="sameAddress" required novalidate>
                        </div>
                         <div class="next-btn-wrapper">
                          <button class="next-btn" ng-class="{'enabled': userForm.$valid}" ng-click="nextButton()" ng-disabled="userForm.$invalid">Next</button>
                          </div>
                    </div>
                </form>
            </div>
            <div class="confirm-order-section" ng-if="step == 2">
                <div class="confirm-order">
                    <h2>Confirm Your Order</h2>
                    <div class="confirm-details" ng-if="checkout">
                        @include('_helpers.order_table')
                    </div>
                    <div ng-if="selectedPlan != null">
                        <div>Autoship Plan: @{{ planInterval }}</div>
                        <div>Next Bill Date: @{{ nextBillDate }}</div>
                    </div>
                    @if (session()->has('store_owner') && $_settings->getGlobal('reseller_coupons', 'show') || !session()->has('store_owner') && $_settings->getGlobal('corp_coupons', 'show'))
                    <div class="apply-coupon" ng-if="selectedPlanPid == null">
                        <input type="text" ng-model="coupon.code" placeholder="Do you have a coupon code?"><span class="cp-button-standard" ng-click="couponApply()">Apply Coupon</span>
                        <div ng-show="couponError" class="warning-message coupon-applied" style="width: 95%">
                            <span>@{{ couponError }}</span>
                        </div>
                        <div class="coupon-applied">
                            <span ng-if="checkout.coupon">Coupon code <i>@{{ checkout.coupon.code }}</i> was applied to your order</span>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="payment-method">
                    <h2>Payment Method</h2>
                    <input type="text" ng-model="payment.card.name" placeholder="Name" ng-disabled="checkout.total == 0">
                    <input type="text" ng-model="payment.card.number" placeholder="Credit Card Number" ng-disabled="checkout.total == 0">
                    <input type="text" ng-model="payment.card.code" placeholder="Security Code (Back of Card)" ng-disabled="checkout.total == 0">
                    <span>Expiration</span>
                    <select ng-model="payment.card.month" ng-disabled="checkout.total == 0">
                        <option>Month</option>
                        <option>01</option>
                        <option>02</option>
                        <option>03</option>
                        <option>04</option>
                        <option>05</option>
                        <option>06</option>
                        <option>07</option>
                        <option>08</option>
                        <option>09</option>
                        <option>10</option>
                        <option>11</option>
                        <option>12</option>
                    </select>
                    <select ng-model="payment.card.year" ng-disabled="checkout.total == 0">
                        <option>Year</option>
                        <option ng-repeat="year in years track by $index" value="@{{year}}">@{{year}}</option>
                    </select>

                    <div class="payment-btn">
                        <div class="payment-options">
                            <img src="https://s3-us-west-2.amazonaws.com/controlpad/CreditCard-Icons.png" class="credit-cards">
                        </div>
                        <div class="order-buttons">
                        <span ng-click="setStep(1)" class="return-btn">Back</span>
                          <input class="place-order" type="button" ng-show="!notDisabled" ng-click="createOrder()" value="Place Order">
                          <input class="place-order" type="button" ng-show="notDisabled" value="Processing . . .">
                        </div>
                        <small>By clicking Place Order above, you agree to the following <a href="/terms" class="agree" target="_blank">terms</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        user_id = '';
        method = 'checkout';
    </script>
@endsection
