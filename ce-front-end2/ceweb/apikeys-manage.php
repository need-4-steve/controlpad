<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

$systemid = $_SESSION['systemid'];


// Page not currently used //
// When we do set this page live //
// Then we need to sanitize the inputs //


// Handle parsing for pagenation //
$pagvar = PagTop("id");

/////////////////////////////////
// Handle adding of a rankrule //
/////////////////////////////////
if ($_GET['direction'] == 'delete')
{
	$headers = [];
	$headers[] = "command: addapikey";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;

	$headers[] = "id: ".$_GET['id'];
	
	$response = PostURL($headers, "false");
	HandleResponse($response);
}

if ($_POST['command'] == "disable")
{
	$headers = [];
	$headers[] = "command: disableapikey";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$systemid;
	$headers[] = "id: ".$_GET['id'];
	
	$response = PostURL($headers, "false")."\n";
	HandleResponse($response);
}

/////////////////////////////////////////////////////////////
// Always run a query on load of page for most recent view //
/////////////////////////////////////////////////////////////
$headers = [];
$headers[] = "command: queryapikeys";
$headers[] = "authemail: ".$authemail;
$headers[] = "apikey: ".$apikey;
$headers[] = "systemid: ".$systemid;

// Add pagination options //
$headers[] = "search: ".$pagvar['search'];
$headers[] = "orderby: ".$pagvar['orderby'];
$headers[] = "orderdir: ".$pagvar['orderdir'];
$headers[] = "offset: ".$pagvar['offset'];
$headers[] = "limit: ".$pagvar['limit'];

$json = PostURL($headers, "false");
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Manage API Keys</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
    
    <table width="100%">
	<tr><td><p class="text-muted font-13 m-b-30" align=left>Display <?php echo PagSelectLimit($pagvals['limit']);?> Entries</p></td>
	<form method="POST" action="<?php $_SERVER['REQUEST_URI'].'?'.$qstring;?>">
    <td><p class="text-muted font-13 m-b-30" align=right><input type="edit" name="search" value="<?=$pagvals['search'];?>"><input type="submit" value="Search"></p></td></tr>
    </form>
	</table>

	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
		<thead>
        <tr>
        	<th>ID</th>
	        <th>System ID</th>
	        <th>Label</th>
	        <th>Created</th>
	        <th>Updated</th>
	        <th></th>
        </tr>
    </thead>
<?php

// Loop through each rule //
foreach ($json['apikey'] as $apikey)
{
	echo "<tr>";

	echo "<td align=center>".$apikey['id']."</td>";
	echo "<td align=center>".$apikey['system_id']."</td>";
	echo "<td align=center>".$apikey['label']."</td>";
	echo "<td>".DispTimestamp($apikey['created_at'])."</td>";
	echo "<td>".DispTimestamp($apikey['updated_at'])."</td>";

	echo "<form method=POST action='?direction=delete&id=".$apikey['id']."'>";
	echo "<td align=center><input type=submit value='Delete'></td>";
	echo "</form>";

	echo "</tr>";
}

include 'includes/inc.footer.php';

?>