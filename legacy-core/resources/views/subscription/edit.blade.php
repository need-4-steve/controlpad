@extends('layouts.boss')
@section('sub-title')
    Edit {{$subscription->title}} Plan
@endsection
@section('content')
    <cp-subscription-form :subscription="{{json_encode($subscription)}}" :edit="true" ></cp-subscription-form>
@endsection
@section('scripts')
        <script src="https://unpkg.com/vue@2.4.2"></script>
        <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
