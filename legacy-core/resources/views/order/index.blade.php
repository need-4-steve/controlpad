@extends('layouts.boss')
@section('sub-title')
    @if ($admin)
       Orders
    @else
       My Sales
    @endif
@endsection
@section('content')
    <cp-order-index></cp-order-index>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
   <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
