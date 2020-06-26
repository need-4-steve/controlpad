@extends('layouts.store')
@section('title')
    Contact Us
@endsection
@section('content')
    <div class="page-wrapper">
        <div class="content-wrapper">
            <section class="contact-info">
                <div class="personal-info">
                    <ul>
                        <li><p>{{ $user->first_name }} {{ $user->last_name }}</p></li>
                        @if($user->settings->show_phone > 0)
                        <li><span><i class="lnr lnr-telephone"></i></span><a href="tel: {{ formatPhone($user->phone_number) }}">{{ formatPhone($user->phone_number) }}</a></li>
                        @endif
                        @if($user->settings->show_email > 0)
                        <li><span><i class="lnr lnr-envelope"></i></span><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></li>
                        @endif
                    </ul>
                </div>
                <form action="/send-contact-form" method="post">
                   {{csrf_field()}}
                    <input type="text" placeholder="name" name="name">
                    <input type="email" placeholder="email" name="email">
                    <input type="subject" placeholder="subject" name="subject_line">
                    <textarea placeholder="how can I help you" name="body"></textarea>
                    <button>Submit</button>
                </form>
            </section>
        </div>
    </div>
@endsection
