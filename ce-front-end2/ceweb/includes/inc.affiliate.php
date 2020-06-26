<?php

/////////////////////////////////////////
// Return the json of the default menu //
/////////////////////////////////////////
function AffilDefaultMenuJson()
{
	// Use this json if not defined by admin //
	$jsonmenu =  '[';

	$jsonmenu .= '{"type":"folder","id":"1","name":"My Records","default":"My Records","class":"fa fa-folder"},';
	$jsonmenu .= '{"type":"link","id":"2","name":"My Payments","default":"My Payments","enabled":"on","webpage":"my-ledger.php?search=search-ledgertype=8"},';
	$jsonmenu .= '{"type":"link","id":"3","name":"My Commissions","default":"My Commissions","enabled":"on","webpage":"my-commissions.php"},';
	$jsonmenu .= '{"type":"link","id":"4","name":"My Achievement Bonuses","default":"My Achievement Bonuses","enabled":"on","webpage":"my-achvbonuses.php"},';
	$jsonmenu .= '{"type":"link","id":"5","name":"My Bonuses","default":"My Bonuses","enabled":"on","webpage":"my-bonuses.php"},';
	$jsonmenu .= '{"type":"link","id":"6","name":"My RankGen Bonuses","default":"My RankGen Bonuses","enabled":"on","webpage":"my-rankgenbonuses.php"},';
	$jsonmenu .= '{"type":"link","id":"7","name":"My Ledger","default":"My Ledger","enabled":"on","webpage":"my-ledger.php"},';
	$jsonmenu .= '{"type":"link","id":"8","name":"My Breakdown","default":"My Breakdown","enabled":"on","webpage":"my-breakdown.php"},';
	$jsonmenu .= '{"type":"link","id":"9","name":"My Team Volume","default":"My Team Volume","enabled":"on","webpage":"my-stats.php"},';
	$jsonmenu .= '{"type":"link","id":"10","name":"My Personal Volume","default":"My Personal Volume","enabled":"on","webpage":"my-stats-lvl1.php"},';
	$jsonmenu .= '{"type":"link","id":"11","name":"My Rank Rules Missed","default":"My Rank Rules Missed","enabled":"on","webpage":"my-rankrules-missed.php"},';

	$jsonmenu .= '{"type":"folder","id":"12","name":"Downline Records","default":"Downline Records","class":"fa fa-group"},';
	$jsonmenu .= '{"type":"link","id":"13","name":"My Downline Report","default":"My Downline Report","enabled":"on","webpage":"mydownlinereport.php"},';
	$jsonmenu .= '{"type":"link","id":"14","name":"My Team Contact","default":"My Team Contact","enabled":"on","webpage":"my-team-contact.php"},';

	$jsonmenu .= '{"type":"folder","id":"15","name":"Hierarchy","default":"Hierarchy","class":"fa fa-users"},';
	$jsonmenu .= '{"type":"link","id":"16","name":"My Downline","default":"My Downline","enabled":"on","webpage":"my-downline.php"}';

	$jsonmenu .= ']';

	return $jsonmenu;
}

/////////////////////////////////
// Default affiliate home json //
/////////////////////////////////
function AffilDefaultHomeJson()
{
	$json = '[
        {"x":0,"y":0,"w":2,"h":6,"i":"1","name":"Wholesale PV","enabled":"checked"},
        {"x":2,"y":0,"w":2,"h":6,"i":"2","name":"Personally Sponsored Qualified","enabled":"checked"},
        {"x":4,"y":0,"w":2,"h":6,"i":"3","name":"Site Sales","enabled":"checked"},
        {"x":0,"y":7,"w":2,"h":6,"i":"4","name":"Wholesale TV","enabled":"checked"},
        {"x":2,"y":7,"w":2,"h":6,"i":"5","name":"Level 1 Mentors","enabled":"checked"},
        {"x":4,"y":7,"w":2,"h":6,"i":"6","name":"Career Title","enabled":"checked"},
        {"x":0,"y":9,"w":2,"h":6,"i":"7","name":"Wholesale EV","enabled":"checked"},
        {"x":2,"y":9,"w":2,"h":6,"i":"8","name":"Master Mentor Legs","enabled":"checked"},
        {"x":4,"y":9,"w":2,"h":6,"i":"9","name":"Current Title","enabled":"checked"},
        {"x":0,"y":11,"w":2,"h":6,"i":"10","name":"Team Volume Retail","enabled":""},
        {"x":2,"y":11,"w":2,"h":6,"i":"11","name":"PV Retail","enabled":""},
        {"x":4,"y":11,"w":2,"h":6,"i":"12","name":"EV Retail","enabled":""},
        {"x":0,"y":13,"w":2,"h":6,"i":"13","name":"Item Count Personal","enabled":""},
        {"x":2,"y":13,"w":2,"h":6,"i":"14","name":"Item Count Enterprise","enabled":""},
        {"x":4,"y":13,"w":2,"h":6,"i":"15","name":"Affiliate Sales","enabled":""},
        {"x":0,"y":15,"w":2,"h":6,"i":"16","name":"Wholesale TV (5-lvl)","enabled":""}
	]';

    return $json;
}

/////////////////////////////////////////
// Return the json of the default menu //
/////////////////////////////////////////
function AffilDefaultDownlineJson()
{
	// Use this json if not defined by admin //
	$jsonmenu =  '[';

	$jsonmenu .= '{"column":"userid","heading":"Designer ID","default":"User ID","enabledtable":"","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"ucell","heading":"Designer Cell","default":"User Cell","enabledtable":"","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"uemail","heading":"Designer Email","default":"User Email","enabledtable":"","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"auserid","heading":"Advisor ID","default":"Advisor ID","enabledtable":"","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"suserid","heading":"Sponsor ID","default":"Sponsor ID","enabledtable":"","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"ufirstname","heading":"Designer","default":"Designer","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"pfirstname","heading":"Advisor","default":"Advisor","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"sfirstname","heading":"Sponsor","default":"Sponsor","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"enrolldate","heading":"Enrollment Date","default":"Enrollment Date","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"level","heading":"Level","default":"Level","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"careertitle","heading":"Career Title","default":"Career Title","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"currenttitle","heading":"Current Title","default":"Current Title","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"personalvolume","heading":"Personal Volume","default":"Personal Volume","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"psq","heading":"P.S.Q.","default":"P.S.Q.","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"teamvolume","heading":"Team Volume","default":"Team Volume","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"enterprisevolume","heading":"Enterprise Volume","default":"Enterprise Volume","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"level1mentors","heading":"Level 1 Mentors","default":"Level 1 Mentors","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"mastermentorlegs","heading":"Master Mentor Legs","default":"Master Mentor Legs","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"couturierlegs","heading":"Couturieur Legs","default":"Couturieur Legs","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"executivecouturierlegs","heading":"Executive Couturieur Legs","default":"Executive Couturieur Legs","enabledtable":"on","enabledcsv":"on"},';
	$jsonmenu .= '{"column":"mastercouturierlegs","heading":"Master Couturieur Legs","default":"Master Couturieur Legs","enabledtable":"on","enabledcsv":"on"}';

	$jsonmenu .= ']';

	return $jsonmenu;
}

////////////////////////////////
// Get the set values of menu //
////////////////////////////////
function AffilMenuGet($id, $jsonmenu)
{
	foreach ($jsonmenu as $item)
	{
		//echo "item->id=".$item->id." == ".$id."<br>";

		if ($item->id == $id)
		{
			//echo "$item->name=".$item->name."<br>";
			return $item;
		}
	}

	return "";
}

///////////////////////////////////
// Do actual display of the menu //
///////////////////////////////////
function AffilDispMenu($jsonmenu)
{
	$folderflag = true;

	// If json is missing new menu item, then add //
	foreach ($jsonmenu as $item)
	{	
		if ($item->type == "folder")
		{
			if ($folderflag == false)
			{
				$folderflag = true;
				echo "</ul></li>\r\n";
			}
			$folderflag = false;

			echo "<li><a><i class='".$item->class."'></i>".$item->name." <span class='fa fa-chevron-down'></span></a>\r\n";
            echo "<ul class='nav child_menu'>\r\n";
		}
		else if ($item->enabled != "on")
		{
			// Display nothing //
		}
		else if ($item->type == "link")
		{
			echo "\t<li><a href='".$item->webpage."'>".$item->name."</a></li>\r\n";
		}
	}

	echo "</ul></li>\r\n";
}

////////////////////////////////
// Handle lookup of tool name //
////////////////////////////////
function AffilDefaultTitles($toolid)
{
	switch ($toolid)
	{
	case 1:
		return "My Personal Volume";
	case 2:
		return "Personally Sponsored Qualified";
	case 3:
		return "Site Sales"; // Inventory Type ID Defined //
	case 4:
		return "My Team Volume";
	case 5:
		return "Level 1 (Rank Name)"; // Rank defined"; 
	case 6:
		return "Career Title";
	case 7:
		return "Enterprise Volume";
	case 8:
		return "(Rank Name) Legs"; // Rank and generation defined //
	case 9:
		return "Current Title";
	default:
		return "Unknown";

		// Rank Rules missed results //

		// Ringbomb tools //
	}
}

///////////////////////////////////////////////
// Show all affiliate tools in correct order //
///////////////////////////////////////////////
function AffilDisplayAll($batchesjson, $toolsjson)
{ 
	///////////////////////////////////////////////////////
	// Hit API once for data then use in multiple places //
	///////////////////////////////////////////////////////

	// mystatslvl1 //
	$_POST["userid"] = $_SESSION['user_id'];
	$_POST['search-batchid'] = $_SESSION['batchid'];
	$fields[] = "userid";
	$fields[] = "search";
	$pagvals = PagValidate("id", "desc");
	$jsonlvl1 = BuildAndPOST(AFFILIATE, "mystatslvl1", $fields, $pagvals);
	$psq = $jsonlvl1['userstatslvl1'][0]['psq'];
	//unset($_POST["userid"]);

	// mystats //
	$pagvals = PagValidate("id", "desc");
	$jsonmonth = BuildAndPOST(AFFILIATE, "mystats", $fields, $pagvals);
	//HandleResponse($jsonmonth, SUCCESS_NOTHING);
	$fullteamsales = $jsonlvl1['userstatslvl1'][0]['mywholesalesales']+$jsonmonth['userstats'][0]['teamwholesalesales'];
	$fullenterprisesales = $jsonlvl1['userstatslvl1'][0]['mywholesalesales']+$jsonmonth['userstats'][0]['groupwholesalesales'];

	//Pre($jsonlvl1);

	// mytitle //
	$_POST['batchid'] = $_SESSION['batchid'];
	$fields[] = "userid";
	$fields[] = "batchid";
	$pagvals = PagValidate("id", "desc");
	$jsonmytitle = BuildAndPOST(AFFILIATE, "mytitle", $fields, $pagvals);

	//HandleResponse($jsonmonth, SUCCESS_NOTHING);
	unset($_POST["userid"]);
	unset($_POST['batchid']);

	///////////////////////////////////////////////
	// Handle heading of each tool and tool data //
	///////////////////////////////////////////////
	$showcount = 0;
	$toolsdata = json_decode($toolsjson, JSON_UNESCAPED_SLASHES);
	foreach ($toolsdata as $tool)
	{
		if (($showcount == 0) || ($showcount == 3) || ($showcount == 6) || ($showcount == 9) || ($showcount == 12))
			echo "<div class='row tile_count'>\n";

		if ($tool['enabled'] == "checked")
		{
			//echo '<div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">';
			echo "<div class='col-md-4 col-sm-4 col-xs-6 tile_stats_count'>\n";
			$showcount++;
		}

		if (($tool['i'] == 1) && ($tool['enabled'] == "checked")) // My Personal Volume (WHOLESALE) //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n";
		    echo '<div class="count">$';
		    echo number_format($jsonlvl1['userstatslvl1'][0]['mywholesalesales'], 2);
		    echo "</div>\n";
		}
		else if (($tool['i'] == 2) && ($tool['enabled'] == "checked")) // Personally Sponsored Qualified //
		{
			unset($_POST["userid"]);
			unset($_POST['search-batchid']);

	        echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n";
	        echo "<div class='count'>".$psq."</div>\n";
		}
		else if (($tool['i'] == 3) && ($tool['enabled'] == "checked")) // Site Sales //
		{
			// Chalk Site Sales //
			$batch = GetBatchDates($_SESSION['batchid'], $batchesjson);
			$fields[] = "invtype";
			$fields[] = "startdate";
			$fields[] = "enddate";
			$_POST["invtype"] = 5; // Chalk Site Sales //
			$_POST["startdate"] = $batch['startdate'];
			$_POST["enddate"] = $batch['enddate'];
			$pagvals = PagValidate("id", "desc");
			$jsonchalksales = BuildAndPOST(AFFILIATE, "myreceiptsum", $fields, $pagvals);
			$receiptsum = $jsonchalksales["receiptsum"];

			unset($_POST["invtype"]);
			unset($_POST['startdate-batchid']);
			unset($_POST['enddate']);
			
	        echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n";
	        echo "<div class='count'>$".number_format($receiptsum, 2)."</div>";
		}
		else if (($tool['i'] == 4) && ($tool['enabled'] == "checked")) // My Team Volume (WHOLESALE) //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n";
	        echo "<div class='count'>$".number_format($fullteamsales)."</div>\n";
		}
		else if (($tool['i'] == 5) && ($tool['enabled'] == "checked")) // Level 1 (Rank) //
		{
			// For count of mentors personally sponsored //
			$_POST["userid"] = $_SESSION['user_id'];
			$_POST["batchid"] = $_SESSION['batchid'];
			$fields[] = "userid";
			$fields[] = "batchid";
			$pagvals = PagValidate("id", "desc");
			$jsonranklvl1 = BuildAndPOST(AFFILIATE, "mydownranksumlvl1", $fields, $pagvals);
			HandleResponse($jsonmonth, SUCCESS_NOTHING);
			//Pre($jsonranklvl1);
			// Add all above together //
			foreach ($jsonranklvl1['ranksumlvl1'] as $record)
			{
			    if ($record['rank'] >= 4)
			    {
			        $rank4lvl1total += $record['total'];
			        $rank4lvl1users .= $record['userdata'].", ";
			    }
			}

			// Add all above together //
			$rank4lvl1total = 0;
			foreach ($jsonranklvl1['ranksumlvl1'] as $record)
			{
			    if ($record['rank'] >= 4) // Rank # needs to be defined //
			    {
			        $rank4lvl1total += $record['total'];
			        $rank4lvl1users .= $record['userdata'].", ";
			    }
			}

			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; // Rank Name needs to be defined //
	        echo "<div class='count'>".$rank4lvl1total."</div>\n";
		}
		else if (($tool['i'] == 6) && ($tool['enabled'] == "checked")) // Career Title //
		{
	        echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; // Rank Name needs to be defined //
	        echo "<div class='count'><font size='5'>".$jsonmytitle['mytitle']['carrertitle']."</font></div>\n";
		}
		else if (($tool['i'] == 7) && ($tool['enabled'] == "checked")) // Enterprise Volume //
		{
	        echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; // Rank Name needs to be defined //
	        echo "<div class='count'>$".number_format($fullenterprisesales)."</div>\n";
		}
		else if (($tool['i'] == 8) && ($tool['enabled'] == "checked")) // (Rank) Legs // // Master Mentor Legs //
		{
			$_POST["userid"] = $_SESSION['user_id'];
			$_POST['search-batchid'] = $_SESSION['batchid'];
			$fields[] = "userid";

			// For count of master mentors in legs //
			$_POST["generation"] = "1";
			$fields[] = "generation";
			$pagvals = PagValidate("id", "desc");
			$jsonrankleg = BuildAndPOST(AFFILIATE, "mydownranksum", $fields, $pagvals);
			HandleResponse($jsonmonth, SUCCESS_NOTHING);
			$rank6legtotalgen1 = 0;
			foreach ($jsonrankleg['ranksum'] as $record)
			{
			    if (($record['rank'] == 6))
			    {
			        $rank6legtotalgen1 += $record['total'];
			        $rank6leggen1users .= $record['userdata'].", ";
			    }
			}
			$rank6leggen1users = rtrim($rank6leggen1users, ", ");
			unset($_POST["generation"]);

			$_POST["generation"] = "2";
			$pagvals = PagValidate("id", "desc");
			$jsonrankleg = BuildAndPOST(AFFILIATE, "mydownranksum", $fields, $pagvals);
			HandleResponse($jsonmonth, SUCCESS_NOTHING);
			$rank6legtotalgen2 = 0;
			foreach ($jsonrankleg['ranksum'] as $record)
			{
			    if (($record['rank'] == 6))
			    {
			        $rank6legtotalgen2 += $record['total'];
			        $rank6leggen2users .= $record['userdata'].", ";
			    }
			}
			$rank6leggen2users = rtrim($rank6leggen2users, ", ");
			unset($_POST["generation"]);

			$_POST["generation"] = "3";
			$pagvals = PagValidate("id", "desc");
			$jsonrankleg = BuildAndPOST(AFFILIATE, "mydownranksum", $fields, $pagvals);
			HandleResponse($jsonmonth, SUCCESS_NOTHING);
			$rank6legtotalgen3 = 0;
			foreach ($jsonrankleg['ranksum'] as $record)
			{
			    if (($record['rank'] == 6))
			    {
			        $rank6legtotalgen3 += $record['total'];
			        $rank6leggen3users .= $record['userdata'].", ";
			    }
			}
			$rank6leggen3users = rtrim($rank6leggen3users, ", ");
			unset($_POST["generation"]);

			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n";
	        echo "<div class='count'>\n";
	        echo "<table border='0' width=50%>
	        <tr><td align='center'><font size='2'><u>Gen 1</u><font></td><td align='center'><font size='2'><u>Gen 2</u></font></td><td align='center'><font size='2'><u>Gen 3</u></font></td></tr>";
	        
	        echo '<tr>';
	        echo '<td align="center"><div data-toggle="tooltip" data-placement="bottom" title="'.$rank6leggen1users.'">'.$rank6legtotalgen1.'</div></td>';
	        echo '<td align="center"><div data-toggle="tooltip" data-placement="bottom" title="'.$rank6leggen2users.'">'.$rank6legtotalgen2.'</div></td>';
	        echo '<td align="center"><div data-toggle="tooltip" data-placement="bottom" title="'.$rank6leggen3users.'">'.$rank6legtotalgen3.'</div></td>';
	        echo "</tr>";

	        echo "</table></div>";
		}
		else if (($tool['i'] == 9) && ($tool['enabled'] == "checked")) // Current Title //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; // Rank Name needs to be defined //
	        echo '<div class="count"><font size="5">'.$jsonmytitle['mytitle']['currenttitle'].'</font></div>';
		}
		else if (($tool['i'] == 10) && ($tool['enabled'] == "checked")) // Ringbomb Enterprise Volume //
		{
	        echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; 
	        echo "<div class='count'>$".number_format($jsonmonth['userstats'][0]['groupwholesalesales'])."</div>\n";
		}

		else if (($tool['i'] == 11) && ($tool['enabled'] == "checked")) // Personal Volume (RETAIL) //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; 
	        echo "<div class='count'>$".number_format($jsonlvl1['userstatslvl1'][0]['myretailsales'])."</div>\n";
		}
		else if (($tool['i'] == 12) && ($tool['enabled'] == "checked")) // Team Volume (RETAIL) //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; 
	        echo "<div class='count'>$".number_format($jsonmonth['userstats'][0]['groupretailsales'])."</div>\n";
		}
		else if (($tool['i'] == 13) && ($tool['enabled'] == "checked")) // Personal Item Count //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; 
	        echo "<div class='count'>".number_format($jsonmonth['userstats'][0]['itemcountwholesale'])."</div>\n";
		}
		else if (($tool['i'] == 14) && ($tool['enabled'] == "checked")) // Team Item Count //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; 
	        echo "<div class='count'>".number_format($jsonmonth['userstats'][0]['itemcountwholesaleev'])."</div>\n";
		}
		else if (($tool['i'] == 15) && ($tool['enabled'] == "checked")) // Affiliate Sales //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; 
	        echo "<div class='count'>$".number_format($jsonmonth['userstats'][0]['corpretailprice'])."</div>\n";
		}
		else if (($tool['i'] == 16) && ($tool['enabled'] == "checked")) // Team Volume of Wholesale purchases by level up to 5 levels //
		{
			echo "<span class='count_top'><i class='fa fa-user'></i> ".$tool['name']."</span>\n"; 
	        echo "<div class='count'>$".number_format($jsonmonth['userstats'][0]['teamwholesalesales'])."</div>\n";
		}

		if ($tool['enabled'] == "checked")
			echo "</div>\n";

		if (($showcount == 3) || ($showcount == 6) || ($showcount == 9) || ($showcount == 12))
			echo "</div>\n";
	}

	// What about all of ringbomb tools //
}


////////////////////////////
// Grab a value from json //
////////////////////////////
function GetJsonHeading($json, $column)
{
	foreach ($json as $record => $value)
    {
    	if ($value->column == $column)
    		return $value->heading;
    }
}

////////////////////////////
// Grab a value from json //
////////////////////////////
function GetJsonTable($json, $column)
{
	foreach ($json as $record => $value)
    {
    	if ($value->column == $column)
    		return $value->enabledtable;
    }
}

////////////////////////////
// Grab a value from json //
////////////////////////////
function GetJsonCSV($json, $column) 
{
	foreach ($json as $record => $value)
    {
    	if ($value->column == $column)
    		return $value->enabledcsv;
    }
}

function ShowColumnCSV($json, $column)
{
	if (GetJsonCSV($json, $column) == "on")
	{
		echo GetJsonHeading($json, $column).",";
	}
}

function ShowDataCSV($json, $column, $data)
{
	if (GetJsonCSV($json, $column) == "on")
	{
		echo $data;
	}
}

function ShowDataTable($json, $column, $data)
{
	if (GetJsonTable($json, $column) == "on")
	{
		return $data;
	}
}

?>
