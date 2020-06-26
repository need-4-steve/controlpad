<div class="alert alert-warning">The following information is required for you to get paid. Note that changes may require reverification, which takes several days and could postpone payments.</div>

<div class="form-group">
    {{ Form::label('account[name]', 'Full Name on Account') }}
    {{ Form::text('account[name]', null, array('id' => 'banking_name', 'class' => 'form-control')) }}
</div>
<div class="form-group">
    {{ Form::label('account[routing]', 'Bank Routing Number') }}
    {{ Form::text('account[routing]', null, array('id' => 'banking_routing', 'class' => 'form-control')) }}
</div>
<div class="form-group">
    {{ Form::label('account[number]', 'Bank Account Number') }}
    {{ Form::text('account[number]', null, array('id' => 'banking_number', 'class' => 'form-control')) }}
</div>

<div class="form-group">
    {{ Form::label('account[type]', 'Account Type') }}
    {{ Form::select('account[type]', [
        'checking' => 'Checking',
        'savings' => 'Savings'
    ], null, array('class' => 'form-control')) }}
</div>