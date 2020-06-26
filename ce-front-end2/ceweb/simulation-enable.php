<?php

include 'includes/inc.ce-comm.php';
include 'includes/inc.header.php';
include 'includes/inc.pagination.php';
include 'includes/inc.display.php';

SystemSelectedCheck();

?>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
<div class="x_panel">
<div class="x_title">
	<h2>Simulations</small></h2>
<div class="clearfix"></div>
</div>
<div class="x_content">
	You are currently in simulation mode. The menu was intentially changed while running simulations to help prevent you from editing live data.<br>
	<br>
	The <a href='simulation-seed.php'><b>Seed Simulation</b></a> menu link will allow you to seed and/or copy your users and receipts data. This will be needed for running a simulation.<br>
	<br>
	<br>

</div>
<?php

include 'includes/inc.footer.php';

?>