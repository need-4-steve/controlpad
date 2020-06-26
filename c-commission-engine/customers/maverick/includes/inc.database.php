<?php
///////////////////////////// 
// Connect to the database // 
///////////////////////////// 
function ConnectDB() 
{ 	
	global $g_dbconn;
	global $g_username;
	global $g_password;
	global $g_database;
	global $g_host;

	$g_dbconn = pg_connect("host=".$g_host." dbname=".$g_database." user=".$g_username." password=".$g_password)
		or die('Could not connect: ' . pg_last_error()); 
} 

////////////////////////////////// 
// Disconnect from the database // 
////////////////////////////////// 
function DisconnectDB() 
{ 	
	global $g_dbconn; 	
	pg_close($g_dbconn); 
} 

////////////////////////////////////// 
// Select a value from the database // 
////////////////////////////////////// 
function QueryDB($query) 
{ 	
	//echo "Q = ".$query."<br><br>"; 	
	$result = pg_query($query) 
		or die('Query failed: ' . pg_last_error()); 	

	$line = pg_fetch_array($result, null, PGSQL_ASSOC); 	
	pg_free_result($result); 	
	return $line; 
} 

////////////////////////////////////// 
// Select a value from the database // 
////////////////////////////////////// 
function ExecDB($query) 
{ 	
	//echo "E = ".$query."<br><br>"; 	
	$result = pg_query($query); 
	//echo "result = ".$result."<br>";
	if (empty($result))
	{
		//echo 'Query failed: ' . pg_last_error();
		return false;
	}

	pg_free_result($result); 	
	return true; 
}

/////////////////////////
// Help with Debugging //
/////////////////////////
function Pre($json)
{
	echo "<pre>";
	print_r($json);
	echo "</pre>";
}