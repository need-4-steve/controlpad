@inject('_settings', 'globalSettings')
<div ng-controller="StoreHeaderController">
<header class="header">
    <div class="header-wrapper">
        <span class="menu" ng-click="mainMenu = !mainMenu; cartMenu = false;"><i class="lnr lnr-menu"></i></span><span class="menu-title"><small>Menu</small></span>
        @if ($_settings->getGlobal('reseller_logo', 'show') && isset($store->settings['logo']) && $store->settings['logo'] !== "")
            <a href="/store" class="cp-store-header-logo"><img src="{{$store->settings['logo']}}" alt="{{$_settings->getGlobal('company_name', 'value')}}"></a>
        @else
            <a href="/store" class="cp-store-header-logo"><img src="{{$_settings->getGlobal('back_office_logo', 'value')}}" alt="{{$_settings->getGlobal('company_name', 'value')}}"></a>
        @endif
        <ul class="header-options">
            @if (isset($store->rep) && $_settings->getGlobal('store_join_link', 'show'))
                <li class="hide-header-link">
                    <a href="//{{ config('app.url') }}/sign-up-with/{{ $store->rep->public_id }}" class="cp-button-link">{{ strtoupper($_settings->getGlobal('store_join_link', 'value')) }}</a>
                </li>
            @elseif ($_settings->getGlobal('store_join_link', 'show'))
                <li class="hide-header-link">
                    <a href="//{{ config('app.url') }}/join" class="cp-button-link">{{ strtoupper($_settings->getGlobal('store_join_link', 'value')) }}</a>
                </li>
            @endif
            {{-- TODO: add event conditional based on settings, and events name --}}
            @if (!$_settings->getGlobal('events_as_replicated_site', 'show') && $_settings->getGlobal('allow_reps_events', 'show')
                && (!isset($store->rep) || !$store->rep->settings->hide_products))
                <li class="hide-header-link">
                    <a href="/store/events" class="cp-button-link">{{ $_settings->getGlobal('events_title', 'value')['plural'] }}</a>
                </li>
            @endif
            <li class="my-cart" ng-click="cartMenu = !cartMenu; mainMenu = false; sideCart();" ng-hide="cartLink">
               <span><i class="lnr lnr-cart"></i></span>
            </li>
            <li class="my-cart" ng-show="cartLink">
                <a href="/cart">
                <span><i class="lnr lnr-cart"></i></span>
               </a>
            </li>
            <li class="dropdown" ng-click="menu = !menu">
                <a href="#" class="header-login">
                    <span><i class="lnr lnr-user"></i></span>
                </a>
                <ul class="store-dropdown-menu" ng-class="{menu: menu}" ng-mouseleave="menu = false">
                 @if (auth()->check())
                    <li><a href="//{{ config('app.url') }}/dashboard">Dashboard</a></li>
                    @if (Auth::user()->hasRole(['Rep']))
                        <li><a href="//{{ config('app.url') }}/my-settings">My Account</a></li>
                    @endif
                    <li><a href="//{{ config('app.url') }}/logout">Sign Out</a></li>
                </ul>
            </li>
             @else
                <li><a href="//{{ config('app.url') }}/login">Sign in</a></li>
            @endif
        </ul>
    </div>
    <div class="main-menu-wrapper" ng-class="{mainMenu: mainMenu}" ng-click="mainMenu = false">
        <div class="main-menu" ng-click="preventDefault($event)">
            @if(isset($store->rep))
                <div class="rep-info">
                    <p class="sm-text">
                        @if(!empty($store->rep->profileImage->first()))
                            <img src="{{ $store->rep->profileImage[0]->url_sm }}" class="profile-image">
                        @endif
                        <h1 class="no-top">{{ $store->rep->full_name }}</h1>
                        @if($store->rep->settings->show_phone > 0)
                        <p><a href="tel:{{ formatPhone($store->rep->phone_number) }}">{{ formatPhone($store->rep->phone_number) }}</a></p>
                        @endif
                    </p>
                </div>
            @endif
            <ul class="product-menu">
                @if(!empty($store->categories) && $store->categories->isNotEmpty())
                <li ng-click="subOpen = !subOpen;">Categories
                    <span class="menu-icon">
                        <i ng-class="['lnr', {'lnr-plus': !subOpen, 'lnr-minus': subOpen}]"></i>
                    </span>
                    <ul class="sub-menu" ng-class="{subOpen: subOpen}">
                        @if (isset($store))
                            @foreach ($store->categories as $category)
                                <li ng-click="mainMenu = !mainMenu" class="category-line">
                                    <a href="/store?category={{$category->id}}" class="main-category">
                                        <span>{{ $category->name }}</span><br/>
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </li>
                @else
                <li><a href="/" ng-click="mainMenu = !mainMenu">Shop</a></li>
                @endif
                @if($_settings->getGlobal('affiliate_link', 'value')['display_on_rep_site'])
                <li><a href="{{ $_settings->getGlobal('affiliate_link', 'value')['url'] . '?public_id=' . $store->rep->public_id }}" ng-click="mainMenu = !mainMenu">{{ $_settings->getGlobal('affiliate_link', 'value')['display_name'] }}</a></li>
                @endif
                <li><a href="/cart" ng-click="mainMenu = !mainMenu">Cart</a></li>
                {{-- TODO: change events name by configurable settings --}}
                @if (!$_settings->getGlobal('events_as_replicated_site', 'show') && $_settings->getGlobal('allow_reps_events', 'show')
                    && (!isset($store->rep) || !$store->rep->settings->hide_products))
                    <li><a href="/store/events" ng-click="mainMenu = !mainMenu">{{ $_settings->getGlobal('events_title', 'value')['plural'] }}</a></li>
                @endif
                 @if (Auth::check())
                    <li><a href="//{{ config('app.url') }}/dashboard">Dashboard</a></li>
                    @if (Auth::user()->hasRole(['Rep']))
                        <li><a href="//{{ config('app.url') }}/my-settings">My Account</a></li>
                    @endif
                    <li><a href="//{{ config('app.url') }}/logout">Sign Out</a></li>
                @else
                    <li><a href="//{{ config('app.url') }}/login" ng-click="mainMenu = !mainMenu">Sign in</a></li>
                @endif
                @if(isset($store->rep))
                <li><a href="/contact" ng-click="mainMenu = !mainMenu">Contact Me</a></li>
                @else
                <li><a href="/contact" ng-click="mainMenu = !mainMenu">Contact Us</a></li>
                @endif
                @if (isset($store->rep) && $_settings->getGlobal('store_join_link', 'show'))
                    <li>
                        <a href="//{{ config('app.url') }}/sign-up-with/{{ $store->rep->public_id }}">{{$_settings->getGlobal('store_join_link', 'value')}}</a>
                    </li>
                @elseif ($_settings->getGlobal('store_join_link', 'show'))
                    <li>
                        <a href="//{{ config('app.url') }}/join">{{$_settings->getGlobal('store_join_link', 'value')}}</a>
                    </li>
                @endif
                @if (isset($store) && isset($store->customLinks))
                    @foreach ($store->customLinks as $customLink)
                        <li>
                            @if (!$customLink->open_in_new_tab)
                                <a href="{{$customLink->url}}">{{$customLink->name}}</a>
                            @else
                                <a href="{{$customLink->url}}" target="_blank">{{$customLink->name}}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            </ul>
            <ul class="account-menu">
                <li><a href="{{$_settings->getGlobal('return_policy', 'value')}}" ng-click="mainMenu = !mainMenu">Return Policy</a></li>
                <li><a href="{{$_settings->getGlobal('terms', 'value')}}" ng-click="mainMenu = !mainMenu">Terms &amp; Conditions</a></li>
            </ul>
        </div>
    </div>
    <div class="cart-menu-wrapper" ng-class="{cartMenu: cartMenu}" ng-click="cartMenu = false">
        <div class="cart-menu" ng-click="preventDefault($event)">
            <div class="cart-wrapper">
                    <p ng-if="emptyCart" class="empty-cart">
                        @if(isset($user_id) && $user_id == Auth::user()->id)
                            (Your cart is empty)
                        @else
                            (Cart is empty)
                        @endif
                    </p>
                    <ul class="cart-items">
                        <li ng-repeat="line in cart.lines">
                            <div class="img">
                                <a href="">
                                    <img ng-src="@{{ line.items[0].img_url }}">
                                </a>
                            </div>
                            <div class="prod-text">
                                <a href="">
                                    <p ng-bind="line.items[0].product_name"></p>
                                    <p class="size" ng-bind="line.items[0].option"></p>
                                </a>
                                <div class="quantity">
                                    <button class="lnr lnr-minus" ng-click="quantityChange('minus', line.quantity, line.item_id); checkInv(line)" ng-if="line.quantity > 1"></button>
                                    <input type="number" ng-change="updateCartline(line.item_id, line.quantity)" ng-model="line.quantity">
                                    <button class="lnr lnr-plus" ng-click="quantityChange('plus', line.quantity, line.item_id); checkInv(line)"></button>
                                    <span ng-if="!line.discount" class="price" ng-bind="line.price * line.quantity | currency" ng-model="line.id"></span>
                                    <span ng-if="line.discount"class="price" ng-bind="(line.price - line.discount) * line.quantity | currency" ng-model="line.id"></span>

                                </div>
                            </div>
                            <button class="remove" ng-click="deleteCartLine(line.item_id)">Remove item</button>
                        </li>
                    </ul>
                    <div class="cart-subtotal">
                        <p>Subtotal <span class="fl-right" ng-bind="subtotal | currency"></span></p>
                        <p class="sm-text">Shipping and taxes calculated at checkout</p>
                        <a href="/cart" class="check-out">View Cart</a>
                    </div>
            </div>
        </div>
    </div>
</header>
</div>
