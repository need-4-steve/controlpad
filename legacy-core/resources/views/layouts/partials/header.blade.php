@inject('_settings', 'globalSettings')
@php
    $isOwnerRep = $_settings->isOwnerRep();
    $isOwnerAdmin = $_settings->isOwnerAdmin();
@endphp
<header>
<div id="header-menu"  role="navigation">
    <div class="header-wrapper">
        <div class="navbar-header">
            <a class="navbar-brand" id="logo" href="/dashboard">
                <img src="{{$_settings->getGlobal('back_office_logo', 'value')}}" alt="{{$_settings->getGlobal('company_name', 'value')}}">
            </a>
        </div>
        <ul id="top-right-menu">
            @yield('header-links')
            @if($isOwnerRep)
                <li>
                </li>
                <li>
                    <div>
                </li>
            @endif
            <li class="menu-item menu-item-type-custom menu-item-object-custom">
                @if (!$isOwnerAdmin)
                <a href="/store"><span class="header-text">Buy</span> <span><i class="lnr lnr-store"></i></span></a>
                @endif
            </li>
            @if (Auth::check())
            <li class="dropdown" ng-click="menu = !menu">
                <a href="#" class="header-login">
                 {{ Auth::user()->first_name }} <span class="label label-default">{{ Auth::user()->role->name }}</span>
                    <i class="lnr lnr-user"></i>
                </a>
                <ul class="dropdown-menu" ng-class="{menu: menu}" ng-mouseleave="menu = false">
                    <li><a href="/dashboard">Dashboard</a></li>
                     @if($isOwnerRep)
                        <li><a href="/my-settings">My Account</a></li>
                        @if($_settings->getGlobal('replicated_site', 'show'))
                          <li><a href="{{ sprintf(env('REP_URL').'/store ', auth()->user()->public_id) }}">My Website</a></li>
                        @endif
                    @endif
                    @if($isOwnerAdmin)
                       <li><a href="/my-settings">My Account</a></li>
                   @endif
                    <li><a href="/logout">Sign Out</a></li>
                </ul>
            </li>
            @else
                <li><a href="{{ env('APP_PROTOCOL') }}{{ config('app.url') }}/login"><i class="lnr lnr-user"></i></a></li>
            @endif
            </ul>
                <div class="mobile-menu" ng-click="isOpen = true"><span><i class="lnr lnr-user"></i></span></div>
                <div class="mobile-nav" ng-class="{open: isOpen}">
                    <div class="mb-nav"><img src="{{$_settings->getGlobal('back_office_logo', 'value')}}" alt="{{$_settings->getGlobal('company_name', 'value')}}"><span class="close" ng-click="isOpen = false"><i class="lnr lnr-cross"></i></span></div>
                    <ul>
                        @yield('header-links')
                            <li class="menu-item menu-item-type-custom menu-item-object-custom">
                                <a href="/store">Buy</a>
                            </li>
                        @if (Auth::check())
                            <li><a href="/dashboard">Dashboard</a></li>
                            <li><a href="/my-settings">My Account</a></li>
                            @if ($isOwnerRep)
                                <li><a href="{{ sprintf(env('REP_URL'), auth()->user()->public_id) }}">My Website</a></li>
                            @endif
                            <li><a href="/logout">Sign Out</a></li>
                        @else
                            <li><a href="/login">Sign in</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
</header>
