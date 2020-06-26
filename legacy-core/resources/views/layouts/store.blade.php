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
    @include('layouts.partials.storeheader')
    <!-- <section class="rep-name-title">
        @if (session()->has('store_owner'))
            <div class="slogan">
                @if (isset($store->affiliateDisplayName))
                    <a href="/store"><h1>{{ $store->affiliateDisplayName }}</h1></a>
                @elseif (isset($store->settings['display_name']))
                    <a href="/store"><h1>{{ $store->settings['display_name'] }}</h1></a>
                @endif
                @if (isset($store->settings))
                    <p>{{ $store->settings['store_slogan'] or ''}}</p>
                @endif
            </div>
        @endif
    </section>
    @if (isset($store) && cache('is_live_streaming.'.session('store_owner.public_id'), null))
    <section class="live-streaming-announcement">
        <span class="col">{{ $store_settings['display_name'] }} is live streaming!</span>
        <span class="col right">
            @if(!$store->showStoreBanner())<a class="cp-button-link attention" href="/store">WATCH NOW</a>@endif
        </span>
    </section>
    @endif -->
@endsection

@section('sub-layout')
    @yield('sub-title')
    @include('_helpers.errors')
    @include('_helpers.message')
    @yield('content')
@endsection

@section('footer')
    @include('layouts.partials.storefooter')
@endsection
