<?php
	///////////////////////////////////
	// Lookup the apikey in database //
	///////////////////////////////////
	
	$mandrill_apikey = "-aMzfPyhxNWqlMIlz3MaaQ";

	$message = "This is a test";
	$email = "westa911@gmail.com";
	$from_email = "wanderson@controlpad.com";
	$from_name = "Commissions";
	$subject = "Comm Email Test";

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
	                'email' => $email,
	                'type' => 'to'
	            )
	        )
	    );

	    $async = false;
	    $ip_pool = 'Main Pool';
	    $send_at = "now";
	    $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);

	    echo "<pre>";
	    print_r($result);
	    echo "</pre>";
	}
    catch (Mandrill_Error $e)
    {
	    // Mandrill errors are thrown as exceptions
	    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
	    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
	    throw $e;
	}

	echo "Done!!!<br>";

?>