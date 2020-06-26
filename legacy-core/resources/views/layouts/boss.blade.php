@extends('layouts.master')
@inject('_settings', 'globalSettings')
@section('angular-app')
ng-app="app"
@endsection
@section('title')

@endsection
@section('header')
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{config('site.google_tracking_id')}}"></script>
        <script>
          window.dataLayer = window.dataLayer || []
          function gtag () { dataLayer.push(arguments) }
          gtag('js', new Date())
          gtag('config', "{{config('site.google_tracking_id')}}")
        </script>
        @include('layouts.partials.header')
@endsection
@section('sub-layout')
<div class="sub-header">
    <div class="sub-wrapper">
        <h1>@yield('sub-title')</h1>
        <div class="sub-option">
            @yield('sub-option')
        </div>
    </div>
</div>
<span class="openSideMenu" ng-click="sideBarOpen = !sideBarOpen"><i class="lnr lnr-menu"></i></span>
<div id="main">
    <div id="navbar" ng-class="{sideBarOpen: sideBarOpen}">
        <cp-nav-menu></cp-nav-menu>
    </div>
    <!--
    The old way of doing things!
    <div id="sidebar" role="navigation" ng-class="{sideBarOpen: sideBarOpen}">
        <div class="list-group-not" id="main-menu-not">
            @yield('boss-menu')
            <cp-nav-menu></cp-nav-menu>
        </div>
    </div>
    -->
    <div id="content">
        <div id="mainContent">
            @include('_helpers.errors')
            @include('_helpers.message')
            @yield('content')
        </div>
    </div>
</div>
@endsection
@section('footer')
    <footer class="home-footer">
        <ul>
            @if ($_settings->getGlobal('about_us', 'show'))
            <li><a href="{{$_settings->getGlobal('about_us', 'value')}}" target="_blank">About Us</a></li>
            @endif
            @if ($_settings->getGlobal('return_policy', 'show'))
            <li><a href="{{$_settings->getGlobal('return_policy', 'value')}}">Return Policy</a></li>
            @endif
            @if ($_settings->getGlobal('terms', 'show'))
            <li><a href="{{$_settings->getGlobal('terms', 'value')}}">Terms &amp; Conditions</a></li>
            @endif
        </ul>
        <div class="address">
            <ul>
                <li><span>&copy;{{ date('Y') }} - {{$_settings->getGlobal('company_name', 'value')}}</span></li>
                @if ($_settings->getGlobal('address', 'show'))
                <li>{{$_settings->getGlobal('address', 'value')}}</li>
                @endif
                @if ($_settings->getGlobal('phone', 'show'))
                <li>{{$_settings->getGlobal('phone', 'value')}}</li>
                @endif
            </ul>
        </div>
    </footer>
    @if ($_settings->getGlobal('olark_chat_integration', 'show') && auth()->user()->hasRole(['Rep']))
        {!!$_settings->getGlobal('olark_chat_integration', 'value')!!}
    @endif
    @if ($_settings->getGlobal('tawk_chat_integration', 'show') && auth()->user()->hasRole(['Rep']))
        {!!$_settings->getGlobal('tawk_chat_integration', 'value')!!}
    @endif
@endsection
