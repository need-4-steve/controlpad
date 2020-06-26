@extends('layouts.boss')
@section('style')
@endsection
@section('sub-title')
    All History
@endsection
@section('content')
<cp-history-index></cp-history-index>
@endsection
@section('scripts')
        <script src="https://unpkg.com/vue@2.4.2"></script>
        <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
