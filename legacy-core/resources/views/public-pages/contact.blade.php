@extends('layouts.store')
@inject('_settings', 'globalSettings')
@section('title')
    Contact Us
@endsection
@section('content')
 <div class="page-wrapper">
        <div class="content-wrapper">
            <section class="contact-info">
                <div class="personal-info">
                    <ul class="company">
                        <li><h3>{{$_settings->getGlobal('company_name', 'value')}}</h3></li>
                        @if($_settings->getGlobal('phone', 'show'))
                            <li><span><i class="lnr lnr-telephone"></i></span><a href="tel: {{$_settings->getGlobal('phone', 'value')}}">{{$_settings->getGlobal('phone', 'value')}}</a></li>
                        @endif
                        <li><span><i class="lnr lnr-envelope"></i></span><a href="mailto:{{$_settings->getGlobal('company_email', 'value')}}">{{$_settings->getGlobal('company_email', 'value')}}</a></li>
                        <li class="address"><span><i class="lnr lnr-"></i></span>
                        <p>{{$_settings->getGlobal('company_name', 'value')}}</p>
                        <p>{{$_settings->getGlobal('address', 'value')}}</p>
                        </li>
                    </ul>
                </div>
                <form action="/send-contact-form" method="post">
                    {{csrf_field()}}
                    <input type="text" name="name" placeholder="name">
                    <input type="email" name="email" placeholder="email">
                    <input type="subject" name="subject_line" placeholder="subject">
                    <textarea name="body" placeholder="how can we help you"></textarea>
                    <button>Submit</button>
                </form>
            </section>
    </div>
</div>
@endsection
