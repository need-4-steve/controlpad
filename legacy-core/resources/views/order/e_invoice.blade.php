@extends('layouts.pages-layout')
@inject('_settings', 'globalSettings')
@section('content')
    <cp-e-invoice></cp-e-invoice>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
