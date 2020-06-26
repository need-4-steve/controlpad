	@if (Session::has('message') || isset($message))
	 <div class="row">
    	<div class="col col-md-12">
			<div class="success-message">
				@if (Session::has('message'))
					{!! Session::get('message') !!}
				@else
					{!! Session::get($message) !!}
				@endif
			</div>
		</div>
	</div>
	@elseif (Session::has('message_warning') || isset($message_warning))
		 <div class="row">
	    	<div class="col col-md-12">
				<div class="warning-message">
					@if (Session::has('message_warning'))
						{!! Session::get('message_warning') !!}
					@else
						{!! $message_warning !!}
					@endif
				</div>
			</div>
		</div>
	@elseif (Session::has('message_danger') || isset($message_danger))
		<div class="row">
	    	<div class="col col-md-12">
				<div class="warning-message">
					@if (Session::has('message_danger'))
						{!! Session::get('message_danger') !!}
					@else
						{!! $message_danger !!}
					@endif
				</div>
			</div>
		</div>
	@endif
