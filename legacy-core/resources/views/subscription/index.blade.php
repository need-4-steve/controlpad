@extends('layouts.boss')
@section('sub-title')
    All User Subscriptions
@endsection
@section('content')
    <cp-subscription-user-index></cp-subscription-user-index>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
