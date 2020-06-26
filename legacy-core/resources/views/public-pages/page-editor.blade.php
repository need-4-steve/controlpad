@extends('layouts.boss')
@section('sub-title')
    Editor: {{ $page->title or '' }}
@endsection
@section('content')
    @php
        $page->content = str_replace('&quot;', ' ', $page->content);
    @endphp
    <cp-markdown-editor :page="{{ $page or null }}"></cp-markdown-editor>
@endsection
@section('scripts')
    <script src="https://unpkg.com/vue@2.4.2"></script>
     <!-- <script src="{{ elixir('js/app.js') }}"></script> -->
@endsection
