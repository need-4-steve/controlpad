@extends('layouts.boss')
@section('sub-title')
    Unpaid Invoices
@endsection
@section('content')
    <cp-invoice-index></cp-invoice-index>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
   <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
