<?php

///////////////////
// Send an email //
///////////////////
function SendEmail($from_name, $from_email, $to_email, $subject, $message)
{
	$command = "curl -s --user 'api:key-2446ffff94864960d870a3096516bfc2' ";
	$command .= "https://api.mailgun.net/v3/my.controlpad.com/messages ";
	$command .= "-F from='No Reply <no-reply@my.controlpad.com>' ";
	$command .= "-F to=".$to_email." ";
	$command .= "-F subject='".$subject."' ";
	$command .= "-F text='".$message."'";

	$retval = exec($command);

	return true;

	/*
	$mandrill_apikey = "-aMzfPyhxNWqlMIlz3MaaQ";
	try {
		// Forward the message through mailchimp email system //
		require_once 'includes/Mandrill.php';
		$mandrill = new Mandrill($mandrill_apikey);	
		$message = array(
	        'text' => $message,
	        'from_email' => $from_email,
	        'from_name' => $from_name,
	        'subject' => $subject,
	        'to' => array(
	            array(
	                'email' => $to_email,
	                'type' => 'to'
	            )
	        )
	    );

	    $async = false;
	    $ip_pool = 'Main Pool';
	    $send_at = "now";
	    $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
	    if ($result[0]['status'] == "sent")
	    	return true;
	}
    catch (Mandrill_Error $e)
    {
	    // Mandrill errors are thrown as exceptions
	    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
	    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
	    throw $e;
	}

	return false;
	*/
}


?>
