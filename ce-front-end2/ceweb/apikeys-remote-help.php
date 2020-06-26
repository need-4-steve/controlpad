<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.pagination.php';
include 'includes/inc.select.php';

?>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Remote Integration Help</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
	
	<h2>PHP Example</h2>
	<b><u><font size=3>Adding a user:</font></u></b><br>
	$url = "https://ce-api.controlpad.com";<br>
	$headers = [];<br>
	$headers[] = "command: adduser";<br>
	$headers[] = "authemail: youremail@domain.com";<br>
	$headers[] = "apikey: 742053e0f5cc38103811ce15aba2c02d5fc65047db2098d4795f187ae0b2b4c5";<br>
	$headers[] = "systemid: 1";<br>
	$headers[] = "userid: 99";<br>
	$headers[] = "parentid: 55";<br>
	$headers[] = "signupdate: 2016-12-25";<br>
<br>
	$ch = curl_init();<br>
	curl_setopt($ch, CURLOPT_URL, $url);<br>
	curl_setopt($ch, CURLOPT_POST, 1);<br>
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);<br>
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>
	$data = curl_exec($ch);<br>
	curl_close($ch);<br>
<br>
<br>
	<b><u><font size=3>Adding a receipt:</font></u></b><br>
	$url = "https://ce-api.controlpad.com";<br>
	$headers = [];<br>
	$headers[] = "command: addreceipt";<br>
	$headers[] = "authemail: youremail@domain.com";<br>
	$headers[] = "apikey: 742053e0f5cc38103811ce15aba2c02d5fc65047db2098d4795f187ae0b2b4c5";<br>
	$headers[] = "systemid: 1";<br>
	$headers[] = "userid: 99";<br>
	$headers[] = "receiptid: 55";<br>
	$headers[] = "amount: 99.99";<br>
	$headers[] = "purchasedate:  2016-12-25";<br>
	$headers[] = "commissionable: true";<br>
<br>
	$ch = curl_init();<br>
	curl_setopt($ch, CURLOPT_URL, $url);<br>
	curl_setopt($ch, CURLOPT_POST, 1);<br>
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);<br>
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);<br>
	$data = curl_exec($ch);<br>
	curl_close($ch);<br>
<br>

<?php

include 'includes/inc.footer.php';

?>