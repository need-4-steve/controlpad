<?php

///////////////////////////////////////
// Handle pagination variable values //
///////////////////////////////////////
function PagValidate($default_orderby, $default_orderdir)
{
	// Handle parsing for pagenation //
	//if (!empty($_POST['search']))
	//	$retval['search'] = $_POST['search'];
	//else if (!empty($_GET['search']))
	//	$retval['search'] = $_GET['search'];

	// Make sure we copy POST over to GET //
	foreach ($_POST as $key => $value)
	{
		$srchkey = substr($key, 0, 7);
		if (($srchkey == "search-") && (!empty($value)))
		{
			$_GET[$key] = $value;
		}
	}
	// Make sure we copy GET over to POST //
	foreach ($_GET as $key => $value)
	{
		$srchkey = substr($key, 0, 7);
		if (($srchkey == "search-") && (!empty($value)))
		{
			$_POST[$key] = $value;
		}
	}

	// Handle search //
	$search = urldecode($_GET['search']);
	$array = explode("&", $search);
	foreach ($array as $set)
	{
		$pair = explode("=", $set);
		$_POST[$pair[0]] = $pair[1];
		$_GET[$pair[0]] = $pair[1];
	}

	// Handle sort defaults //
	$retval['limit'] = $_GET["limit"];
	if (empty($retval['limit']))
		$retval['limit'] = 10;
	$retval['offset'] = $_GET["offset"];
	if (empty($retval['offset']))
		$retval['offset'] = 0;
	$retval['orderby'] = $_GET['orderby'];
	if (empty($retval['orderby']))
		$retval['orderby'] = $default_orderby;
	$retval['orderdir'] = $_GET['orderdir'];
	if (empty($retval['orderdir']))
	{
		if (empty($default_orderdir))
			$retval['orderdir'] = "asc";
		else
			$retval['orderdir'] = $default_orderdir;
	}

	// pre-prepare the querystring //
	$retval['qstring'] = "limit=".$retval['limit']."&orderby=".$retval['orderby'];

	return $retval;
}

///////////////////////
// Fix Table Sorting //
///////////////////////
function PagTableSort($heading, $ordermatch, $pagvar)
{
	echo "<td align=center><b>";

	if ($pagvar['orderby'] == $ordermatch)
	{
		if ($pagvar['orderdir'] == "asc")
			echo "<a href='?orderdir=desc&".$pagvar['qstring']."&orderby=".$ordermatch."&search=".PagBuildSearch()."'>".$heading."<span class='fa fa-chevron-down right'></span></a>";
		else if ($pagvar['orderdir'] == "desc")
			echo "<a href='?orderdir=asc&".$pagvar['qstring']."&orderby=".$ordermatch."&search=".PagBuildSearch()."'>".$heading."<span class='fa fa-chevron-up right'></span></a>";
	}
	else
	{
		if ($pagvar['orderdir'] == "asc")
			echo "<a href='?orderdir=asc&".$pagvar['qstring']."&orderby=".$ordermatch."&search=".PagBuildSearch()."'>".$heading."</a>";
		else if ($pagvar['orderdir'] == "desc")
			echo "<a href='?orderdir=desc&".$pagvar['qstring']."&orderby=".$ordermatch."&search=".PagBuildSearch()."'>".$heading."</a>";
	}

	echo "</b></td>";
}

///////////////////////////
// Handle top pagination //
///////////////////////////
function PagTop($sort, $fields, $pagvals)
{
	echo '<p class="text-muted font-13 m-b-30" align=left>Display ';
	echo PagSelectLimit($pagvals['limit']);
	echo ' Entries</p>';

	PagSort($sort, $pagvals);
	PagDisplaySearch($fields);
}

///////////////////////////////////////////////////////////
// Handle pagination functionality at the bottom of page //
///////////////////////////////////////////////////////////
function PagBottom($pagvar, $recordcount)
{
	if (empty($recordcount))
		$recordcount = 0;

	echo "<table width='100%'>";
	$numoffset = $pagvar['offset']+$pagvar['limit'];
	if ($numoffset > $recordcount)
		$numoffset = $recordcount;

	if ($recordcount == 0)
		$startnum = 0;
	else
		$startnum = $pagvar['offset']+1;
	echo "<tr><td align=left>Showing ".$startnum." to ".$numoffset." of ".$recordcount." entries</td>";

	echo "<td align=right>";
	if ($pagvar['offset'] != 0)
		echo "<b><a href='?".$pagvar['qstring']."&offset=".($pagvar['offset']-$pagvar['limit'])."&search=".PagBuildSearch()."'>&lt;&lt; prev</a></b>";
	echo "&nbsp;&nbsp;&nbsp;&nbsp;"; // Spacer //

	if ($numoffset != $recordcount)
		echo "<b><a href='?".$pagvar['qstring']."&offset=".($pagvar['offset']+$pagvar['limit'])."&search=".PagBuildSearch()."'>next &gt;&gt;</a></b>";
	else
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

	echo "</td></tr>";
	echo "</table>";
}

////////////////////////////
// Handle limit selection //
////////////////////////////
function PagSelectLimit($limit, $url = "")
{
	$display .= "<select name='limit' onchange='location.href=\"".$url."?limit=\"+this.value'>";

	if ($limit == 10)
		$display .= "<option selected value='10'>10";
	else
		$display .= "<option value='10'>10";

	if ($limit == 25)
		$display .= "<option selected value='25'>25";
	else
		$display .= "<option value='25'>25";

	if ($limit == 50)
		$display .= "<option selected value='50'>50";
	else
		$display .= "<option value='50'>50";

	if ($limit == 100)
		$display .= "<option selected value='100'>100";
	else
		$display .= "<option value='100'>100";

	if ($limit == 100)
		$display .= "<option selected value='20000'>20000";
	else
		$display .= "<option value='20000'>20000";

	$display .= "</select>";

	return $display;
}

///////////////////////////////////////
// Build the full sorting capability //
///////////////////////////////////////
function PagSort($sort, $pagvals)
{
	echo "<tr>";
	echo "<td></td>";
	foreach ($sort as $key => $value)
	{
		PagTableSort($value, $key, $pagvals);
	}
	echo "<td></td>";
    echo "</tr>";
}

////////////////////////////////////
// Build the search functionality //
////////////////////////////////////
function PagDisplaySearch($fields)
{
	echo "<form method='POST' action='".$_SERVER['PHP_SELF']."'>";
	echo "<tr align=center><td><input type='submit' value='Clear'></td>";
	echo "</form>";

	echo "<form method='POST' action=''>";
	foreach ($fields as $key => $value)
	{
		echo "<td align=center>";
		if ($value == "NULL")
		{
			// Display nothing //
		}
		else if ($value == "selectcommtype")
			echo SelectCommType("search-".$key, $_POST['search-'.$key]);
		else if ($value == "selectpayouttype")
			echo SelectPayoutType("search-".$key, $_POST['search-'.$key]);
		else if ($value == "selectqualifytype")
			echo SelectQualifyType("search-".$key, $_POST['search-'.$key]);
		else if ($value == "selectinvtype")
			echo SelectInvType("search-".$key, $_POST['search-'.$key]);
		else if ($value == "selectevent")
			echo SelectEvent("search-".$key, $_POST['search-'.$key]);
		else if ($value == "selectusertype")
			echo SelectUserType("search-".$key, $_POST['search-'.$key]);
		else if ($value == "selectledgertype")
			echo SelectLedgerType("search-".$key, $_POST['search-'.$key]);
		else if ($value == "selectbatch")
			echo SelectBatch("search-".$key, $_POST['search-'.$key]);
		else if (is_numeric($value))
		{
			echo "<input type=edit name='search-".$key."' size='".$value."' value='".$_POST['search-'.$key]."'>";
		}
		else
		{
			echo "n/a";
		}
		echo "</td>";
	}
	echo "<td align='center'><input type='submit' value='Search'></td></tr>";
	echo "</form>";
}

/////////////////////////////
// build the search string //
/////////////////////////////
function PagBuildSearch()
{
	foreach ($_GET as $key => $value)
	{
		$scheck = substr($key, 0, 7);
		if ($scheck == "search-")
		{
			$retval .= $key."=".$value."&";
		}
	}
	$retval = rtrim($retval, "&");
	return urlencode($retval);
}

?>
