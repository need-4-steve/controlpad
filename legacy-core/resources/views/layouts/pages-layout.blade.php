@extends('layouts.master')
@inject('_settings', 'globalSettings')
@section('angular-app')
ng-app="app"
@endsection
@section('css')
    <link rel="stylesheet" type="text/css" href="{{  angularBuild('angular/store.css') }}"/>
@endsection
@section('header')
    @include('layouts.partials.publicheader')
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
