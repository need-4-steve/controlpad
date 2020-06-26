@extends('layouts.home')
@inject('_settings', 'globalSettings')
@section('title')
    {{$_settings->getGlobal('company_name', 'value')}}
@endsection
@section('angular-controller')
ng-controller="StoreController"
@endsection
@section('content')
 <header class="home-header">
    <div class="logo-wrapper">
        <img src="{{$_settings->getGlobal('back_office_logo', 'value')}}" class="logo">
    </div>
     <ul class="demo-categories">
         <li ng-repeat="category in catHierarchy">
            <a href="/store?category=@{{category.id}}" ng-bind="category.name"></a>
        </li>
     </ul>
     <div class="menu-links">
        @if ($_settings->getGlobal('google_store_url', 'show'))
            <a href="{{$_settings->getGlobal('google_store_url', 'value')}}">
                <img src="https://s3-us-west-2.amazonaws.com/controlpad/banners/google-play.png">
            </a>
        @endif
        @if ($_settings->getGlobal('ios_store_url', 'show'))
            <a href="{{$_settings->getGlobal('ios_store_url','value')}}">
                <img src="https://s3-us-west-2.amazonaws.com/controlpad/banners/appstore.png">
            </a>
        @endif
        <a href="/login" class="login">SIGN IN</a>
     </div>
 </header>
    <div class="home-wrapper">
        <div class="banner">
            <div class="overlay-text top">
                <div class="slogan-wrapper">
                    <h1>BLACK & WHITE</h1>
                    <p>The World's first E-Commerce Platform</br> for Running a Social Media Business.</p>
                    <div class="actions">
                        <a href="/store">SHOP</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
@endsection
