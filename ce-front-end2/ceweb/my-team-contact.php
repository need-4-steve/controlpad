<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.pagination.php';

if(!isset($_SESSION['batchid']))
    $_SESSION['batchid'] = DefaultBatch();

// For some reason serach level is off by one //
if (isset($_POST['search-level']))
    $_POST["search-level"] += 1;

// Grab the downlineheadings value //
$fields[] = "varname";
$_POST['varname'] = "downlineheadings";
$json = BuildAndPOST(CLIENT, "settingsget", $fields, $pagvals);
//HandleResponse($json, SUCCESS_NOTHING);
if (($json['errors'][status] == "400") && ($json['errors']['detail'] == "There are no records"))
    $headings = json_decode(AffilDefaultDownlineJson());
else
    $headings = json_decode($json['settings'][0]['value']);

// Needs to be here before we put anything on the page //
if ($_POST['direction'] == "downloadcsv")
{
    // Grab the csv file //
    $_POST["batchid"] = $_SESSION['batchid'];
    $_POST["userid"] = $_SESSION['user_id'];
    $fields[] = "userid";
    $fields[] = "systemid";
    $fields[] = "batchid";
    $pagvals = PagValidate("id", "desc");
    $pagvals['limit'] = "999999999999"; // 1 less of a trillion //
    $json = BuildAndPOST(AFFILIATE, "mydownstatsfull", $fields, $pagvals);

    if ($json['count'] <= 0)
    {
        // Flag error below //
        $error = true;
    }
    else
    {
        $currentdate = date("m-d-Y");
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=downline-report-'.$currentdate.'.csv');
        header('Pragma: no-cache');

        ShowColumnCSV($headings, "ucell");
        ShowColumnCSV($headings, "uemail");

        // Build Sort Parameters //
        //echo "Designer,";
        //echo "Advisor,";
        //echo "Level,";
        //echo "Career Title,";
        ShowColumnCSV($headings, "ufirstname");
        ShowColumnCSV($headings, "pfirstname");
        ShowColumnCSV($headings, "level");
        ShowColumnCSV($headings, "careertitle");
        echo "Date Last Earned,";
        ShowColumnCSV($headings, "enrolldate");
        //echo "Phone,";
        //echo "Email,";
        ShowColumnCSV($headings, "ucell");
        ShowColumnCSV($headings, "uemail");
        echo "Address,";
        echo "City,";
        echo "State,";
        echo "Zip\n";

        // Address needed also //

        // Build the .csv file on fly //
        $firstentry = true;
        foreach ($json['userstatsfull'] as $user)
        {
            $leveltmp = strstr($user['uuplineadvisor'], " ".$_SESSION['user_id']." ");
            $level = substr_count($leveltmp, "/")+1;

            echo $user['ufirstname']." ".$user['ulastname']." (".$user['userid']."),";
            echo $user['afirstname']." ".$user['alastname']." (".$user['auserid']."),";
            echo $level.",";
            echo $user['carrertitle'].",";
            echo $user['udatelastearned'].","; // datelastearned not done yet //
            echo $user['usignupdate'].",";
            echo $user['ucell'].",";
            echo $user['uemail'].",";
            echo $user['address'].",";
            echo $user['city'].",";
            echo $user['state'].",";
            echo $user['zip']."\n";
        }
    }

    return;
}

include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.systems.php';
if(!isset($_SESSION['batchid'])){
  $_SESSION['batchid'] = DefaultBatch();
}
$_POST["batchid"] = $_SESSION['batchid'];
$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$fields[] = "systemid";
$fields[] = "batchid";
$pagvals = PagValidate("id", "desc");
$users = BuildAndPOST(AFFILIATE, "mydownstatsfull", $fields, $pagvals);
HandleResponse($users, SUCCESS_NOTHING);
unset($fields['userid']);
unset($fields['batchid']);
unset($fields['systemid']);

// For some reason serach level is off by one //
if (isset($_POST['search-level']))
    $_POST["search-level"] -= 1;

?>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
    <h2>Edit Users</small></h2>
    <div align=right>
        <form method="POST" action="">
        <input type="submit" value="Download csv">
        <input type="hidden" name="direction" value="downloadcsv"> 
        </form>
    </div>
<div class="clearfix"></div>
</div>
<div class="x_content">
    <table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build Search Parameters //
$size['ufirstname'] = 3;
$size['afirstname'] = 3;
$size['level'] = 3;
$size['careertitle'] = "NULL";
$size['datelastearned'] = "NULL";
$size['usignupdate'] = "NULL";
$size['ucell'] = "NULL";
$size['uemail'] = "NULL";

// Build Sort Parameters //
$sort['ufirstname'] = GetJsonHeading($headings, "ufirstname"); //"Designer";
$sort['afirstname'] = GetJsonHeading($headings, "pfirstname"); //"Advisor";
$sort['level'] = "Level";
$sort['careertitle'] = "Career Title";
$sort['datelastearned'] = "Date Last Earned";
$sort['usignupdate'] = "Enrollment Date";
$sort['ucell'] = "Phone";
$sort['uemail'] = "Email";

PagTop($sort, $size, $pagvals);

foreach ($users['userstatsfull'] as $user) {

    $leveltmp = strstr($user['uuplineadvisor'], " ".$_SESSION['user_id']." ");
    $level = substr_count($leveltmp, "/")+1;

    echo "<tr>";
    echo "<td></td>";

    // designer
    echo "<td align='center'>".$user['ufirstname'].' '.$user['ulastname']." (".$user['userid'].")</td>";
    // advisor
    echo "<td align='center'>".$user['afirstname'].' '.$user['alastname']." (".$user['auserid'].")</td>";
    // level
    echo "<td align='center'>".$level."</td>";
    // career Title
    echo "<td align='center'>".$user['carrertitle']."</td>";
    // date last earned
    echo "<td align='center'>".DispDate($user['udatelastearned'])."</td>";
    // enrollment date
    echo "<td align='center'>".DispDate($user['usignupdate'])."</td>";
    // Phone
    echo "<td align='center'>".$user['ucell']."</td>";
    // Email
    echo "<td align='center'>".$user['uemail']."</td>";
    echo "<td></td>";
    echo "</tr>";
}
echo "</table>";
PagBottom($pagvals, $users['count']);
?>
</div>
<?php
include 'includes/inc.footer.php';
?>
