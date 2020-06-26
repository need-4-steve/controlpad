<?php

//////////////////////////////////
// Build Qualify values options //
//////////////////////////////////
function SelectBuildOption($default, $number, $name)
{
	if ($default == $number)
		return "<option selected value='".$number."'>".$name;
	else
		return "<option value='".$number."'>".$name;
}

/////////////////////////////////////////////
// Allow selection of commission plan type //
/////////////////////////////////////////////
function SelectCommType($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	//$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '	<select name="'.$varname.'">';
	
	$display .= SelectBuildOption($default, 0, ""); //Choose Play Type");
	$display .= SelectBuildOption($default, 1, "Hybrid-Uni");
	$display .= SelectBuildOption($default, 2, "Breakaway");
	$display .= SelectBuildOption($default, 3, "Binary");

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

////////////////////////////////////
// Allow selection of payout type //
////////////////////////////////////
function SelectPayoutType($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	//$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '	<select name="'.$varname.'">';

	$display .= SelectBuildOption($default, 0, ""); //Choose Payout Type");
	$display .= SelectBuildOption($default, 1, "Monthly");
	$display .= SelectBuildOption($default, 2, "Weekly");
	$display .= SelectBuildOption($default, 3, "Daily");
	$display .= SelectBuildOption($default, 4, "Custom API");

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

///////////////////////////////
// Handle Monthday selection //
///////////////////////////////
function SelectMonthDay($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '		<option></option>';

	for ($index=1; $index <= 28; $index++)
	{
		$display .= SelectBuildOption($default, $index, $index);
	}

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

//////////////////////////////
// Handle Weekday selection //
//////////////////////////////
function SelectWeekDay($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '		<option></option>';

	for ($index=1; $index <= 7; $index++)
	{
		$display .= SelectBuildOption($default, $index, $index);
	}

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

//////////////////////////
// Handle qualifty type //
//////////////////////////
function SelectQualifyType($varname, $default)
{
	$display .= "<select name='".$varname."'>"; 
	$display .= "<option value='0'>";

	$display .= SelectBuildOption($default, 1, "Personal Sales");
	$display .= SelectBuildOption($default, 2, "Group Sales");
	$display .= SelectBuildOption($default, 3, "Signup Count");
	$display .= SelectBuildOption($default, 4, "Rank");
	$display .= SelectBuildOption($default, 5, "Customer Count");
	$display .= SelectBuildOption($default, 6, "Affiliate Count");
	$display .= SelectBuildOption($default, 7, "Customer and Affiliate Count");
	$display .= SelectBuildOption($default, 8, "Level 1 Customer Count");
	$display .= SelectBuildOption($default, 9, "Level 1 Affiliate Count");
	//$display .= SelectBuildOption($default, 10, "United - Tokens used");
	//$display .= SelectBuildOption($default, 11, "United - Tokens bought and used");
	$display .= SelectBuildOption($default, 12, "My Wholesale (Type 2)");
	$display .= SelectBuildOption($default, 13, "My Retail (Type 2)");

	// Chalkatour needed //
	$display .= SelectBuildOption($default, 14, "Team Wholesale");
	$display .= SelectBuildOption($default, 15, "Team Retail");

	$display .= SelectBuildOption($default, 16, "Rank Sum Leg");
	$display .= SelectBuildOption($default, 17, "Rank Sum Lvl1");

	$display .= SelectBuildOption($default, 21, "PSQ");

	$display .= "</select>";

	return $display;
}

///////////////////////////////
// Handle Monthday selection //
///////////////////////////////
function SelectUserType($varname, $default)
{
	$display .= "<select name='".$varname."'>"; 
	$display .= "<option value='0'>";

	$display .= SelectBuildOption($default, 1, "Affiliate");
	$display .= SelectBuildOption($default, 2, "Customer");

	$display .= "</select>";

	return $display;
}

/////////////////////////////////////////////
// Allow selection of commission plan type //
/////////////////////////////////////////////
function SelectInvType($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	
	$display .= SelectBuildOption($default, "", "");
	$display .= SelectBuildOption($default, 1, "Wholesale");
	$display .= SelectBuildOption($default, 2, "Retail");
	$display .= SelectBuildOption($default, 3, "Cash and Carry");
	$display .= SelectBuildOption($default, 4, "Sold on Corporate");
	$display .= SelectBuildOption($default, 5, "Affiliate on Corporate");

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

/////////////////////////////////////////
// Allow selection of transation event //
/////////////////////////////////////////
function SelectEvent($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	
	$display .= SelectBuildOption($default, "", "");
	$display .= SelectBuildOption($default, 1, "On Wholesale Date");
	$display .= SelectBuildOption($default, 2, "On Retail Date");

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

/////////////////////////////////////////
// Allow selection of transation event //
/////////////////////////////////////////
function SelectLedgerType($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	//$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '	<select name="'.$varname.'">';
	
	$display .= SelectBuildOption($default, "", "");
	$display .= SelectBuildOption($default, 1, "Nacha Payment");
	$display .= SelectBuildOption($default, 2, "Rewards");
	$display .= SelectBuildOption($default, 3, "Commissions");
	$display .= SelectBuildOption($default, 4, "Transferred");
	//$display .= SelectBuildOption($default, 5, "CM Purchased"); // Only United //
	//$display .= SelectBuildOption($default, 6, "CM Used"); // Only United //
	$display .= SelectBuildOption($default, 7, "Bonus");
	$display .= SelectBuildOption($default, 8, "Custom Payout");
	$display .= SelectBuildOption($default, 9, "Repair");
	$display .= SelectBuildOption($default, 10, "Signup Bonus");

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

///////////////////////////////////////////////////
// Manage how the users and receipts are related //
///////////////////////////////////////////////////
function SelectStackType($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	//$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '	<select name="'.$varname.'">';
	
	$display .= SelectBuildOption($default, 1, "Full Stack");
	$display .= SelectBuildOption($default, 2, "Condensed Stack");

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

////////////////////
// Select a batch //
////////////////////
function SelectBatch($name, $default)
{
	global $g_BatchJson;
	global $g_BatchDisplay;

	$retval = "<select name='".$name."'>";
	foreach ($g_BatchJson['batches'] as $batch)
	{
		$time = strtotime($batch['startdate']);
		$month = date("F", $time);
		$year = date("Y", $time);

		if ($default == $batch['id'])
		{
			$g_BatchDisplay = $month." ".$year; // Retain for DisplayBatch //
			$retval .= "<option selected value='".$batch['id']."'>".$month." ".$year;
		}
		else
		{
			$retval .= "<option value='".$batch['id']."'>".$month." ".$year;
		}
	}	
	$retval .= "</select>";
	return $retval;
	
	/*
	global $g_coredomain;
	global $authemail;
	global $apikey;

	$curlstring = $g_coredomain;
	$headers = [];
	$headers[] = "command: querybatches";
	$headers[] = "authemail: ".$authemail;
	$headers[] = "apikey: ".$apikey;
	$headers[] = "systemid: ".$_SESSION['systemid'];
	$headers[] = "authorized: false";
	$jsonrules = PostURL($curlstring, $headers);
	$retarray = json_decode($jsonrules, true);

	$display .= "<select name='".$name."'>"; 
	$display .= "<option value='0'>";
	foreach ($retarray['batches'] as $batch)
	{
		if ($rank['id'] == $default)
			$display .= "<option selected value='".$batch['id']."'>".$batch['id']." - From:".$batch['startdate']." To:".$batch['enddate'];
		else
			$display .= "<option value='".$batch['id']."'>".$batch['id']." - From:".$batch['startdate']." To:".$batch['enddate'];
	}
	$display .= "</select>";

	return $display;
	*/
}

/////////////////////////
// Wholesale vs Retail //
/////////////////////////
function SelectPayType($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	//$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '	<select name="'.$varname.'">';
	
	$display .= SelectBuildOption($default, 1, "Wholesale");
	$display .= SelectBuildOption($default, 2, "Retail");

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

/////////////////////////
// Wholesale vs Retail //
/////////////////////////
function SelectTrueFalse($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	//$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '	<select name="'.$varname.'">';
	
	$display .= SelectBuildOption($default, 't', "True");
	$display .= SelectBuildOption($default, 'f', "False");

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

//////////////////////////////////
// Allow selection of rankrules //
//////////////////////////////////
function SelectRankRule($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	//$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '	<select name="'.$varname.'">';
		
	$display .= SelectBuildOption("", "", "");
	for ($index=1; $index <= 25; $index++)
	{
		$display .= SelectBuildOption($default, $index, $index);
	}

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

/////////////////////////////
// Selection of generation //
/////////////////////////////
function SelectGeneration($varname, $default)
{
	$display .= '<div class="col-md-9 col-sm-9 col-xs-12">';
	//$display .= '	<select class="select2_single form-control" tabindex="-1" name="'.$varname.'">';
	$display .= '	<select name="'.$varname.'">';
		
	$display .= SelectBuildOption("", "", "");
	$display .= SelectBuildOption($default, '-1', "1 - Personally Sponsored");
	for ($index=1; $index <= 25; $index++)
	{
		$display .= SelectBuildOption($default, $index, $index);
	}

	$display .= '	</select>';
	$display .= '</div>';

	return $display;
}

?>