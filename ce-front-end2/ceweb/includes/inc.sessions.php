<?php

// login authentication needs to be implemented //

// Start a session //
session_start();

// Handle system selected //
// This need to be at the very top cause it's displayed at the top of page also //
if ($_GET['direction'] == "selected")
{
	$_SESSION['systemid'] = $_GET['systemid'];
}

?>