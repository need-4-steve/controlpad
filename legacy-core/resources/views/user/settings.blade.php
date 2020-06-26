@extends('layouts.boss')
@section('sub-title')
    My Account
@endsection
@section('content')
    <cp-my-settings></cp-my-settings>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
  <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
