@extends('layouts.boss')

@section('sub-title')
    Create a Live Video
@endsection

@section('content')
    <cp-live-video-create-youtube
        :current-live-video="{{ $currentLiveVideo }}"
        :service-obj="{{ $service }}"
        :auth-id="{{ $id }}"
        :seller-type-id="{{ $sellerTypeId }}"
    ></cp-live-video-create-youtube>
@endsection

@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
