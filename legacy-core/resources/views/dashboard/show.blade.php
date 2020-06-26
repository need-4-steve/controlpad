@extends('layouts.boss')
@section('head')
@endsection
@section('sub-title')
Dashboard
@endsection
@section('content')
<div>
    <cp-dashboard></cp-dashboard>
</div>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
  <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
