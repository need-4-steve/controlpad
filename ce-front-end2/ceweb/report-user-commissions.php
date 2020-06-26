<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';

SystemSelectedCheck();

$systemid = $_SESSION['systemid'];

$tmpuserid = 57;

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$curlstring = $coredomain;
$headers = [];
$headers[] = "command: queryusercomm";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: ".$systemid;

$headers[] = "userid: ".$tmpuserid;

$json = PostURL($headers, "false");
HandleResponse($json, SUCCESS_NOTHING);
?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>View User Commissions</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
    <p class="text-muted font-13 m-b-30" align=right><input type="edit" name="search"><input type="submit" value="Search"></p>
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
		<thead>
        <tr>
        	<th>ID</th>
        	<th>System ID</th>
        	<th>Batch ID</th>
        	<th>User ID</th>
	        <th>Amount</th>
        </tr>
    </thead>
<?php

// Loop through each commission //
foreach ($array['commission'] as $commission)
{
	echo "<tr>";
	echo "<td align=center>".$commission['id']."</td>";
	echo "<td align=center>".$commission['system_id']."</td>";
	echo "<td align=center>".$commission['batch_id']."</td>";
	echo "<td align=center>".$commission['user_id']."</td>";
	echo "<td align=center>".$commission['amount']."</td>";
	echo "</tr>";
}

include 'includes/inc.footer.php';

?>