@extends('layouts.plain-layout')
@section('css')
    <link rel="stylesheet" type="text/css" href="{{  asset('css/slick.css') }}"/>
    <link rel="stylesheet" type="text/css" href="{{  elixir('css/store.css') }}"/>
@endsection
@section('content')
<cp-rep-store-locator></cp-rep-store-locator>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
