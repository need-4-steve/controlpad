@extends('layouts.boss')
@section('sub-title')
    Settings
@endsection
@section('content')
    <cp-settings-index></cp-settings-index>
@endsection
@section('scripts')
       <script src="https://unpkg.com/vue@2.4.2"></script>
       <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
