@extends('layouts.boss')
@section('sub-title')
	Media Library
@endsection
@section('content')
    <cp-media-index></cp-media-index>
@endsection
@section('scripts')
	<script src="https://unpkg.com/vue@2.4.2"></script>
	<!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
