@extends('layouts.boss')
@section('sub-title')
    New User
@endsection
@section('content')
    <cp-user-create-form></cp-user-create-form>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
     <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
