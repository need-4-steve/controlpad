@extends('layouts.boss')
@section('sub-title')
    All Subscription Plans
@endsection
@section('content')
    <cp-subscription-plan-index></cp-subscription-plan-index>
@endsection
@section('scripts')
<script src="https://unpkg.com/vue@2.4.2"></script>
<!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
