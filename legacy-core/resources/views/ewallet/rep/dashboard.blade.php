@extends('layouts.boss')
@section('sub-title')
    My eWallet
@endsection
@section('content')
    <cp-ewallet-dashboard></cp-ewallet-dashboard>
@endsection
@section('scripts')
<script src="https://unpkg.com/vue@2.4.2"></script>
<!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
