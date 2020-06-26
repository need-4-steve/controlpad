@extends('layouts.boss')
@section('style')
@endsection
@section('sub-title')
    Email Edit
@endsection
@section('content')
    <cp-email-edit :email="{{$email}}"></cp-email-edit>
@endsection
@section('scripts')
       <script src="https://unpkg.com/vue@2.4.2"></script>
       <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
