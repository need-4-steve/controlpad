<?php

$g_jwturl = GetJwtURL(); 

// Handle not logged in //
if (($_SESSION['sysuserloggedin'] != "true") && ($_SESSION['userloggedin'] != "true"))
{
    if ($_SESSION['sysuserloggedin'] != "true")
        header("Location: login.php");

    if (empty($g_jwturl))
        header("Location: user-login.php"); // Normal Login //
    else
        header("Location: ".$g_jwturl);

    exit();
}

// Handle a system selection //
if ($_POST['direction'] == "selected")
{
    $_SESSION['systemid'] = $_POST['systemid'];
    $_SESSION['systemname'] = $_POST['systemname'];
    $_SESSION['commtype'] = $_POST['commtype']; // We change form display if binary is selected //
}

// Allow system selection up dropdown up top //
if ($_GET['direction'] == "selectsystem")
{
    $_SESSION['systemid'] = $_GET['systemid'];
} elseif($_GET['direction'] == "selectbatch")
{
    $_SESSION['batchid'] = $_GET['batchid'];
}

// Handle simulation Selection //
if (!empty($_GET['simulations']))
{
    $_SESSION['simulations'] = $_GET['simulations'];
}

// Handle Account Override //
if (($_SESSION['sysuserloggedin'] == "true") && ($_POST['direction'] == "override"))
{
    $_SESSION['override'] = "true";
    $_SESSION['userloggedin'] = "true";
    $_SESSION['sysuserloggedin'] = "false";

    $_SESSION['user_id'] = $_POST['userid'];
    $_SESSION['useremail'] = $_POST['email'];
    $_SESSION['userpass'] = "PLACEHOLDER";
}

// Handle Revert Back //
if (($_SESSION['override'] == "true") && ($_POST['direction'] == "revert"))
{
    //echo "direction=".$_POST['direction']."<br>";
    unset($_SESSION['override']);
    unset($_SESSION['user_id']);
    unset($_SESSION['useremail']);
    unset($_SESSION['userpass']);

    $_SESSION['userloggedin'] = "false";
    $_SESSION['sysuserloggedin'] = "true";
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Commission Engine | </title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">

    <!--<link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">-->

    <!-- Date Range Picker -->
    <link href="../vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">

<?php
if ($_SESSION['simulations'] == "true")
    echo '   <link href="build/css/custom.min2.css" rel="stylesheet">';
else
    echo '   <link href="build/css/custom.min.css" rel="stylesheet">';
?>

<!-- Style for my-downline.php -->
<style>
.node circle {
  fill: #999;
}

.node text {
  font: 10px sans-serif;
  cursor: pointer;
}

.node--internal circle {
  fill: #555;
}

.node--internal text {
  text-shadow: 0 1px 0 #fff, 0 -1px 0 #fff, 1px 0 0 #fff, -1px 0 0 #fff;
}

.link {
  fill: none;
  stroke: #555;
  stroke-opacity: 0.4;
  stroke-width: 1.5px;
}
</style>

<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-108240165-6"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag(‘js’, new Date());
gtag(‘config’, ‘UA-108240165-6’);
</script>

  </head>

  <link href="build/css/cp-custom.css" rel="stylesheet">

  <body class="nav-md">
    <div class="container body">
      <div class="main_container">
        <div class="col-md-3 left_col">
          <div class="left_col scroll-view">
            <div class="navbar nav_title" style="border: 0;">

<?php
if ($_SESSION['sysuserloggedin'] == "true")
    echo '        <a href="index.php" class="site_title"><i class="fa fa-cogs"></i> <span><font size=3>Commission Engine</font></span></a>';
else
    echo '        <a href="user-index.php" class="site_title"><i class="fa fa-cogs"></i> <span><font size=3>Commission Engine</font></span></a>';
?>
            </div>

            <div class="clearfix"></div>

            <!-- menu profile quick info -->

            <!--
              <div class="profile clearfix">
              <div class="profile_pic">
                <img src="images/img.jpg" alt="..." class="img-circle profile_img">
              </div>
              <div class="profile_info">
                <span>Welcome,</span>
                <h2>John Doe</h2>
              </div>
              <div class="clearfix"></div>
              </div>
            -->
            <!-- /menu profile quick info -->

            <br />

            <!-- sidebar menu -->
            <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
<!--
              <div class="menu_section">
                <h3>User</h3>
                <ul class="nav side-menu">
                  <li><a><i class="fa fa-user"></i> My Tools <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="my-ledgerbalance.php">My Downline</a></li>
                      <li><a href="my-ledgerbalance.php">My Upline</a></li>
                      <li><a href="my-ledgerbalance.php">My Ledger Balance</a></li>
                      <li><a href="my-commission.php">My Commissions</a></li>
                      <li><a href="my-commission.php">My Receipt Breakdown</a></li>
                      <li><a href="my-commission.php">My Achievement Bonuses</a></li>
                      <li><a href="user-add.php">My Statistics</a></li>
                      <li><a href="user-add.php">My Projections</a></li>
                    </ul>
                  </li>
                </ul>
              </div>
-->
<?php
if ($_SESSION['sysuserloggedin'] == "true")
{
?>
              <div class="menu_section">
                <h3>Inital Setup</h3>
                <ul class="nav side-menu">

                  <li><a><i class="fa fa-sliders"></i> Settings <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="settings-timezone.php">Timezone</a></li>
                      <li><a href="settings-default-system.php">Default System</a></li>
                      <li><a href="settings-affil-menu.php">Affiliate Menu</a></li>
                      <li><a href="settings-affil-home.php">Affiliate Home</a></li>
                      <li><a href="settings-affil-downline-headings.php">Affiliate Downline Headings</a></li>
                      <li><a href="settings-jwt.php">JWT Single-Sign-on</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-sitemap"></i> Systems <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="system-add.php">Add System</a></li>
                      <li><a href="systems-edit.php">Edit Systems</a></li>
                      <li><a href="systems-select.php">Select System</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-arrows"></i> Rank Rules <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="rankrule-add.php">Add Rank Rule</a></li>
                      <li><a href="rankrule-edit.php">Edit Rank Rules</a></li>
                    </ul>
                  </li>
                  <li><a><i class="fa fa-key"></i> Commission Rules <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="commrule-add.php">Add Commission Rule</a></li>
                      <li><a href="commrule-edit.php">Edit Commission Rules</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-key"></i> Basic Comm Rules <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="basiccommrule-add.php">Add Basic Comm Rule</a></li>
                      <li><a href="basiccommrule-edit.php">Edit Basic Comm Rules</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-key"></i> RankGenBonus Rule <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="rankgenbonus-add.php">Add RankGenBonus Rule</a></li>
                      <li><a href="rankgenbonus-edit.php">Edit RankGenBonus Rule</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-puzzle-piece"></i> Simulations <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
<?php
if ($_SESSION['simulations'] == "true")
{
    echo '              <li><a href="simulation-enable.php?simulations=false">Disable Simulation</a></li>';
    echo '              <li><a href="simulation-seed.php">Seed Simulation</a></li>';
    echo '              <li><a href="simulation-run.php">Run Simulation</a></li>';
}
else
{
//    echo '              <li><a href="projection-run.php">Run Real-Time Projections</a></li>';
    echo '              <li><a href="simulation-enable.php?simulations=true">Enable Simulation</a></li>';
}
?>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-bar-chart-o"></i> Audit Reports <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="batch-select.php">Batch Select</a></li>
                      <li><a href="report-audit-generation.php">Generation Audit Report</a></li>
                      <li><a href="report-audit-rank.php">Rank Audit Report</a></li>
                      <li><a href="report-audit-users.php">Users Audit Report</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-plug"></i> API <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="apikey-reissue.php">Reissue API Key</a></li>
<?php
/*                    <li><a href="apikey-add.php">Add API Key</a></li>
                      <li><a href="apikeys-manage.php">Manage API Keys</a></li>
*/
?>
                      <li><a href="apikeys-remote-help.php">Remote Integration Help</a></li>

                    </ul>
                  </li>

                </ul>
              </div>

              <div class="menu_section">
                <h3>Manage</h3>
                <ul class="nav side-menu">
                 <li><a><i class="fa fa-group"></i> Users <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="user-add.php">Add User</a></li>
                      <li><a href="user-edit.php">Edit Users</a></li>
                      <li><a href="user-bulk-disable.php">Bulk Disable</a></li>
                      <!--<li><a href="users-add-bulk.php">Bulk Add Users</a></li>-->
                    </ul>
                  </li>
                  <li><a><i class="fa fa-map-o"></i> Receipts <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="receipt-add.php">Add Receipt</a></li>
                      <li><a href="receipt-edit.php">Edit Receipts</a></li>
                      <li><a href="receipt-bulk-commissionable.php">Bulk Commissionable</a></li>
                      <li><a href="receipt-order-report.php">Receipt Orders Report</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-cubes"></i> Pools <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="pool-add.php">Add Pool</a></li>
                      <li><a href="pool-edit.php">Edit Pool</a></li>
                      <li><a href="pool-select.php">Select Pool</a></li>
                      <li><a href="poolrule-add.php">Add Pool Rule</a></li>
                      <li><a href="poolrule-edit.php">Edit Pool Rules</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-credit-card"></i> Bonus <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="bonus-add.php">Add Bonus</a></li>
                      <li><a href="bonus-edit.php">Edit Bonuses</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-diamond"></i> Commissions <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="commissions-run.php">Run Commissions</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-folder"></i> Records <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="report-batch.php">View Batches</a></li>
                      <li><a href="report-ranks.php">View Ranks</a></li>
                      <li><a href="rankrules-missed-view.php">View Rank Rules Missed</a></li>
                      <li><a href="report-achvbonus.php">View Achievement Bonuses</a></li>
                      <li><a href="report-rankgenbonus.php">View Rank Gen Bonuses</a></li>
                      <li><a href="breakdown-view.php">View Receipt Breakdown</a></li>
                      <li><a href="report-commissions.php">View Commissions</a></li>
                      <li><a href="ledger-add.php">Add Ledger</a></li>
                      <li><a href="report-ledger.php">View Ledger</a></li>
                      <li><a href="report-userstats.php">View User Stats</a></li>
                      <li><a href="report-userstats-lvl1.php">View User Stats Lvl 1</a></li>
                      <!--<li><a href="report-user-stats-total.php">View User Stats Total</a></li>-->
                    </ul>
                  </li>

                  <li><a><i class="fa fa-eye"></i> Fraud Management <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="report-ledger-balance.php">View Ledger Balance</a></li>
                      <li><a href="report-receipt-sumation.php">View Receipt Sumation</a></li>
                      <li><a href="payments-authorize.php">Authorize Payments</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-bank"></i>Bank Accounts <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="bankaccount-edit.php">Edit Bank Accounts</a></li>
                      <!--<li><a href="bankaccount-validate.php">Validate Bank Accounts</a></li>-->
                    </ul>
                  </li>

                  <li><a><i class="fa fa-money"></i> Payments <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="payments-process.php">Process Payments</a></li>
                    </ul>
                  </li>

                </ul>
              </div>
<?php
}
// Display user tools //
else if ($_SESSION['userloggedin'] == "true")
{
?>
              <div class="menu_section">
              <!--  <h3>My Tools</h3> -->
                <ul class="nav side-menu">
              <!--    <li><a><i class="fa fa-wrench"></i>My Tools <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="my-projections.php">My Real-Time Projections</a></li>
                    </ul>
                  </li>
              -->

<?php
                  $fields[] = "varname";
                  $_POST['varname'] = "affiliatemenu";
                  $json = BuildAndPOST(MASTER, "settingsget", $fields, $pagvals);
                  //Pre($json);
                  if (($json['errors'][status] == "400") && ($json['errors']['detail'] == "There are no records"))
                      $jsonmenu = json_decode(AffilDefaultMenuJson());
                  else
                      $jsonmenu = json_decode($json['settings'][0]['value']);

                  AffilDispMenu($jsonmenu);
                  /*
?>
                  <li><a><i class="fa fa-folder"></i>My Records <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="my-ledger.php?search=search-ledgertype=8">My Payments</a></li>
                      <li><a href="my-commissions.php">My Commissions</a></li>
                      <li><a href="my-achvbonuses.php">My Achievement Bonuses</a></li>
                      <li><a href="my-bonuses.php">My Bonuses</a></li>
                      <li><a href="my-rankgenbonuses.php">My Couturier Bonuses</a></li>
                      <li><a href="my-ledger.php">My Ledger</a></li>
                      <li><a href="my-breakdown.php">My Breakdown</a></li>
                      <!--<li><a href="my-rankrules-missed.php">My RankRules Missed</a></li>-->
                      <li><a href="my-stats.php">My Team Volume</a></li>
                      <li><a href="my-stats-lvl1.php">My Personal Volume</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-group"></i>Downline Records <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <!--<li><a href="downline-rankrules-missed.php">RankRules Missed</a></li>
                      <li><a href="my-downline-stats.php">Downline Team Volume</a></li>
                      <li><a href="my-downline-stats-lvl1.php">Downline Personal Volume</a></li>
                      <li><a href="my-sponsored-stats.php">Level 1 Team Volume</a></li>
                      <li><a href="my-sponsored-stats-lvl1.php">Level 1 Personal Volume</a></li>-->
                      <li><a href="mydownlinereport.php">My Downline Report</a></li>
                      <li><a href="my-team-contact.php">My Team Contact</a></li>
                    </ul>
                  </li>

                  <li><a><i class="fa fa-users"></i>Hierarchy <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu">
                      <li><a href="my-downline.php">My Downline</a></li>
                      <!--<li><a href="my-upline.php">My Upline</a></li>-->
                    </ul>
                  </li>
*/
                  ?>
                </ul>
              </div>
<?php

}
?>

            </div>
            <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
            <!--
            <div class="sidebar-footer hidden-small">
              <a data-toggle="tooltip" data-placement="top" title="Settings">
                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="FullScreen">
                <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Lock">
                <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
              </a>
              <a data-toggle="tooltip" data-placement="top" title="Logout" href="login.html">
                <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
              </a>
            </div>
            -->
            <!-- /menu footer buttons -->
          </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
          <div class="nav_menu">
            <nav>
              <div class="nav toggle">
                <table border='0' width='600'>
                <tr>
                  <td width='1%'><a id="menu_toggle"><i class="fa fa-bars"></i></a></td>
<?php
if ($_SESSION['override'] == "true")
{
                  echo "<td>&nbsp;</td>";
                  echo "<td align=center width='1%'>";
                  echo "<form method=POST action='index.php'>";
                  echo "<input type=submit value='Revert Back'>";
                  echo "<input type=hidden name='direction' value='revert'>";
                  echo "</form>";
                  echo "</td>";
}
else
{
                  //echo "<td>&nbsp;</td>";
}
                  // Handle System Selection //
                  echo "<td> &nbsp; <b>System:</b>&nbsp;";
                  //$fields;
                  $pagvals['limit'] = "10";
                  $pagvals['offset'] = "0";
                  $pagvals['orderby'] = "id";
                  $pagvals['orderdir'] = "asc";
                  $pagvals['qstring'] = "limit=10&orderby=id";

                  if ($_SESSION['sysuserloggedin'] == "true")
                      $headersystemresult = BuildAndPOST(CLIENT, "querysystem", $fields, $pagvals);
                  else
                      $headersystemresult = BuildAndPOST(AFFILIATE, "querysystem", $fields, $pagvals);

                  $url = $g_protocol.$_SERVER["SERVER_NAME"].strtok($_SERVER["REQUEST_URI"], '?');

                  echo "<select name='systemid' onChange='window.location = \"?direction=selectsystem&systemid=\"+this.value;alert(test)'>";
                  //echo "<select name='systemid' onChange='var test = \"".$url."\"+\"?systemid=\"+this.value;alert(test)'>";
                  //echo "<select name='systemid' onChange='var test = window.location+\"?systemid=\"+this.value;alert(test)'>";
                  foreach ($headersystemresult['system'] as $systemrecord)
                  {
                      if ($_SESSION['systemid'] == $systemrecord['id'])
                          echo "<option selected value='".$systemrecord['id']."'>".$systemrecord['systemname'];
                      else
                          echo "<option value='".$systemrecord['id']."'>".$systemrecord['systemname'];
                  }
                  echo "</select>";
                  echo "</td>";

                  //echo "<td><h2><a href='systems-select.php'>System:&nbsp;".$_SESSION['systemname']."&nbsp;(".$_SESSION['systemid'].")</a></h2></td>";

                  // Handle BatchID Selection //


                  //echo "<td> &nbsp; <b>BatchID:</b>&nbsp;";
                  //$batchesjson = BuildAndPOST(AFFILIATE, "querybatches", $fields, $pagvals);

                  echo "<td> &nbsp; <b>Commission Period:</b>&nbsp;";
                  unset($pagvals);
                  $pagvals['orderby'] = "id";
                  $pagvals['orderdir'] = "desc";
                  $batchesjson = BuildAndPOST(AFFILIATE, "querybatches", $fields, $pagvals);
                  HandleResponse($batchesjson, SUCCESS_NOTHING);

                  echo "<select name='systemid' onChange='window.location = \"?direction=selectbatch&batchid=\"+this.value'>";
                  foreach ($batchesjson['batches'] as $batch)
                  {
                    $time = strtotime($batch['startdate']);
                    $month = date("F", $time);
                    $year = date("Y", $time);
                    if (isset($_SESSION['batchid']) && $_SESSION['batchid'] == $batch['id'])
                    {
                      echo "<option selected value='".$batch['id']."'>".$month." ".$year."</option>";
                    } else {
                      echo "<option value='".$batch['id']."'>".$month." ".$year."</option>";
                    }
                  }

                  echo "</select>";
                  echo "</td>";


?>

                </tr>
                </table>
              </div>

              <ul class="nav navbar-nav navbar-right">
                <li class="">

                  <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <!--<img src="images/img.jpg" alt="">-->Settings
                    <span class=" fa fa-angle-down"></span>
                  </a>
                  <ul class="dropdown-menu dropdown-usermenu pull-right">
                    <li><a href="javascript:;"> Profile</a></li>
                    <li>
                      <a href="javascript:;">
                        <span class="badge bg-red pull-right">50%</span>
                        <span>Settings</span>
                      </a>
                    </li>
                    <li><a href="javascript:;">Help</a></li>
<?php
if ($_SESSION['userloggedin'] == "true")
  echo '                  <li><a href="user-login.php?siteid='.$_SESSION['systemid'].'&logout=true"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>';
else
  echo '                  <li><a href="login.php?logout=true"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>';
?>
                  </ul>
                </li>
<!--
                <li role="presentation" class="dropdown">
                  <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-envelope-o"></i>
                    <span class="badge bg-green">6</span>
                  </a>
                  <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <a>
                        <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                        <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                        <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                      </a>
                    </li>
                    <li>
                      <div class="text-center">
                        <a>
                          <strong>See All Alerts</strong>
                          <i class="fa fa-angle-right"></i>
                        </a>
                      </div>
                    </li>
                  </ul>
                  -->
                </li>
              </ul>
              <br>
              <br>
            </nav>
          </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
<?php
echo '<div class="right_col" role="main">';
if ((basename($_SERVER['PHP_SELF']) == "user-index.php") ||
    (basename($_SERVER['PHP_SELF']) == "index.php"))
    echo '<div class="row tile_count">';
else
{
    echo '<div class="">';
    echo '<div class="page-title">';
    echo '<div class="title_left">';
}

?>
