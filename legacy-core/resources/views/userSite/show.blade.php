@extends('layouts.store')
@section('content')
<div class="page-wrapper">
    <div class="content-wrapper">
        @if(session()->has('store_owner'))
        <h2>Contact Us</h2>
        @else
        <h2>Contact Me</h2>
        @endif
        <section class="contact-info">
            <div class="avatar-img">
                <img src="">
            </div>
            <div class="personal-info">
                <ul>
                    <li><p>{{ $user->first_name }} {{ $user->last_name }}</p></li>
                    @if ($userSite->display_phone == 1)
                        <li><span><i class="lnr lnr-telephone"></i></span><a href="{{ $user->formatted_phone }}">{{ $user->formatted_phone }}</a></li>
                    @endif
                    <li><span><i class="lnr lnr-envelope"></i></span><a href="mailto: {{ $user->email }}">{{ $user->email }}</a></li>
                    <li class="address"><span><i class="lnr lnr-"></i></span>
                    <p>XYZ Company</p>
                    <p>123 Street #23</p>
                    <p>Provo, UT 84601</p>
                    </li>
                </ul>
            </div>
            <form action='/send-contact-form' method="POST">
                {{ csrf_field() }}
                <input type="text" placeholder="name">
                <input type="email" placeholder="email">
                <input type="subject" placeholder="subject">
                <textarea placeholder="how can I help you"></textarea>
                <button>Submit</button>
            </form>
        </section>
        <section class="bio">
            <h3>{{ $user->first_name }} {{ $user->last_name }}</h3>
            <p>{{ $user->Bio }}</p>
        </section>
    </div>
</div>
@endsection
