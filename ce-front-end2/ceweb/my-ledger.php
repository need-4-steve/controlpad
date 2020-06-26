<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.pagination.php';
include 'includes/inc.convert.php';

SystemSelectedCheck();

// Handle query //
$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$pagvals = PagValidate("id", "desc");
$json = BuildAndPOST(AFFILIATE, "myledger", $fields, $pagvals);
HandleResponse($json, SUCCESS_NOTHING);

?>    
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>My Ledger</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	<table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">

<?php
// Build search parameters //
$size["id"] = 5;
$size["systemid"] = 5;
$size["batchid"] = 5;
$size["refid"] = 5;
$size["ledgertype"] = "selectledgertype";
$size["amount"] = 5;
$size["eventdate"] = "NULL";
$size["createdat"] = "NULL";

// Build Sort Parameters //
$sort["id"] = "ID";
$sort["systemid"] = "System ID";
$sort["batchid"] = "Batch ID";
$sort["refid"] = "Ref ID";
$sort["ledgertype"] = "Ledger Type";
$sort["amount"] = "Amount";
$sort["eventdate"] = "Event Date";
$sort["createdat"] = "Created";

PagTop($sort, $size, $pagvals);

// Loop through each rule //
foreach ($json['ledger'] as $ledger)
{
	echo "<tr>";
	echo "<td></td>";
	echo "<td align=center>".$ledger['id']."</td>";
	echo "<td align=center>".$ledger['systemid']."</td>";
	echo "<td align=center>".$ledger['batchid']."</td>";
	echo "<td align=center>".$ledger['refid']."</td>";
	echo "<td align=center>".DispLedgerType($ledger['ledgertype'])."</td>";

	if ($ledger['ledgertype'] == 3)
		echo "<td align=center'><a href='#'><div style='text-decoration: underline;' OnClick='BreakdownCommGen(\"gen".$ledger['id']."\", ".$ledger['systemid'].", ".$ledger['batchid'].", ".$_SESSION['user_id'].")'>$".PerfectCents($ledger['amount'])."</div></a><div id='gen".$ledger['id']."'></div></td>";
	else
		echo "<td align=center>$".PerfectCents($ledger['amount'])."</td>";
	
	echo "<td align=center>".DispDate($ledger['eventdate'])."</td>";
	//echo "<td align=center>".$ledger['generation']."</td>";
	//echo "<td align=center>".$ledger['authorized']."</td>";	
	//echo "<td align=center>".$ledger['disabled']."</td>";
	echo "<td align=center>".DispTimestamp($ledger['createdat'])."</td>";
	//echo "<td align=center>".$ledger['updatedat']."</td>";
}

PagBottom($pagvals, $json['count']);

include 'includes/inc.footer.php';

?>
<script language='JavaScript'>
//////////////////////////////////////////////////////////////
// Allow unordered lost to open up on click for generations //
//////////////////////////////////////////////////////////////
function BreakdownCommGen(genid, systemid, batchid, parentid)
{
	// Make Ajax call to API //
	$.post("ajax-ledgercommgen.php?systemid="+systemid+"&batchid="+batchid+"&parentid="+parentid, function( data )
	{
		var obj = JSON.parse(data);
		var finalstr = "<ul>";
		var index;
		for (index=0; index < obj.breakdowngen.length; index++)
		{
			var amount = obj['breakdowngen'][index]['amount'];
			amount = parseFloat(amount).toFixed(2);
			var eleid = genid+"users"+obj['breakdowngen'][index]['generation'];
			var tmpstr = obj['breakdowngen'][index]['generation']+" - $"+amount;
			finalstr += "<li><div OnClick='BreakdownCommUsers(\""+genid+"\", "+eleid+", "+systemid+", "+batchid+", "+parentid+", "+obj['breakdowngen'][index]['generation']+")'><a href='#' style='text-decoration: underline;'>Gen "+tmpstr+"</a></div><div id='"+eleid+"'></div>";
		}
		finalstr += "</ul>";

		var ele = document.getElementById(genid);
		ele.innerHTML = finalstr;

  		//$( ".result" ).html( data );  		
	});
}

////////////////////////////////////////////////////////
// Allow unordered lost to open up on click for users //
////////////////////////////////////////////////////////
function BreakdownCommUsers(genid, eleid, systemid, batchid, parentid, generation)
{
	// Make Ajax call to API //
	$.post("ajax-ledgercommusers.php?systemid="+systemid+"&batchid="+batchid+"&parentid="+parentid+"&generation="+generation, function( data )
	{
		//alert(data);

		var obj = JSON.parse(data);
		var finalstr = "<ul>";
		var index;
		for (index=0; index < obj.breakdownusers.length; index++)
		{
			var amount = obj['breakdownusers'][index]['amount'];
			amount = parseFloat(amount).toFixed(2);
			var tmpobj = obj['breakdownusers'][index];
			var userid = tmpobj['userid'];
			var tmpstr = tmpobj['firstname']+" "+tmpobj['lastname']+" ("+userid+") - $"+amount;
			var orderid = genid+"orders"+userid;

			finalstr += "<li><div OnClick='BreakdownCommOrders("+orderid+", "+systemid+", "+batchid+", "+parentid+", "+userid+")'><a href='#' style='text-decoration: underline;'>"+tmpstr+"</a></div><div id='"+orderid+"'></div>";
		}
		finalstr += "</ul>"; 
		eleid.innerHTML = finalstr;
		
  		//$( ".result" ).html( data );  		
	});
}

/////////////////////////////////////////////////////////
// Allow unordered lost to open up on click for orders //
/////////////////////////////////////////////////////////
function BreakdownCommOrders(orderid, systemid, batchid, parentid, userid)
{
	// Make Ajax call to API //
	$.post("ajax-ledgercommorders.php?systemid="+systemid+"&batchid="+batchid+"&parentid="+parentid+"&userid="+userid, function( data )
	{
		var obj = JSON.parse(data);
		var finalstr = "<ul>";
		var index;
		for (index=0; index < obj.breakdownorders.length; index++)
		{
			var amount = obj['breakdownorders'][index]['amount'];
			amount = parseFloat(amount).toFixed(2);
			var tmpobj = obj['breakdownorders'][index];
			var tmpstr = tmpobj['ordernum']+" - $"+amount;
			finalstr += "<li>"+tmpstr;
		}
		finalstr += "</ul>"; 
		orderid.innerHTML = finalstr;
		
  		//$( ".result" ).html( data );  		
	});
}

</script>
