<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';
include 'includes/inc.date.php';

SystemSelectedCheck();

// Default dates to current month //
$year = date("Y");
$month = date("m");
$endday = cal_days_in_month(CAL_GREGORIAN, $month, $year);
if (empty($_POST['startdate']))
    $_POST['startdate'] = $month."/1/".$year;
if (empty($_POST['enddate']))
    $_POST['enddate'] = $month."/".$endday."/".$year;

// Send API the seed option //
if ($_POST['direction'] == "run")
{
    $fields[] = "startdate";
    $fields[] = "enddate";
    $headers = BuildHeader(CLIENT, "predictgrandtotal", $fields, "", $_POST);
    $json = PostURL($headers, "false");
    if (HandleResponse($json, SUCCESS_NOTHING) == true)
    {
        $text = $text."<table>";
        $text = $text."<tr><td align=right><b>Receipts:</b></td><td>&nbsp;&nbsp;&nbsp;</td><td align=right>$".number_format($json["grandpayouts"]["receipts"])."</td></tr>";
        $text = $text."<tr><td align=right><b>Commissions:</b></td><td>&nbsp;&nbsp;&nbsp;</td><td align=right>$".number_format($json["grandpayouts"]["commissions"])."</td></tr>";
        $text = $text."<tr><td align=right><b>Achievement Bonuses:</b></td><td>&nbsp;&nbsp;&nbsp;</td><td align=right>$".number_format($json["grandpayouts"]["achvbonuses"])."</td></tr>";
        $text = $text."</table>";
        ShowBannerMessage("The prediction has completed", "green", "white");
        ShowBannerMessage($text, "white", "green");
        include 'includes/inc.footer.php';
        exit();
    }
}

// Display the form //
echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Run Real-Time Projection</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
echo "<input type=hidden name='direction' value='run'>";

// Start Date //
echo '          <div class="form-group">';
echo '              <label class="control-label col-md-3 col-sm-3 col-xs-12">Start Date</label>';
echo '              <div class="col-md-9 col-sm-9 col-xs-12">';
ChooseDate("startdate", $_POST['startdate']);
echo '              </div>';
echo '          </div>';

// End Date //
echo '          <div class="form-group">';
echo '              <label class="control-label col-md-3 col-sm-3 col-xs-12">End Date</label>';
echo '              <div class="col-md-9 col-sm-9 col-xs-12">';
ChooseDate("enddate", $_POST['enddate']);
echo '              </div>';
echo '          </div>';


// Submit Button //
echo '			<div class="ln_solid"></div>';
echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
echo '				<button type="submit" class="btn btn-success">Submit</button>';
echo '			</div>';

echo '		</div>';
echo '		</form>';
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>