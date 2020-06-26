@extends('layouts.'.$layout)
@section('sub-title')
    Checkout
@endsection
@section('content')
    <cp-checkout :has-store-owner="{{ json_encode(session()->has('store_owner')) }}"></cp-checkout>
@endsection
@section('scripts')
<script src="https://unpkg.com/vue@2.4.2"></script>
<!-- <script src="{{ elixir('/js/app.js') }}"></script> -->
@endsection
