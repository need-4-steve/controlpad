@extends('layouts.boss')
@section('sub-title')
    Create Shipping Label
@endsection
@section('content')
    <cp-shippo
        user_email="{{$email}}"
        user_phone="{{$phone}}"
    ></cp-shippo>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
