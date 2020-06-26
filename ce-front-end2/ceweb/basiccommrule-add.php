<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.select.php';

SystemSelectedCheck();

$fields[] = "id";
$fields[] = "generation";
$fields[] = "qualifytype";
$fields[] = "startthreshold";
$fields[] = "endthreshold";	
$fields[] = "invtype";
$fields[] = "event";
$fields[] = "percent";
$fields[] = "modulus";
$fields[] = "paylimit";
$fields[] = "pvoverride";
$fields[] = "paytype";

// Handle editpage logic //
$values = BuildEditPage(CLIENT, "basiccommrule", $fields);

echo '<div class="col-md-16 col-xs-12">';

if ($_GET['edit'] == "true")
	echo '	<h2>Edit Basic Commission Rule</h2>';
else
	echo '	<h2>Add Basic Commission Rule</h2>';

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

// Start Generation //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Generation</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Generation" name="generation" value="'.$values['generation'].'">';
echo '				</div>';
echo '			</div>';


// Qualify Type //
echo '			<div class="form-group">';
echo '                <label class="control-label col-md-3 col-sm-3 col-xs-12">Qualify Type</label>';
echo '                <div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectQualifyType("qualifytype", $values['qualifytype']);
echo '                </div>';
echo '          </div>';

// Start Threshold //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Start Threshold</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Start Threshold" name="startthreshold" value="'.$values['startthreshold'].'">';
echo '				</div>';
echo '			</div>';

// End Threshold //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">End Threshold</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="End Threshold" name="endthreshold" value="'.$values['endthreshold'].'">';
echo '				</div>';
echo '			</div>';

// Select inventory type //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Inventory Type</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectInvType("invtype", $values['invtype']);
echo '				</div>';
echo '			</div>';

// Select event //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Event</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectEvent("event", $values['event']);
echo '				</div>';
echo '			</div>';

// Percent //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12"><font color=red>*</font> Percent</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Percent" name="percent" value="'.$values['percent'].'">';
echo '				</div>';
echo '			</div>';

// Modulus //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Modulus</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Modulus" name="modulus" value="'.$values['modulus'].'">';
echo '				</div>';
echo '			</div>';

// Pay Limit //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Pay Limit</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo '					<input type="text" class="form-control" placeholder="Pay Limit" name="paylimit" value="'.$values['paylimit'].'">';
echo '				</div>';
echo '			</div>';

// PV Override //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">PV Override</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
//echo '					<input type="text" class="form-control" placeholder="PV Override" name="pvoverride" value="'.$values['pvoverride'].'">';
echo SelectTrueFalse("pvoverride", $values['pvoverride']);
echo '				</div>';
echo '			</div>';

// Pay Type //
echo '			<div class="form-group">';
echo '				<label class="control-label col-md-3 col-sm-3 col-xs-12">Pay Type</label>';
echo '				<div class="col-md-9 col-sm-9 col-xs-12">';
echo SelectPayType("paytype", $values['paytype']);
//echo '					<input type="text" class="form-control" placeholder="Pay Type" name="paytype" value="'.$values['paytype'].'">';
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