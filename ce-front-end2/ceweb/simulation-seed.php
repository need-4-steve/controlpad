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

// Check/correct values before submit //
if ($_POST['copyseedoption'] == 1)
{   
    $_POST['seedtype'] = "";
    $_POST['usersmax'] = "";
    $_POST['enddate'] = "";
}
else if ($_POST['copyseedoption'] == 2)
{   
    $_POST['receiptsmax'] = "";
    $_POST['minprice'] = "";
    $_POST['maxprice'] = "";
}
else if ($_POST['copyseedoption'] == 3)
{   
    $_POST['seedtype'] = "";
    $_POST['usersmax'] = "";
    $_POST['receiptsmax'] = "";
    $_POST['minprice'] = "";
    $_POST['maxprice'] = "";
}
// Seed option 4 takes all values //

// Send API the seed option //
if ($_POST['direction'] == "seed")
{
    $fields[] = "copyseedoption";
    $fields[] = "seedtype";
    $fields[] = "usersmax";
    $fields[] = "receiptsmax";
    $fields[] = "minprice";
    $fields[] = "maxprice";
    $fields[] = "startdate";
    $fields[] = "enddate";
    $headers = BuildHeader(CLIENT, "copyseedsim", $fields, "", $_POST);
    $json = PostURL($headers, "false");
    if (HandleResponse($json, SUCCESS_NOTHING) == true)
    {
        $text = "The seeding of a simulation has completed";
        ShowBannerMessage($text, "green", "white");
        include 'includes/inc.footer.php';
        exit();
    }
}

// Display the form //
echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Seed Simulation</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
echo "<input type=hidden name='direction' value='seed'>";

?>
	<div class="form-group">
	    <label class="control-label col-md-3 col-sm-3 col-xs-12">Seed/Copy Data</label>
		<div class="col-md-9 col-sm-9 col-xs-12">
			<div class="form-group">
                <div class="col-md-9 col-sm-9 col-xs-12">
                  	<div class="radio">
                        <label>
                            <input type="radio" value="3" id="optionsRadios3" onclick="" name="copyseedoption"> Copy users and receipts from live
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" value="4" id="optionsRadios4" onclick="" name="copyseedoption"> Seed users and receipts
                        </label>
                    </div>
                    <div class="radio">
                       	<label>
                         	<input type="radio" value="1" id="optionsRadios1" onclick="" name="copyseedoption"> Copy users from live. Seed receipts
                       	</label>
                   	</div>
                  	<div class="radio">
                       	<label>
                          	<input type="radio" value="2" id="optionsRadios2" onclick="" name="copyseedoption"> Seed users. Copy receipts from live
                       	</label>
                   	</div>
                </div>
            </div>
        </div>
    </div>
<div class="ln_solid"></div>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Downline Type</label>
        <div class="col-md-9 col-sm-9 col-xs-12">
            <div class="form-group">
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <div class="radio">
                        <label>
                        <input type="radio" value="1" id="optionsRadios1" name="seedtype"> Wide
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                        <input type="radio" value="2" id="optionsRadios2" name="seedtype"> Deep
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Users Max</label>
        <div class="col-md-9 col-sm-9 col-xs-12">
            <input type="text" class="form-control" placeholder="Maximum limit seeding of users count" value="<?=$_POST['usersmax']?>" name="usersmax">
        </div>
    </div>
<div class="ln_solid"></div>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Receipts Max</label>
        <div class="col-md-9 col-sm-9 col-xs-12">
            <input type="text" class="form-control" placeholder="Maximum limit seeding of receipts count" value="<?=$_POST['receiptsmax']?>" name="receiptsmax">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Minimum Price</label>
        <div class="col-md-9 col-sm-9 col-xs-12">
            <input type="text" class="form-control" placeholder="Minimum receipt price on seeding" value="<?=$_POST['minprice']?>" name="minprice">
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12">Maximum Price</label>
        <div class="col-md-9 col-sm-9 col-xs-12">
            <input type="text" class="form-control" placeholder="Maximum receipt price on seeding" value="<?=$_POST['maxprice']?>" name="maxprice">
        </div>
    </div>
<div class="ln_solid"></div>

<?php
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
echo '		<div class="ln_solid"></div>';
echo '			<div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-5">';
echo '				<button type="submit" class="btn btn-success">Submit</button>';
echo '			</div>';

echo '		</div>';
echo '		</form>';
echo '	</div>';
echo '</div>';

include 'includes/inc.footer.php';

?>