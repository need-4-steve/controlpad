@extends('layouts.boss')
@inject('_settings', 'globalSettings')
@section('angular-controller')
ng-controller="UserController"
@endsection
@section('sub-title')
    All Users
@endsection
@section('content')
    <cp-user-index></cp-user-index>
@stop
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
