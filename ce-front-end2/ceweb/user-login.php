<?php
include "includes/inc.ce-comm.php";

// Handle single signon //
if (!empty($_GET['cp_token']))
{ 
    $headers[] = "authorization: ".$_GET['cp_token'];
    $headers[] = "command: myjwtverify";
    $headers[] = "systemid: 1";
    $json = PostURL($headers, "false");
    if ($json['success']['status'] == "200")
    {
        // Set vars //
        $_SESSION['user_id'] = $json['userid'];
        $_SESSION['userloggedin'] = "true"; // Allow into admin portal //
        $_SESSION['loggedintime'] = time(); // starting point for 24 minutes //
        //$_SESSION['useremail'] = $_POST['email']; // Needed for API authentication //
        //$_SESSION['userpass'] = $_POST['password']; // Needed for API authentication //
        $_SESSION['systemid'] = $_POST['siteid']; // Retain systemid for later //
        $_SESSION['cp_token'] = $_GET['cp_token'];

        // Grab the default systemid //
        $fields[] = "varname";
        $_POST['varname'] = "defaultsystem";
        $json = BuildAndPOST(MASTER, "settingsget", $fields, $pagvals);
        $_SESSION['systemid'] = $json['settings'][0]['value'];

        header("Location: user-index.php");
    }
}

$jwturl = GetJwtURL();
if (($jwturl != "") && ($_SESSION['userloggedin'] != "true"))
{
    header("Location: ".$jwturl);
    exit;
}

// Allow siteid to be passed in on the query string //
if (!empty($_GET['siteid']) && (empty($_POST['siteid'])))
  $_POST['siteid'] = $_GET['siteid'];

// Sanitize inputs //
if ((!empty($_POST['email'])) && ($_POST["direction"] == "login"))
{
    // Make sure email is valid //
    if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) == false)
    {
        $text = "This (".$_POST['email'].") email address is invalid<br><br>";
        echo "<div><b><font color=red>".$text."</font></b></div>";
        $invalid = true;
    }
    else if (filter_var($_POST['siteid'], FILTER_VALIDATE_INT) == false)
    {
        $text = "This (".$_POST['siteid'].") siteid is invalid<br><br>";
        echo "<div><b><font color=red>".$text."</font></b></div>";
        $invalid = true;
    }
    else
    {
        $invalid = false;
    }
}

// Handle logout //
if ($_GET['logout'] == "true")
{ 
    if (!empty($_SESSION['useremail']))
    {
        // Set database logout entry //
        $fields[] = "email";
        $values["email"] = $_SESSION['useremail'];
        $headers = BuildHeader(MASTER, "mylogoutlog", $fields, "", $values);
        $json = PostURL($headers, "false");

    }

    // Clear everything out on logout //
    unset($_SESSION['sysuserloggedin']);
    unset($_SESSION['authemail']);
    unset($_SESSION['authpass']);
    unset($_SESSION['userloggedin']);
    unset($_SESSION['useremail']); 
    unset($_SESSION['userpass']); 
    unset($_SESSION['systemid']);
    unset($_SESSION['systemname']);
    unset($_SESSION['commtype']); 
    unset($_SESSION['simulations']);
    unset($_SESSION['loggedintime']);
    unset($_SESSION['user_id']);
    unset($_SESSION['override']);

    // 
    $logouturl = GetJwtURLLogout();
    if (!empty($logouturl))
        header("Location: ".$logouturl);
}

// Handle Login //
if (($_POST["direction"] == "login") && ($invalid == false))
{
    $headers = [];
    $headers[] = "command: mylogin";
    $headers[] = "affiliateemail: ".$_POST['email'];
    $headers[] = "affiliatepass: ".$_POST['password'];
    $headers[] = "systemid: ".$_POST['siteid'];
    $headers[] = "remoteaddress: ".$_SERVER['REMOTE_ADDR'];

     // If successful, then redirect to user-index.php //
    $json = PostURL($headers, "false");
    if (HandleResponse($json, SUCCESS_NOTHING) == true)
    {
        // Set vars //
        $_SESSION['user_id'] = $json['user']['userid'];
        $_SESSION['userloggedin'] = "true"; // Allow into admin portal //
        $_SESSION['loggedintime'] = time(); // starting point for 24 minutes //
        $_SESSION['useremail'] = $_POST['email']; // Needed for API authentication //
        $_SESSION['userpass'] = $_POST['password']; // Needed for API authentication //
        $_SESSION['systemid'] = $_POST['siteid']; // Retain systemid for later //

        header("Location: user-index.php");
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ControlPad | Commission Engine</title>

    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="../vendors/animate.css/animate.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="../build/css/custom.min.css" rel="stylesheet">
  </head>

  <body class="login">
    <div>
      <a class="hiddenanchor" id="signup"></a>
      <a class="hiddenanchor" id="signin"></a>

      <div class="login_wrapper">
        <div class="animate form login_form">
          <section class="login_content">
            <form method="POST" action=''>
              <input type="hidden" name="direction" value="login">
              <h1>Affiliate Login</h1>
              <div>
                <input type="text" class="form-control" placeholder="Site ID" required="" name='siteid' value='<?=$_POST['siteid']?>' />
              </div>
              <div>
                <input type="text" class="form-control" placeholder="Email" required="" name='email' value='<?=$_POST['email']?>' />
              </div>
              <div>
                <input type="password" class="form-control" placeholder="Password" required="" name='password' value='<?=$_POST['password']?>' />
              </div>
              <div>
                <input type="submit" class="btn btn-default submit" value="Log in"/>
                <a class="reset_pass" href="user-passreset.php?siteid=<?=$_POST['siteid']?>">Lost your password?</a>
              </div>

              <div class="clearfix"></div>

              <div class="separator">
                
                <p class="change_link">Admin?
                  <a href="login.php" class="to_register"> Login </a>
                </p>

                <div class="clearfix"></div>
                <br />

                <div>
                  <h1><i class="fa fa-gears"></i> Commission Engine</h1>
                  <p>©2017 All Rights Reserved.<br>Controlpad - Commission Engine. Privacy and Terms</p>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </body>
</html>
