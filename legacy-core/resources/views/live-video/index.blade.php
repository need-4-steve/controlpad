@extends('layouts.boss')

@section('sub-title')
    My Live Videos
@endsection
@section('content')
  @if ($currentLiveVideo)
    <cp-live-video-index
        :current-live-video="{{ $currentLiveVideo }}"
        seller-type="{{ $sellerType }}">
    </cp-live-video-index>
  @else
    <cp-live-video-index
        seller-type="{{ $sellerType }}">
    </cp-live-video-index>
  @endif
@endsection

@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
