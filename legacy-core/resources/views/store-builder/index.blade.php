@extends('layouts.boss')
@section('sub-title')
    <h1>Store Builder</h1>
@endsection
@section('content')
    <cp-store-builder></cp-store-builder>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
  <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
