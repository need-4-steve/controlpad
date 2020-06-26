@extends('layouts.boss')
@section('sub-title')
	Validate Bank Account
@stop
@section('content')
<div class="edit">
	<div class="row">
		<div class="col col-xl-3 col-lg-4 col-md-6 col-sm-6">
		    {{ Form::open(['url' => 'verify-banking', 'method' => 'POST']) }}

				<div class="alert alert-warning">
					Two to three days after your banking information is saved, you will receive two small deposits in your bank account. To verify that you are the owner of this bank account, enter the amounts received.
				</div>

				<div class="form-group">
					{{ Form::label('amount1', 'First Deposit') }}
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-dollar"></i></span>
						{{ Form::text('amount1', null, ['class' => 'form-control']) }}
					</div>
				</div>

				<div class="form-group">
					{{ Form::label('amount2', 'Second Deposit') }}
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-dollar"></i></span>
						{{ Form::text('amount2', null, ['class' => 'form-control']) }}
					</div>
				</div>

		        {{ Form::submit('Verify Bank Account', array('class' => 'cp-button-standard')) }}

		    {{ Form::close() }}
		</div>
	</div>
</div>
@stop
