@extends('layouts.boss')
@section('sub-title')
    All Products
@endsection
@section('content')
    <cp-product-index></cp-product-index>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
