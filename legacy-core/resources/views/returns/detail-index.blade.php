@extends('layouts.boss')
@section('sub-title')
    My Returns
@endsection
@section('content')
    <cp-return-detail :returned="{{$returned}}"></cp-return-detail>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
  <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
