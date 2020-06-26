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
            @if (Auth::check())
            <li class="dropdown" ng-click="menu = !menu">
                <a href="#" class="header-login">
                 {{ Auth::user()->first_name }} <span class="label label-default">{{ Auth::user()->role->name }}</span>
                    <i class="lnr lnr-user"></i>
                </a>
                <ul class="dropdown-menu" ng-class="{menu: menu}" ng-mouseleave="menu = false">
                    <li><a href="//{{ Config('app.url') }}/dashboard">Dashboard</a></li>
                    <li class="menu-item menu-item-type-custom menu-item-object-custom">
                <a href="/store">My Store <span><i class="lnr lnr-store"></i></span></a>
            </li>
                @if (Auth::user()->hasRole(['Rep']))
                    <li><a href="//{{ Config('app.url') }}/my-settings">My Account</a></li>
                    <li><a href="/user/{{ Auth::user()->id }}">My Website</a></li>
                    <li><a href="//{{ Auth::user()->public_id }}.{{ Config('app.url') }}">My Store</a></li>
                @endif
                <li><a href="//{{ Config('app.url') }}/logout">Sign Out</a></li>
            </ul>
            </li>
         @else
        @endif
        </ul>
        @if (Auth::check())
            <div class="mobile-menu" ng-click="isOpen = true"><span><i class="lnr lnr-menu"></i></span></div>
        @endif
            <div class="mobile-nav" ng-class="{open: isOpen}">
                <div class="mb-nav"><img src="{{$_settings->getGlobal('back_office_logo', 'value')}}" alt="{{$_settings->getGlobal('company_name', 'value')}}"><span class="close" ng-click="isOpen = false"><i class="lnr lnr-cross"></i></span></div>
                <ul>
                    @yield('header-links')
                    @if (Auth::check())
                        <li><a href="//{{ Config('app.url') }}/dashboard">Dashboard</a></li>
                    <li class="menu-item menu-item-type-custom menu-item-object-custom">
                        <a href="//{{ Config('app.url') }}/store">My Website</a>
                    </li>
                    @if (Auth::user()->hasRole(['Rep']))
                        <li><a href="//{{ Config('app.url') }}/my-settings">My Account</a></li>
                        <li><a href="/user/{{ Auth::user()->id }}">My Site</a></li>
                        <li><a href="{{ Auth::user()->public_id }}.{{ Config('app.url') }}">My Website</a></li>
                    @endif
                        <li><a href="//{{ Config('app.url') }}/logout">Sign Out</a></li>
                    @endif
                </ul>
            </div>
    </div>
</div>
</header>
<div class="header-line"></div>
