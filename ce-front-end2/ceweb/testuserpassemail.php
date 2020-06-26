<?php
/*
	include "includes/inc.ce-comm.php";
	include "includes/inc.email.php";

	$hash = "this-is-a-test-hash";

	// Send email //
    $from_name = "CommissionEngine";
    $from_email = "no-reply@controlpad.com";
    $to_email = "wanderson@controlpad.com"; //$_POST['email'];
    $subject = "Reset Password";

    // Allow just in case for debugging locally //
    if (!empty($_SERVER['HTTPS']))
        $link = "https://";
    else
        $link = "http://";
    
    //$link = $link.$_SERVER['SERVER_NAME'].str_replace("user-passreset.php", "", $_SERVER['REQUEST_URI'])."user-passreset-finish.php?siteid=".$_POST['siteid']."&hash=".$hash;
    //$link = $link.$_SERVER['SERVER_NAME'].str_replace("user-passreset.php", "user-passreset-finish.php", $_SERVER['REQUEST_URI'])."?siteid=".$_POST['siteid']."&hash=".$hash;

    $link = $link.$_SERVER['SERVER_NAME'].str_replace("user-passreset.php", "user-passreset-finish.php", $_SERVER['REQUEST_URI'])."&siteid=".$siteid."&hash=".$hash;
    $link = str_replace("siteid=?", "", $link); // Intermittent Error //
    $link = str_replace("siteid=&", "", $link); // Intermittent Error //

    $message = "Use the following link to reset your password for the Commission Engine\r\n";// at Controlpad.com\r\n";
    $message = $message."The link will be active for 30 minutes\r\n\r\n";
    $message = $message.$link;
    SendEmail($from_name, $from_email, $to_email, $subject, $message);
*/
?>