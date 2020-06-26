@extends('layouts.boss')
@section('sub-title')
    New Subscription Plan
@endsection
@section('content')
    <cp-subscription-form :edit="false"></cp-subscription-form>
@endsection
@section('scripts')
      <script src="https://unpkg.com/vue@2.4.2"></script>
      <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
