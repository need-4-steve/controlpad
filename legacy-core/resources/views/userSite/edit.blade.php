@extends('layouts.boss')
@section('sub-title')
	Edit Your Site
@endsection
@section('content')
<div class="edit">
	<div class="row">
		<div class="col col-xl-4 col-lg-6 col-md-8 col-sm-12">
		    {{ Form::model($userSite, array('route' => array('user-sites.update', $userSite->id), 'method' => 'PUT', 'files' => true)) }}
			    
			    <div class="form-group">
			        {{ Form::label('title', 'Title') }}
			        {{ Form::text('title', null, array('class' => 'form-control')) }}
			    </div>
			    
			    <div class="form-group">
			        {{ Form::label('body', 'Content') }}<br>
			        {{ Form::textarea('body', null, array('class' => 'form-control')) }}
			    </div>

			    <div class="form-group">
			        <label>{{ Form::checkbox('display_phone') }} Display My Phone Number</label>
			    </div>

			    {{ Form::submit('Update Site', array('class' => 'cp-button-standard')) }}
		
		    {{ Form::close() }}
		</div>
	</div>
</div>
@endsection
@section('modals')
	@include('_helpers.wysiwyg_modals')
@endsection