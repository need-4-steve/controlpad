@extends('layouts.boss')
@section('sub-title')
    Sales
@endsection
@section('content')
    <cp-sales-index></cp-sales-index>
@endsection
@section('scripts')
<script src="https://unpkg.com/vue@2.4.2"></script>
<!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
