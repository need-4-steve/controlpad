@extends('layouts.boss')
@inject('_settings', 'globalSettings')

@section('sub-title')
All Categories
@endsection
@section('content')
<cp-categories></cp-categories>
@endsection
@section('scripts')
  <script src="https://unpkg.com/vue@2.4.2"></script>
  <!-- <script src="{{ elixir('/js/app.js') }}"></script> -->
@endsection
