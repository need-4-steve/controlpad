@extends('layouts.boss')
@section('sub-title')
    @if (Auth::user()->id == $user->id)
        Privacy &amp; Communication Preferences
    @else
        Edit {{ $user->first_name }} {{ $user->last_name }}
    @endif
@endsection
@section('content')
<div class="edit">
    <div class="row">
        {!! Form::open(array('url' => 'users/updateprivacy/'.$user->id, 'method' => 'POST')) !!}
            <div class="row">
                <div class="col col-xl-4 col-lg-6 col-md-8 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2 class="panel-title"><i class="lnr lnr-lock"></i> Privacy Settings</h2>
                        </div>
                        <div class="panel-body">
                            <p>What information would you like to to share with {{ Cache::get('settings.rep_title') }}s who have you in their {{ Cache::get('settings.downline_title') }}?*</p>
                            <label>{!! Form::checkbox('hide_gender', null, $checked['hide_gender']) !!} Gender</label><br>
                            <label>{!! Form::checkbox('hide_dob', null, $checked['hide_dob']) !!} Date of Birth</label><br>
                            <label>{!! Form::checkbox('hide_email', null, $checked['hide_email']) !!} Email</label><br>
                            <label>{!! Form::checkbox('hide_phone', null, $checked['hide_phone']) !!} Phone</label><br>
                            <label>{!! Form::checkbox('hide_billing_address', null, $checked['hide_billing_address']) !!} Billing Address</label><br>
                            <label>{!! Form::checkbox('hide_shipping_address', null, $checked['hide_shipping_address']) !!} Shipping Address</label><br>
                        </div>
                    </div>
                </div>
                <div class="col col-xl-4 col-lg-6 col-md-8 col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h2 class="panel-title"><i class="lnr lnr-smartphone"></i> Communication Preferences</h2>
                        </div>
                        <div class="panel-body">
                            <p>Which kinds of communication would you like to receive from {{ Cache::get('settings.rep_title') }}s who have you in their {{ Cache::get('settings.downline_title') }}?</p>
                            <label>{!! Form::checkbox('block_email', null, $checked['block_email']) !!} Email</label><br>
                            <label>{!! Form::checkbox('block_sms', null, $checked['block_sms']) !!} Text Messages (SMS)</label>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::submit('Update', array('class' => 'cp-button-standard')) !!}
        {!!Form::close()!!}
    </div>
</div>
@endsection
