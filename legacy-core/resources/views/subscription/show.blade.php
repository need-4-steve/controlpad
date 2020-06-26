@extends('layouts.boss')
@section('sub-title')
	Viewing Monthly Plan
@stop
@section('content')
<div class="show">
	<div class="row page-actions">
		@if (Auth::user()->hasRole(['Superadmin', 'Admin']))
		    <div class="btn-group" id="record-options">
			    <a class="btn btn-default" href="{{ url('subscriptionPlans/'.$subscriptionPlan->id .'/edit') }}" title="Edit"><i class="lnr lnr-pencil"></i></a>
			    @if ($subscriptionPlan->disabled == 0)
				    {{ Form::open(array('url' => 'subscriptionPlans/disable', 'method' => 'DISABLE')) }}
				    	<input type="hidden" name="ids[]" value="{{ $subscriptionPlan->id }}">
				    	<button class="btn btn-default active" title="Currently enabled. Click to disable.">
				    		<i class="lnr lnr-eye"></i>
				    	</button>
				    {{ Form::close() }}
				@else
				    {{ Form::open(array('url' => 'subscriptionPlans/enable', 'method' => 'ENABLE')) }}
				    	<input type="hidden" name="ids[]" value="{{ $subscriptionPlan->id }}">
				    	<button class="btn btn-default" title="Currently disabled. Click to enable.">
				    		<i class="lnr lnr-eye"></i>
				    	</button>
				    {{ Form::close() }}
				@endif
			    {{ Form::open(array('url' => 'subscriptionPlans/' . $subscriptionPlan->id, 'method' => 'DELETE', 'onsubmit' => 'return confirm("Are you sure you want to delete this subscriptionPlan? This cannot be undone.");')) }}
			    	<button class="btn btn-default" title="Delete">
			    		<i class="lnr lnr-trash2" title="Delete"></i>
			    	</button>
			    {{ Form::close() }}
			</div>
		@endif
	</div><!-- row -->
	<div class="row">
		<div class="col col-xl-4 col-lg-6 col-md-8 col-sm-12">
		    <table class="table">

		        <tr>
		            <th>Description:</th>
		            <td>{{ $subscriptionPlan->description }}</td>
		        </tr>

		        <tr>
		            <th>Frequency:</th>
		            <td>{{ $subscriptionPlan->frequency }}</td>
		        </tr>

		        <!-- <tr>
		            <th>Details:</th>
		            <td>{{ $subscriptionPlan->details }}</td>
		        </tr> -->

		        <tr>
		            <th>Amount:</th>
		            <td>$ {{ $subscriptionPlan->amount }}</td>
		        </tr>

		        <tr>
		            <th>Disabled:</th>
		            <td>{{ $subscriptionPlan->disabled }}</td>
		        </tr>

		    </table>
	    </div>
	</div>
</div>
@stop
