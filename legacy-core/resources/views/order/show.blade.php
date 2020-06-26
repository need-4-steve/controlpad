@extends('layouts.boss')
@section('sub-title')
        @if ($type == "invoice")
            Invoice ID: {{ $order->uid }} &nbsp;
        @else
            Receipt ID: {{ $order->receipt_id }} &nbsp;
        @endif
        <img src="/barcode/img/{{$order->receipt_id}}" alt="barcode"/>
@endsection
@section('content')
    <cp-order
      receipt_id="{{ $order->receipt_id }}"
      :hide-hold="{{ json_encode($hideHold) }}"
      type-of-order="{{ $type }}"></cp-order>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
   <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
