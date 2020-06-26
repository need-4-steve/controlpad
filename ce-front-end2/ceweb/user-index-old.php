<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

if(!isset($_SESSION['batchid']))
  $_SESSION['batchid'] = DefaultBatch();

// userstatslvl1 //
$_POST["userid"] = $_SESSION['user_id'];
$_POST['search-batchid'] = $_SESSION['batchid'];
$fields[] = "userid";
$fields[] = "search";
$pagvals = PagValidate("id", "desc");
$jsonlvl1 = BuildAndPOST(AFFILIATE, "mystatslvl1", $fields, $pagvals);
$psq = $jsonlvl1['userstatslvl1'][0]['psq'];
HandleResponse($jsonlvl1, SUCCESS_NOTHING);
//unset($_POST['search']);
//unset($fields['search']);

// userstats //
//$_POST["userid"] = $_SESSION['user_id'];
//$fields[] = "userid";
$pagvals = PagValidate("id", "desc");
$jsonmonth = BuildAndPOST(AFFILIATE, "mystats", $fields, $pagvals);
HandleResponse($jsonmonth, SUCCESS_NOTHING);
$fullteamsales = $jsonlvl1['userstatslvl1'][0]['mywholesalesales']+$jsonmonth['userstats'][0]['teamwholesalesales'];
$fullenterprisesales = $jsonlvl1['userstatslvl1'][0]['mywholesalesales']+$jsonmonth['userstats'][0]['groupwholesalesales'];
//unset($fields['userid']);
//unset($_POST['userid']);
unset($_POST['search']);
unset($fields['search']);

// For count of mentors personally sponsored //
//$_POST["userid"] = $_SESSION['user_id'];
$_POST["batchid"] = $_SESSION['batchid'];
//$fields[] = "userid";
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
//Pre($jsonchalksales);

// My title //
//$_POST["userid"] = $_SESSION['user_id'];
//$_POST["batchid"] = 6; //$_SESSION['batchid'];
$fields[] = "userid";
$fields[] = "batchid";
$pagvals = PagValidate("id", "desc");
$jsonmytitle = BuildAndPOST(AFFILIATE, "mytitle", $fields, $pagvals);
HandleResponse($jsonmonth, SUCCESS_NOTHING);

?>
<!--
<div class="row tile_count">
    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Master Mentor Legs Gen 1</span>
        <div class="count"><?php echo $rank6legtotalgen1;?></div>
    </div>

    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Master Mentor Legs Gen 2</span>
        <div class="count"><?php echo $rank6legtotalgen2;?></div>
    </div>

    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Master Mentor Legs Gen 3</span>
        <div class="count"><?php echo $rank6legtotalgen3;?></div>
    </div>
</div>
-->
<div class="row tile_count">
    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> My Personal Volume</span>
        <div class="count">$<?php echo number_format($jsonlvl1['userstatslvl1'][0]['mywholesalesales'], 2);?></div>
    </div>

    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Personally Sponsored Qualified</span>
        <div class="count"><?php echo $psq;?></div>
    </div>

    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Chalk Site Sales</span>
        <div class="count">$<?php echo number_format(/*$receiptsum*/ 0, 2);?></div>
    </div>

</div>

<div class="row tile_count">
    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-group"></i> My Team Volume</span>
        <div class="count">$<?php echo number_format($fullteamsales);?></div>
    </div>

    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i>Level 1 Mentors</span>
        <div class="count"><?php echo $rank4lvl1total;?></div>
    </div>

    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i>Career Title</span>
        <div class="count"><?php echo str_replace(" ", " ", $jsonmytitle['mytitle']['carrertitle']);?></div>
    </div>

</div>
<div class="row tile_count">
    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Enterprise Volume</span>
        <div class="count">$<?php echo number_format($fullenterprisesales);?></div>
    </div>

    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Master Mentor Legs</span>
        <div class="count">
        <table border='0' width=50%>
        <tr><td align='center'><font size='2'><u>Gen 1</u><font></td><td align='center'><font size='2'><u>Gen 2</u></font></td><td align='center'><font size='2'><u>Gen 3</u></font></td></tr>
        
        <tr><td align='center'>
        <div data-toggle="tooltip" data-placement="bottom" title="<?php echo $rank6leggen1users;?>">
        <?php echo $rank6legtotalgen1;?>
        </div>
        </td>

        <td align='center'>
        <div data-toggle="tooltip" data-placement="bottom" title="<?php echo $rank6leggen2users;?>">
        <?php echo $rank6legtotalgen2;?>
        </div>
        </td>

        <td align='center'>
        <div data-toggle="tooltip" data-placement="bottom" title="<?php echo $rank6leggen3users;?>">
        <?php echo $rank6legtotalgen3;?>
        </div>
        </td></tr>
        </table></div>
    </div>

    <div class="col-md-4 col-sm-4 col-xs-6 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Current Title</span>
        <div class="count"><?php echo str_replace(" ", "<br>", $jsonmytitle['mytitle']['currenttitle']);?></div>
    </div>
</div>

<?php
/*
$fields[] = "systemid";
$fields[] = "userid";
$values["systemid"] = $_SESSION['system_id'];
$values["userid"] = $_SESSION['user_id'];
$headers = BuildHeader(AFFILIATE, "mytopclose", $fields, "", $values);
$json = PostURL($headers, "false");
if (HandleResponse($json, SUCCESS_NOTHING) == false)
{
    ShowError("There was a problem. Probably no records");
}
else
{
    echo '<div class="x_panel">';
    echo '<h2>Top 10 Almost Next Rank</h2>';
    echo '<table class="table">';
    echo '<thead>';
    echo '<tr><td align=center><b>Affiliate</b></td><td align=center><b>Email</b></td><td align=center><b>Current Rank</b></td><td align=center><b>Qualify Type</b></td><td align=center><b>To Next Rank</b></td></tr>';
    echo '</thead>';
    echo '<tbody>';

    //$json = json_decode($jsonstr, true);
    foreach ($json['rankmissed'] as $record)
    {
        echo "<tr><td align=center>".$record['firstname']." ".$record['lastname']." (".$record['userid'].")</td>";
        echo "<td align=center> ".$record['email']."</td>";
        echo "<td align=center>".($record['rank']-1)."</td>";

        echo "<td align=center>";
        echo DispQualifyType($record['qualifytype']);
        echo "</td>";

        echo "<td align=center>";

        // Display $ or not? //
        if (($record['qualifytype'] == 1) ||
            ($record['qualifytype'] == 2) ||
            ($record['qualifytype'] == 12) ||
            ($record['qualifytype'] == 13))
        {
            echo "$";
        }

        echo $record['diff']."</td>";
        echo "</tr>";
    }
}
*/
?>
</tbody>
</table>
</div>

<?php
include 'includes/inc.footer.php';
?>
