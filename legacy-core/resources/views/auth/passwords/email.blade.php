@extends('layouts.pages-layout')

<!-- Main Content -->
@section('content')
  <cp-send-password-reset></cp-send-password-reset>
@endsection
@section('scripts')
       <script src="https://unpkg.com/vue@2.4.2"></script>
       <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
