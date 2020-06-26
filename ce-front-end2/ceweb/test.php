<?php

//echo "serversoftware = ".$_SERVER['SERVER_SOFTWARE'];

/*
$token = "Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE1MzI1NTk2MzUsImlhdCI6MTUzMjU1MjQzNSwiaXNzIjoiYXBpLmNvbnRyb2xwYWQuY29tIiwiYXVkIjoiYXBpLmNvbnRyb2xwYWQuY29tIiwic3ViIjoxMDYsIm5hbWUiOiJBZGFoIiwiZnVsbE5hbWUiOiJBZGFoIFJlaWNoZWwiLCJyZXBTdWJkb21haW4iOiJyZXAiLCJyb2xlIjoiUmVwIiwic2VsbGVyVHlwZSI6IlJlc2VsbGVyIiwicGVybSI6eyJjb3JlOmJ1eSI6MSwiY29yZTpzZWxsIjoxfSwiYWNjZXB0ZWRUZXJtcyI6dHJ1ZSwiYWN0aXZlU3Vic2NyaXB0aW9uIjp0cnVlLCJvcmdJZCI6bnVsbCwidGVuYW50X2lkIjoiMiIsImFjdHVhbFVzZXJJZCI6MTA5fQ.5WTOkJ5hXjdRSPCKr11p9Kr0MHB93RAfLK79kDLJyyo";

$token = str_replace("Bearer ", "", $token);
$base = explode(".", $token);

$json = base64_decode($base[1]);
echo "json=".$json."<br>";
$obj = json_decode($json, true);

echo "orgid=".$obj['exp'];
*/
//echo "data=".$obj['orig']."<br>";


/*
// Test CORS for Steve //
$url = "http://127.0.0.1:8080";
$headers = [];
//$headers[] = 'Access-Control-Allow-Origin: *';
//$headers[] = 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH';
//$headers[] = 'Access-Control-Allow-Credentials: true';
//$headers[] = 'Access-Control-Max-Age: 86400';
//$headers[] = 'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-Cp-Request-Id, X-Cp-Org-Id';

$headers[] = 'Access-Control-Request-Origin: *';
$headers[] = 'Access-Control-Request-Methods: POST, GET, OPTIONS, PUT, DELETE, PATCH';
$headers[] = 'Access-Control-Request-Credentials: true';
$headers[] = 'Access-Control-Max-Age: 86400';
$headers[] = 'Access-Control-Request-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN, X-Cp-Request-Id, X-Cp-Org-Id';


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
echo $data;
*/

/*
// Add Receipt Bulk //
$url = "http://comm.dev:8080";
$headers = [];
$headers[] = "command: addreceiptbulk";
$headers[] = "authemail: wanderson@controlpad.com";
$headers[] = "apikey: 83c49aaaf6bda9e6bd486c79b4ceb5abc7b7df198554327c297bff445104f66";
$headers[] = "systemid: 378515";
$headers[] = "userid: 1";
$headers[] = "receiptid: 55";
$headers[] = "qty: 4";
$headers[] = "wholesaledate: 2017-4-29";
$headers[] = "wholesaleprice: 29.99";
$headers[] = "invtype: 1";
$headers[] = "commissionable: true";
$headers[] = "metadata: ruff";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
echo $data;

// Update Receipt Bulk //
$url = "http://comm.dev:8080";
$headers = [];
$headers[] = "command: updatereceiptbulk";
$headers[] = "authemail: wanderson@controlpad.com";
$headers[] = "apikey: 83c49aaaf6bda9e6bd486c79b4ceb5abc7b7df198554327c297bff445104f66";
$headers[] = "systemid: 378515";
$headers[] = "userid: 1";
$headers[] = "receiptid: 55";
$headers[] = "qty: 3";
$headers[] = "retaildate: 2017-5-29";
$headers[] = "retailprice: 119.99";
$headers[] = "metadata: arff";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
echo $data;
*/

/*
$url = "http://comm.dev:8080";
$headers = [];
$headers[] = "command: myuservalidcheck";
$headers[] = "authemail: wanderson@controlpad.com";
$headers[] = "authpass: asdfASDF1";
$headers[] = "systemid: 378515";
$headers[] = "userid: 64690";
$headers[] = "email: test@test.com";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
echo $data;

$url = "http://comm.dev:8080";
$headers = [];
$headers[] = "command: mypasshashgen";
$headers[] = "authemail: wanderson@controlpad.com";
$headers[] = "authpass: asdfASDF1";
$headers[] = "systemid: 378515";
$headers[] = "remoteaddress: 5.5.5.5";
$headers[] = "userid: 1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
echo $data;
*/

/*
$url = "http://comm.dev:8080";
$headers = [];
$headers[] = "command: mypasshashupdate";
$headers[] = "authemail: wanderson@controlpad.com";
$headers[] = "authpass: asdfASDF1";
$headers[] = "systemid: 378515";
$headers[] = "userid: 64690";
$headers[] = "hash: 05DC3F45733650C3D22C52369EF229B3CD71EA398451B3C3061A02AD4614ABF6";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
echo $data;
*/
/*
$url = "http://comm.dev:8080";
$headers = [];
$headers[] = "command: mylogoutlog";
$headers[] = "authemail: wanderson@controlpad.com";
$headers[] = "authpass: asdfASDF1";
$headers[] = "systemid: 378515";
$headers[] = "email: test@test.com";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
echo $data;
*/

//echo "test = ".$json['success']['status']."<br>";

?>