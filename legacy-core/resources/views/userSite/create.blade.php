@extends('layouts.boss')
@section('sub-title')
	New User Site
@endsection
@section('content')
<div class="create">
	<div class="row">
		<div class="col col-md-12">
		    {{ Form::open(array('url' => 'user-sites')) }}
		
			    
			    <div class="form-group">
			        {{ Form::label('user_id', 'User Id') }}
			        {{ Form::text('user_id', Input::old('user_id'), array('class' => 'form-control')) }}
			    </div>
			    
			    <div class="form-group">
			        {{ Form::label('body', 'Body') }}
			        {{ Form::text('body', Input::old('body'), array('class' => 'form-control')) }}
			    </div>
			    
		
			    {{ Form::submit('Add UserSite', array('class' => 'cp-button-standard')) }}
	
		    {{ Form::close() }}
	    </div>
	</div>
</div>
@endsection