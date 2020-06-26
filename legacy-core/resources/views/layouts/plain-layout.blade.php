@extends('layouts.master')
@inject('_settings', 'globalSettings')
@section('angular-app')
ng-app="app"
@endsection
@section('header')
    <div id="header-menu"  role="navigation">
        <div class="header-wrapper">
            <div class="navbar-header">
                <a class="navbar-brand" id="logo" href="#">
                    <img src="{{$_settings->getGlobal('back_office_logo', 'value')}}" alt="{{$_settings->getGlobal('company_name', 'value')}}">
                </a>
            </div>
        </div>
    </div>
@endsection
@section('sub-layout')
    <div class="cp-container">
        @yield('content')
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
@endsection
