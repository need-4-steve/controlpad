@extends('layouts.pages-layout')
@section('content')
    @if (isset($error_message))
        <div class="alert alert-danger">
            <p>{{ var_dump($error_message) }}</p>
        </div>
    @endif
    <cp-login></cp-login>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
    <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
