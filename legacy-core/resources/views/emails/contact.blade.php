@extends('emails.layouts.basic')
@section('body')
<p>
    <strong>Name:</strong>{{$data['name']}}<br>
    <strong>Email:</strong>{{$data['email']}}<br>
    <strong>Subject:</strong>{{$data['subject_line']}}
</p>
<p>
    <strong>Message:</strong>
    <br>
    {{$data['body']}}
</p>
<p>
    <strong>Date:</strong>{{date("F j, Y, g:i a")}}
    <strong>User IP address:</strong><{{Request::getClientIp()}}
</p>

@stop
@section('unsubscribe')
@stop
