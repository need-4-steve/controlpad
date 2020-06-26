@extends('layouts.boss')

@section('sub-title')
    Past {{ $video->driver->name }} Video
@endsection

@section('content')
    <cp-live-video-show :video-obj="{{ $video }}"></cp-live-video-show>
@endsection

@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
