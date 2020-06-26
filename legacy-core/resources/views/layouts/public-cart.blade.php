@inject('_settings', 'globalSettings')
@extends('layouts.master')

@section('angular-app')
ng-app="app"
@endsection

@section('title')

@endsection

@section('css')
    <link rel="stylesheet" type="text/css" href="{{  asset('css/slick.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{  angularBuild('angular/store.css') }}"/>
@endsection
@section('header')
<header class="header">
    <div class="header-wrapper">
        <a href="@{{ storeReturnUrl }}" ng-if="storeReturnUrl">
            <span class="menu"><i class="lnr lnr-arrow-left"></i></span><span class="menu-title"><small>Back to Shopping</small></span>
        </a>
        @if ($_settings->getGlobal('reseller_logo', 'show') && isset($store->settings['logo']) && $store->settings['logo'] !== "")
            <div class="cp-store-header-logo"><img src="{{$store->settings['logo']}}" alt="{{$_settings->getGlobal('company_name', 'value')}}"></div>
        @else
            <div class="cp-store-header-logo"><img src="{{$_settings->getGlobal('back_office_logo', 'value')}}" alt="{{$_settings->getGlobal('company_name', 'value')}}"></div>
        @endif
    </div>
</header>
@endsection

@section('sub-layout')
@yield('sub-title')
@include('_helpers.errors')
@include('_helpers.message')
@yield('content')
@endsection
