<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';
include 'includes/inc.convert.php';

// Build a list of input fields //
$fields[] = "systemname";
$fields[] = "stacktype";
$fields[] = "commtype";
$fields[] = "payouttype";
$fields[] = "payoutmonthday";
$fields[] = "payoutweekday"; 
$fields[] = "autoauthgrand";
$fields[] = "infinitycap";
$fields[] = "minpay";
$fields[] = "updatedurl";
$fields[] = "updatedusername";
$fields[] = "updatedpassword";
$fields[] = "signupbonus";
$fields[] = "teamgenmax";
$fields[] = "piggyid";
$fields[] = "psqlimit";
$fields[] = "compression";

// Handle checkbox correction // 
$_POST['autoauthgrand'] = ConvCheckbox($_POST['autoauthgrand']);

// We need to handle this in a funny way :(
$tmpsystemid = $_SESSION['systemid'];
$_SESSION['systemid'] = $_GET['systemid'];
$values = BuildEditPage(CLIENT, "system", $fields);
$_SESSION['systemid'] = $tmpsystemid;

echo '<div class="col-md-16 col-xs-12">';

// Handle Page Name Change //
if ($_GET['edit'] == "true")
	echo '	<h2>Edit System</h2>';
else
	echo '	<h2>Add System</h2>';

echo '	<div class="x_panel">';
echo '		<div class="x_content">';

// Hidden //
if ($_GET['edit'] == "true")
{
	echo '		<form class="form-horizontal form-label-left" method=POST action="?edit=true&systemid='.$values['id'].'">';
	//echo "<input type=hidden name='systemid' value='".$values['id']."'>";
	echo "<input type=hidden name='direction' value='edit'>";
}
else
{
	echo '		<form class="form-horizontal form-label-left" method=POST action="">';
	echo "<input type=hidden name='direction' value='add'>";
}

// System Name //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> System Name</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="System Name" name="systemname" value="'.$values['systemname'].'">';
echo '                </div>';
echo '          </div>';

// Stack Type //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Stack Type</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectStackType("stacktype", $values['stacktype']);
echo '				</div>';
echo '			</div>';

// Commission Plan Type //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Commission Type</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectCommType("commtype", $values['commtype']);
echo '				</div>';
echo '			</div>';

// Payout Type
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Payout Type</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectPayoutType("payouttype", $values['payouttype']);
echo '				</div>';
echo '			</div>';

// Month Day //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Month Day</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectMonthDay("payoutmonthday", $values['payoutmonthday']);
echo '				</div>';
echo '			</div>';

// Week Day //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Week Day</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectWeekDay("payoutweekday", $values['payoutweekday']);
echo '				</div>';
echo '			</div>';

// Min Pay //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Min Pay</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="$ Amount" name="minpay" value="'.$values['minpay'].'">';
echo '                </div>';
echo '          </div>';

// Infinity Cap //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12">Infinity Cap</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="$ Amount" name="infinitycap" value="'.$values['infinitycap'].'">';
echo '                </div>';
echo '          </div>';

// Signup Bonus //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12">Signup Bonus</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="$ Amount" name="signupbonus" value="'.number_format($values['signupbonus'], 2).'">';
echo '                </div>';
echo '          </div>';

// Auto Authorize //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Auto Authorize</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<div class="checkbox">';
echo '						<label>';
echo '							<input type="checkbox" class="form-control" placeholder="Auto Authorize" name="autoauthgrand">';
echo '						</label>';
echo '					</div>';
echo '				</div>';
echo '			</div>';

// Team Generation Max //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Team Gen Max</label>';
echo '					<div class="col-md-9 col-sm-9 col-xs-12">';			
echo '							<input type="text" class="form-control" placeholder="Team Gen Max" name="teamgenmax" value="'.$values['teamgenmax'].'">';
echo '					</div>';
echo '				</div>';
//echo '			</div>';

// Piggy Back System ID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Piggy ID</label>';
echo '					<div class="col-md-9 col-sm-9 col-xs-12">';			
echo '							<input type="text" class="form-control" placeholder="System ID" name="piggyid" value="'.$values['piggyid'].'">';
echo '					</div>';
echo '				</div>';
//echo '			</div>';

// PSQ limit //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> PSQ Limit</label>';
echo '					<div class="col-md-9 col-sm-9 col-xs-12">';			
echo '							<input type="text" class="form-control" placeholder="PSQ Limit" name="psqlimit" value="'.$values['psqlimit'].'">';
echo '					</div>';
echo '				</div>';
//echo '			</div>';

// Compression //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Compression</label>';
echo '					<div class="col-md-9 col-sm-9 col-xs-12">';			

echo '					<select name="compression">';
echo SelectBuildOption($default, "", "");
echo SelectBuildOption($default, "true", "true");
echo SelectBuildOption($default, "false", "false");
echo '					</select>';

echo '					</div>';
echo '				</div>';
//echo '			</div>';

/*
// REST url //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12">REST url</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="REST url" name="updatedurl" value="'.$values['updatedurl'].'">';
echo '                </div>';
echo '          </div>';

// REST username //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12">REST username</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="REST username" name="updatedusername" value="'.$values['updatedusername'].'">';
echo '                </div>';
echo '          </div>';

// REST password //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12">REST password</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="REST password" name="updatedpassword" value="'.$values['updatedpassword'].'">';
echo '                </div>';
echo '          </div>';
*/
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