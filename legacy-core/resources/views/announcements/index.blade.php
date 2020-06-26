@inject('_settings', 'globalSettings')
@extends('layouts.boss')
@section('sub-title')
    All {{ $_settings->getGlobal('title_announcement', 'value') }}
@endsection
@section('content')
    <cp-announcement-index></cp-announcement-index>
@endsection
@section('scripts')
            <script src="https://unpkg.com/vue@2.4.2"></script>
            <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
