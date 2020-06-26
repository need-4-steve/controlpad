@extends('emails.layouts.default')
@section('body')
<div class="welcome-wrapper" style="min-width: 400px; margin: 10 auto;  max-width: 800px; border-radius: 2px; text-align: center; font-family: Fira Sans Ultralight, sans-serif, Verdana, sans-serif; font-size: 1.35em;">
    <div class="header" style="min-height: 80px; width: 100%;">
        <img style="height: auto" src="{{ app('globalSettings')->getGlobal('back_office_logo', 'value') }}" alt="{{ config('site.company_name') }}">
    </div>
    <h4 style="font-size: 1.2em; font-weight: 200">Welcome to {{ config('site.company_name') }}</h4>
    <div class="clear" style="margin-top: 25px"></div>
    <div style="width: 100%; border-bottom: 1px solid #333333;"></div>
    <div class="content">
        <p style="font-size: .8em; font-weight: 300; line-height: 1.3em;">Thanks for registering!  To complete your registration please go to <a href="{{'https://' . config('site.domain') . '/join/' . $token->token}}">link.</a></p>
    <div class="clear" style="margin-top: 25px"></div>
    <div class="footer">
        <p style="font-size: .8em";><?php echo date("F j, Y"); ?></p>
    </div>
</div>
@stop
