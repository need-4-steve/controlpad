@inject('_settings', 'globalSettings')
<footer class="site-footer">
		<ul class="footer-links">
			@if(session()->has('store_owner'))
			<li><a href="/contact">Contact Me</a></li>
			@else
			<li><a href="/contact">Contact Us</a></li>
			@endif

			@if ($_settings->getGlobal('return_policy', 'show'))
			<li><a href="{{$_settings->getGlobal('return_policy', 'value')}}">Return Policy</a></li>
			@endif
			@if ($_settings->getGlobal('terms', 'show'))
            <li><a href="{{$_settings->getGlobal('terms', 'value')}}">Terms &amp; Conditions</a></li>
			@endif
			<li><a href="/privacy">Privacy Policy</a></li>
		</ul>
		@if($_settings->getGlobal('address', 'show'))
			@if ($_settings->getGlobal('reseller_address_store', 'show') && session()->get('store_owner.businessAddress'))
				<p>{{formatAddress(session()->get('store_owner.businessAddress'))}}</p>
			@else
				<p>{{$_settings->getGlobal('address', 'value')}}</p>
			@endif
		@endif
		<p><span>&copy; {{ date('Y') }} - {{$_settings->getGlobal('company_name', 'value')}}</span></p>
</footer>
