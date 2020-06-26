@extends('layouts.pages-layout')
@section('content')
<div class="border-wrap">
    <div class="text-wrapper">
        <h1>{{ $page->title }}</h1>
        {!! $page->content !!}
    </div>
</div>
@endsection
