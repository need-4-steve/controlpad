<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.date.php';

SystemSelectedCheck();

// Build a list of input fields //
$fields[] = "id";
$fields[] = "userid";
$fields[] = "amount";
$fields[] = "bonusdate";

// Handle editpage logic //
$values = BuildEditPage(CLIENT, "bonus", $fields);

$values['bonusdate'] = FixDate($values['bonusdate']);

echo '<div class="col-md-16 col-xs-12">';
echo '	<h2>Add Bonus</h2>';
echo '	<div class="x_panel">';
echo '		<div class="x_content">';
echo '		<form class="form-horizontal form-label-left" method=POST action="">';

// Hidden //
if ($_GET['edit'] == "true")
{
	echo "<input type=hidden name='direction' value='edit'>";
	echo "<input type=hidden name='id' value='".$values['id']."'>";
}
else
	echo "<input type=hidden name='direction' value='add'>";

// User ID //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> User ID</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="User ID" name="userid" value="'.$values['userid'].'">';
echo '				</div>';
echo '			</div>';

// Amount //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Amount $</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="$ Amount" name="amount" value="'.$values['amount'].'">';
echo '				</div>';
echo '			</div>';

// Bonus Date //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Bonus Date</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
ChooseDate("bonusdate", $values['bonusdate']);
echo '				</div>';
echo '			</div>';

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
