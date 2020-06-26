<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';
include 'includes/inc.systems.php';


// userstats //
$_POST["userid"] = $_SESSION['user_id'];
//$_POST["batchid"] = 6; //$_SESSION['batchid'];
$pagvals = PagValidate("id", "desc");
$designers = BuildAndPOST(CLIENT, "newquery", $fields, $pagvals);
HandleResponse($design, SUCCESS_NOTHING);

// $newStats = [];
// foreach ($json['userstats'] as $userstat) {
//   $fields = [];
//   $_POST["userid"] = $userstat['userid'];
//   $_POST["systemid"] = $userstat["system_id"];
//   $fields[] = "userid";
//   $fields[] = "systemid";
//   $pagvals = PagValidate("id", "desc");
//   $user = BuildAndPOST(CLIENT, "getuser", $fields, $pagvals);
//   HandleResponse($user, SUCCESS_NOTHING);
//   $user = $user['user'][0];
//
//   $fields = [];
//   $_POST["userid"] = $user['parentid'];
//   $_POST["systemid"] = $userstat["system_id"];
//   $fields[] = "userid";
//   $fields[] = "systemid";
//   $pagvals = PagValidate("id", "desc");
//   $parent = BuildAndPOST(CLIENT, "getuser", $fields, $pagvals);
//   HandleResponse($parent, SUCCESS_NOTHING);
//   $parent = $parent['user'][0];
//
//
//   $fields = [];
//   $_POST["userid"] = $user['sponsorid'];
//   $_POST["systemid"] = $userstat["system_id"];
//   $fields[] = "userid";
//   $fields[] = "systemid";
//   $pagvals = PagValidate("id", "desc");
//   $sponsor = BuildAndPOST(CLIENT, "getuser", $fields, $pagvals);
//   HandleResponse($sponsor, SUCCESS_NOTHING);
//   $sponsor = $sponsor['user'][0];
//
//   $user['sponsor'] = $sponsor['firstname'] . ' ' . $sponsor['lastname'];
//   $user['advisor'] = $parent['firstname'] . ' ' . $parent['lastname'];
//
//   // My title
//   $_POST["userid"] = $userstat['userid'];
//   $_POST["batchid"] = $userstat['batchid'];
//   $fields[] = "userid";
//   $fields[] = "batchid";
//   $pagvals = PagValidate("id", "desc");
//   $jsonmytitle = BuildAndPOST(AFFILIATE, "mytitle", $fields, $pagvals);
//   HandleResponse($jsonmytitle, SUCCESS_NOTHING);
//   $user['mytitle'] = $jsonmytitle['mytitle'];
//
//   $userstat['user'] = $user;
//   array_push($newStats, $user);
// }



// Pre($newStats); die;

// $batchid = $json['userstats'][0]['batchid'];
// $userid = $json['userstats'][0]['userid'];
// Pre($userid); die;

// Handle query //
/*$_POST["userid"] = $_SESSION['user_id'];
$fields[] = "userid";
$pagvals = PagValidate("id", "desc");
$downline = BuildAndPOST(AFFILIATE, "mydownstats", $fields, $pagvals);
HandleResponse($downline, SUCCESS_NOTHING);
print_r($downline);*/


// Pre($user);
// exit;
?>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
    <h2>Edit Users</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
    <table id="datatable-checkbox" class="table table-striped table-bordered bulk_action">
<?php
// Build Search Parameters //
$size['userid'] = "NULL";
$size['parentid'] = "NULL";
$size['sponsorid'] = "NULL";
$size['level'] = "NULL";
$size['careertitle'] = "NULL";
$size['currenttitle'] = "NULL";
$size['personalvolume'] = "NULL";
$size['personalsponsoredqualified'] = "NULL";
$size['teamvolume'] = "NULL";
$size['enterprisevolume'] = "NULL";
$size['level1mentors'] = "NULL";
$size['mastermentorlegs'] = "NULL";
$size['courtieurlegs'] = "NULL";
$size['executivecourtieurlegs'] = "NULL";

// Build Sort Parameters //
$sort['userid'] = "Designer";
$sort['parentid'] = "Advisor";
$sort['sponsorid'] = "Sponsor";
$sort['level'] = "Level";
$sort['careertitle'] = "Career Title";
$sort['currenttitle'] = "Current Title";
$sort['personalvolume'] = "Personal Volume";
$sort['personalsponsoredqualified'] = "Personal Sponsored Qualified";
$sort['teamvolume'] = "Team Volume";
$sort['enterprisevolume'] = "Enterprise Volume";
$sort['level1mentors'] = "Level 1 Mentors";
$sort['mastermentorlegs'] = "Master Mentor Legs";
$sort['courtieurlegs'] = "Courtieur Legs";
$sort['executivecourtieurlegs'] = "Executive Courtieur Legs";

PagTop($sort, $size, $pagvals);

// // Loop through each rule //
// foreach ($json['userstats'] as $stats)
// {
// 	echo "<tr>";
// 	echo "<td></td>";
// 	echo "<td align=center>".DispTimestamp($stats['createdat'])."</td>";
// 	//echo "<td align=center>".$ledger['updatedat']."</td>";
// }

foreach ($designers as $designer){
  echo "<tr>";
  echo "<td align=center "
    ."data-toggle='tooltip' data-html='true'"
    ."title='<b>Date Last Earned: </b>".$designer['datelastearned']."<br/>"
    ."<b>Enrolment Date: </b>".$designer['enrolmentdate']."<br/>"
    ."<b>Phone: </b>".$designer['cell']."<br/>"
    ."<b>Email: </b>".$designer['email']."'>".$designer['firstname'].' '.$designer['lastname']."</td>";
  echo "<td align=center "
    ."data-toggle='tooltip' data-html='true' "
    ."title='<b>Phone: </b>".$designer['advisor']['cell']."<br/>"
    ."<b>Email: </b>".$designer['advidor']['email']."<br/>"
    ."'>".$designer['advisor']['firstname'].' '.$designer['advisor']['lastname']."</td>";
  echo "<td align=center "
    ."data-toggle='tooltip' data-html='true' "
    ."title='<b>Phone: </b>".$designer['sponsor']['cell']."<br/>"
    ."<b>Email: </b>".$designer['sponsor']['email']."</br>"
    ."'>".$designer['sponsor']['firstname'].' '.$designer['sponsor']['lastname']."</td>";
  echo "<td align=center>".$designer['level']."</td>";
  echo "<td align=center>".$designer['careertitle']."</td>";
  echo "<td align=center>".$designer['currenttitle']."</td>";
  echo "<td align=center>".$designer['personalvolume']."</td>";
  echo "<td align=center>".$designer['psq']."</td>";
  echo "<td align=center>".$designer['teamvolume']."</td>";
  echo "<td align=center>".$designer['enterprisevolume']."</td>";
  echo "<td align=center>".$designer['level1mentors']."</td>";
  echo "<td align=center>".$designer['mastermentorlegs']."</td>";
  echo "<td align=center>".$designer['courtierlegs']."</td>";
  echo "<td align=center>".$designer['mastercourtierlegs']."</td>";

}
echo "</table>";
PagBottom($pagvals, $json['count']);
?>


<?php
include 'includes/inc.footer.php';
?>
