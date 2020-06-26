<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

// Handle query //
$_POST["batchid"] = $_SESSION['batchid'];
$fields[] = "batchid";
$json = BuildAndPOST(CLIENT, "queryauditusers", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Users Audit Report - Batch: <?=$_SESSION['batchid']?></small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
    
   	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">  
<?php

// Display the userid's //
echo "<tr><td align=center></td><td align=center colspan=6><b>Payout</b></td></tr>";
echo "<tr><td align=center><b><font size='3' color='black'>User ID</font></b></td>";
foreach ($json['userids'] as $data)
{
	echo "<td align=center><b><font size='3' color='black'>".$data['userid']."</font></b></td>";
}
echo "</tr>";

// Display the user generational data //
$index = 1;
foreach ($json['auditusers'] as $key => $array)
{
	echo "<tr>";
	echo "<td align=center><b>Generation ".$index."</b></td>";

	foreach ($array['data'] as $generation => $array2)
	{
		echo "<td align=center>$";
		echo number_format($array2['total'], 2);
		echo "</td>";
	}
	$index++;
	echo "</tr>";
}

echo "</table>";

include 'includes/inc.footer.php';

?>