@extends('emails.layouts.basic')
@section('body')
    <section style="padding: 20px; width:90%; max-width: 600px; margin: 0 auto;">
        <p>
            Hello {{$buyer->first_name}},
        </p>
        <p>
            I hope this note finds you well. There was an issue with your {{ $settings->autoship_display_name->value }} order that requires your attention. Please log in to your account to update your information.
        </p>
        @if(!empty($failureMessage))
            <p>Reason: {{ $failureMessage }} </p>
        @endif
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
        @include('emails.layouts.customer-details')
        <br/>
        <br/>
        @include('emails.layouts.footer')
    </section>
@stop
