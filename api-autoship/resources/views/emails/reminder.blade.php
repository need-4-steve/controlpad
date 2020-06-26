@extends('emails.layouts.basic')
@section('body')
    <section style="padding: 20px; width:90%; max-width: 600px; margin: 0 auto;">
        <p>
            Hello {{$buyer->first_name}},
        </p>
        <p>
            This is a friendly reminder to let you know that your {{ $settings->autoship_display_name->value }} order will be placed within 3 days. We thank you for your support.
        </p>
        <p>
            If you have any questions please contact our customer support. 
        </p>
        <p>
            Thank you,
        </p>
        <p style="text-align:center;">
            {{ $settings->company_name->value }}
        </p>
        <br/>
        @include('emails.layouts.order-details')
        <br/>
        <br/>
        @include('emails.layouts.customer-details')
        <br/>
        <br/>
        @include('emails.layouts.footer')
    </section>
@stop
