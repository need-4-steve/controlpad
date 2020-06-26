@extends('emails.layouts.basic')
@section('body')
    <section style="padding: 20px; width:90%; max-width: 600px; margin: 0 auto;">
        {!! $body !!}
    </section>
@stop
@section('footer')
    @parent
@stop
