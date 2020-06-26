@extends('layouts.boss')

@section('sub-title')
    Create a Facebook Live Video
@endsection

@section('content')
    <cp-live-video-create
        :oauth-user="{{ $oauth }}"
        :service-obj="{{ $service }}"
        :auth-id="{{ $id }}"
        :seller-type-id="{{ $sellerTypeId }}"
    ></cp-live-video-create>
@endsection

@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
  <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
