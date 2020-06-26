<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.affiliate.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

if(!isset($_SESSION['batchid']))
  $_SESSION['batchid'] = DefaultBatch();

//////////////////////////////////
// Grab the affiliatehome value //
//////////////////////////////////
$fields[] = "varname";
$_POST['varname'] = "affiliatehome";
$retvaljson = BuildAndPOST(MASTER, "settingsget", $fields, $pagvals);
if (($retvaljson['errors'][status] == "400") && ($retvaljson['errors']['detail'] == "There are no records"))
{
    $jsonhome = AffilDefaultHomeJson();
}
else
{
    $jsonhome = json_decode($retvaljson['settings'][0]['value']);
    $jsonhome = json_encode($jsonhome->layout);
}

//echo '<div class="row tile_count">';
AffilDisplayAll($batchesjson, $jsonhome);
//echo '</div>';

?>
</tbody>
</table>
</div>

<?php
include 'includes/inc.footer.php';
?>
