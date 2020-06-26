@extends('layouts.boss')
@section('sub-title')
    Events
@endsection
@section('content')
    <cp-events-index></cp-events-index>
@endsection
@section('scripts')
<script src="https://unpkg.com/vue@2.4.2"></script>
<!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
