@extends('layouts.boss')
@section('content')
<div class="create">
	<div class="row">
		<div class="col col-md-12">
			@include('_helpers.breadcrumbs')
		    <h1 class="no-top">New Cart</h1>
		    {{ Form::open(array('url' => 'carts')) }}


			    <div class="form-group">
			        {{ Form::label('product_id', 'Product Id') }}
			        {{ Form::text('product_id', Input::old('product_id'), array('class' => 'form-control')) }}
			    </div>

			    <div class="form-group">
			        {{ Form::label('disabled', 'Disabled') }}
			        {{ Form::text('disabled', Input::old('disabled'), array('class' => 'form-control')) }}
			    </div>


			    {{ Form::submit('Add Cart', array('class' => 'cp-button-standard')) }}

		    {{ Form::close() }}
	    </div>
	</div>
</div>
@stop
