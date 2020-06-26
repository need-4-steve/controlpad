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
$json = BuildAndPOST(MASTER, "settingsget", $fields, $pagvals);
//HandleResponse($json, SUCCESS_NOTHING);
if (($json['errors'][status] == "400") && ($json['errors']['detail'] == "There are no records"))
    $headings = json_decode(AffilDefaultDownlineJson());
else
    $headings = json_decode($json['settings'][0]['value']);

// Needs to be here before we put anything on the page //
if ($_POST['direction'] == "downloadcsv")
{
    ini_set('memory_limit', '512M');
    // Chalk - Fatal error:  Allowed memory size of 134217728 bytes exhausted

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

        // Build Sort Parameters //
        ShowColumnCSV($headings, "userid");
        ShowColumnCSV($headings, "ufirstname");
        ShowColumnCSV($headings, "ucell");
        ShowColumnCSV($headings, "uemail");
        ShowColumnCSV($headings, "auserid");
        ShowColumnCSV($headings, "pfirstname");
        ShowColumnCSV($headings, "suserid");
        ShowColumnCSV($headings, "sfirstname");
        ShowColumnCSV($headings, "enrolldate");
        ShowColumnCSV($headings, "level");
        ShowColumnCSV($headings, "careertitle");
        ShowColumnCSV($headings, "currenttitle");
        ShowColumnCSV($headings, "personalvolume");
        ShowColumnCSV($headings, "psq");
        ShowColumnCSV($headings, "teamvolume");
        ShowColumnCSV($headings, "enterprisevolume");
        ShowColumnCSV($headings, "level1mentors");
        ShowColumnCSV($headings, "mastermentorlegs");
        ShowColumnCSV($headings, "couturierlegs");
        ShowColumnCSV($headings, "executivecouturierlegs");
        ShowColumnCSV($headings, "mastercouturierlegs");
        echo "\n";

        // Build the .csv file on fly //
        $firstentry = true;
        foreach ($json['userstatsfull'] as $user)
        {
            $level1mentors = $user['lvl1-4']+$user['lvl1-5']+$user['lvl1-6']+$user['lvl1-7']+$user['lvl1-8']+$user['lvl1-9'];
            $mastermentors = $user['leg-6']+$user['leg-7']+$user['leg-8']+$user['leg-9'];
            $couturierlegs = $user['leg-7']+$user['leg-8']+$user['leg-9'];
            $executivecouturierlegs = $user['leg-8']+$user['leg-9'];

            $leveltmp = strstr($user['uuplineadvisor'], " ".$_SESSION['user_id']." ");
            $level = substr_count($leveltmp, "/")+1;

            ShowDataCSV($headings, 'userid', '"'.$user['userid'].'",');
            ShowDataCSV($headings, 'ufirstname', '"'.$user['ufirstname'].' '.$user['ulastname'].'",');
            ShowDataCSV($headings, 'ucell', '"'.$user['ucell'].'",');
            ShowDataCSV($headings, 'uemail', '"'.$user['uemail'].'",');
            ShowDataCSV($headings, 'auserid', '"'.$user['auserid'].'",');
            ShowDataCSV($headings, 'pfirstname', '"'.$user['afirstname'].' '.$user['alastname'].'",');
            ShowDataCSV($headings, 'suserid', '"'.$user['suserid'].'",');
            ShowDataCSV($headings, 'sfirstname', '"'.$user['sfirstname'].' '.$user['slastname'].'",');
            ShowDataCSV($headings, 'enrolldate', '"'.$user['usignupdate'].'",');
            ShowDataCSV($headings, 'level', '"'.$level.'",');
            ShowDataCSV($headings, 'careertitle', '"'.$user['carrertitle'].'",');
            ShowDataCSV($headings, 'currenttitle', '"'.$user['currenttitle'].'",');
            ShowDataCSV($headings, 'personalvolume', '"'.number_format($user['mywholesalesales'], 2).'",');
            ShowDataCSV($headings, 'psq', '"'.$user['psq'].'",');
            ShowDataCSV($headings, 'teamvolume', '"'.number_format($user['teamwholesalesales']+$user['mywholesalesales'], 2).'",');
            ShowDataCSV($headings, 'enterprisevolume', '"'.number_format($user['groupwholesalesales'], 2).'",');
            ShowDataCSV($headings, 'level1mentors', '"'.$level1mentors.'",');
            ShowDataCSV($headings, 'mastermentors', '"'.$mastermentors.'",');
            ShowDataCSV($headings, 'couturierlegs', '"'.$couturierlegs.'",');
            ShowDataCSV($headings, 'executivecouturierlegs', '"'.$executivecouturierlegs.'",');
            ShowDataCSV($headings, 'mastercouturierlegs', '"'.$user['leg-9'].'",');

            echo "\n";
        }
    }

    return;
}

include 'includes/inc.header.php';
include 'includes/inc.display.php';
include 'includes/inc.select.php';
include 'includes/inc.systems.php';

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

// For some reason serach level is off by one. Set it back to normal for display //
if (isset($_POST['search-level']))
    $_POST["search-level"] -= 1;

?>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
    <h2>My Downline Report</small></h2>
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
if (GetJsonTable($headings, "ufirstname") == "on")
    $size['ufirstname'] = 3;
if (GetJsonTable($headings, "pfirstname") == "on")
    $size['pfirstname'] = 3;
if (GetJsonTable($headings, "sfirstname") == "on")
    $size['sfirstname'] = 3;
if (GetJsonTable($headings, "enrolldate") == "on")
    $size['enrolldate'] = "NULL";
if (GetJsonTable($headings, "level") == "on")
    $size['level'] = "3";
if (GetJsonTable($headings, "careertitle") == "on")
    $size['careertitle'] = "NULL";
if (GetJsonTable($headings, "currenttitle") == "on")
    $size['currenttitle'] = "NULL";
if (GetJsonTable($headings, "personalvolume") == "on")
    $size['personalvolume'] = "NULL";
if (GetJsonTable($headings, "psq") == "on")
    $size['psq'] = "NULL";
if (GetJsonTable($headings, "teamvolume") == "on")
    $size['teamvolume'] = "NULL";
if (GetJsonTable($headings, "enterprisevolume") == "on")
    $size['enterprisevolume'] = "NULL";
if (GetJsonTable($headings, "level1mentors") == "on")
    $size['level1mentors'] = "NULL";
if (GetJsonTable($headings, "mastermentorlegs") == "on")
    $size['mastermentorlegs'] = "NULL";
if (GetJsonTable($headings, "couturierlegs") == "on")
    $size['couturierlegs'] = "NULL";
if (GetJsonTable($headings, "executivecouturierlegs") == "on")
    $size['executivecouturierlegs'] = "NULL";
if (GetJsonTable($headings, "mastercouturierlegs") == "on")
    $size['mastercouturierlegs'] = "NULL";

// Build Sort Parameters //
if (GetJsonTable($headings, "ufirstname") == "on")
    $sort['ufirstname'] = GetJsonHeading($headings, "ufirstname");
if (GetJsonTable($headings, "pfirstname") == "on")
    $sort['pfirstname'] = GetJsonHeading($headings, "pfirstname");
if (GetJsonTable($headings, "sfirstname") == "on")
    $sort['sfirstname'] = GetJsonHeading($headings, "sfirstname");
if (GetJsonTable($headings, "enrolldate") == "on")
    $sort['enrolldate'] = GetJsonHeading($headings, "enrolldate");
if (GetJsonTable($headings, "level") == "on")
    $sort['level'] = GetJsonHeading($headings, "level");
if (GetJsonTable($headings, "careertitle") == "on")
    $sort['careertitle'] = GetJsonHeading($headings, "careertitle");
if (GetJsonTable($headings, "currenttitle") == "on")
    $sort['currenttitle'] = GetJsonHeading($headings, "currenttitle");
if (GetJsonTable($headings, "personalvolume") == "on")
    $sort['personalvolume'] = GetJsonHeading($headings, "personalvolume");
if (GetJsonTable($headings, "psq") == "on")
    $sort['psq'] = GetJsonHeading($headings, "psq");
if (GetJsonTable($headings, "teamvolume") == "on")
    $sort['teamvolume'] = GetJsonHeading($headings, "teamvolume");
if (GetJsonTable($headings, "enterprisevolume") == "on")
    $sort['enterprisevolume'] = GetJsonHeading($headings, "enterprisevolume");
if (GetJsonTable($headings, "level1mentors") == "on")
    $sort['level1mentors'] = GetJsonHeading($headings, "level1mentors");
if (GetJsonTable($headings, "mastermentorlegs") == "on")
    $sort['mastermentorlegs'] = GetJsonHeading($headings, "mastermentorlegs");
if (GetJsonTable($headings, "couturierlegs") == "on")
    $sort['couturierlegs'] = GetJsonHeading($headings, "couturierlegs");
if (GetJsonTable($headings, "executivecouturierlegs") == "on")
    $sort['executivecouturierlegs'] = GetJsonHeading($headings, "executivecouturierlegs");
if (GetJsonTable($headings, "mastercouturierlegs") == "on")
    $sort['mastercouturierlegs'] = GetJsonHeading($headings, "mastercouturierlegs");

PagTop($sort, $size, $pagvals);

foreach ($users['userstatsfull'] as $user) {

    $level1mentors = $user['lvl1-4']+$user['lvl1-5']+$user['lvl1-6']+$user['lvl1-7']+$user['lvl1-8']+$user['lvl1-9'];
    $mastermentors = $user['leg-6']+$user['leg-7']+$user['leg-8']+$user['leg-9'];
    $couturierlegs = $user['leg-7']+$user['leg-8']+$user['leg-9'];
    $executivecouturierlegs = $user['leg-8']+$user['leg-9'];

    $leveltmp = strstr($user['uuplineadvisor'], " ".$_SESSION['user_id']." ");
    $level = substr_count($leveltmp, "/")+1;

    echo "<tr>";
    echo "<td></td>";

    // designer
    $string = "<td data-toggle='tooltip' data-html='true' title='<b>Phone:</b>".$user['ucell']."<br /><b>Email:</b>".$user['uemail']."<br /><b>UserID:</b>".$user['userid']."'>".$user['ufirstname'].' '.$user['ulastname'].' ('.$user['userid'].")</td>";
    echo ShowDataTable($headings, 'ufirstname', $string);
    // advisor
    $string = "<td data-toggle='tooltip' data-html='true' title='<b>Phone:</b>".$user['acell']."<br /><b>Email:</b>".$user['aemail']."'>".$user['afirstname'].' '.$user['alastname'].' ('.$user['auserid'].")</td>";
    echo ShowDataTable($headings, 'pfirstname', $string);
    // sponsor
    $string = "<td data-toggle='tooltip' data-html='true' title='<b>Phone:</b>".$user['scell']."<br /><b>Email:</b>".$user['semail']."'>".$user['sfirstname'].' '.$user['slastname']." (".$user['suserid'].")</td>";
    echo ShowDataTable($headings, 'sfirstname', $string);
    // Enroll Date //
    echo ShowDataTable($headings, 'enrolldate', "<td align=center>".DispDate($user['usignupdate'])."</td>");
    // level
    echo ShowDataTable($headings, 'level', "<td align=center>".$level."</td>");
    // // career title
    echo ShowDataTable($headings, 'careertitle', "<td>".$user['carrertitle']."</td>");
    //  current title
    echo ShowDataTable($headings, 'currenttitle', "<td>".$user['currenttitle']."</td>");
    // // personal volume
    echo ShowDataTable($headings, 'personalvolume', "<td>$".number_format($user['mywholesalesales'], 2)."</td>");
    // // personally sponsored qualified
    echo ShowDataTable($headings, 'psq', "<td>".$user['psq']."</td>");
    // // team volume
    echo ShowDataTable($headings, 'teamvolume', "<td>$".number_format($user['teamwholesalesales'] + $user['mywholesalesales'], 2)."</td>"); 
    // // enterprise volume
    echo ShowDataTable($headings, 'enterprisevolume', "<td>$".number_format($user['groupwholesalesales'], 2)."</td>"); 
    // // level 1 mentors
    echo ShowDataTable($headings, 'level1mentors', "<td>".$level1mentors."</td>"); 
    // // master mentors
    echo ShowDataTable($headings, 'mastermentorlegs', "<td>".$mastermentors."</td>"); 
    // // couturier legs
    echo ShowDataTable($headings, 'couturierlegs', "<td>".$couturierlegs."</td>"); 
    // // executive couturier legs
    echo ShowDataTable($headings, 'executivecouturierlegs', "<td>".$executivecouturierlegs."</td>");

    // master couturier legs
    echo ShowDataTable($headings, 'mastercouturierlegs', "<td>".$user['leg-9']."</td>");
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
