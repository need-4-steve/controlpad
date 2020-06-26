@extends('layouts.boss')
@section('sub-title')
    My Orders
@endsection
@section('content')
   <cp-rep-order-index></cp-rep-order-index>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
