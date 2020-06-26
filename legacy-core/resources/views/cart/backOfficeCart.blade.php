@extends('layouts.boss')
@section('sub-title')
Cart
@endsection
@section('content')
    <cp-back-office-cart></cp-back-office-cart>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
  <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
