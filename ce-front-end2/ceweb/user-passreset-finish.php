<?php
include "includes/inc.ce-comm.php";
include "includes/inc.email.php";

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
              <input type="hidden" name="direction" value="resetpassword">
              <h1>Password Finish</h1>
<?php

$universalerror = "<div><b><font color=red>The reset link has expired. Please create another one</font></b><br><br><b><a href='user-passreset.php'>Click here to create another reset link</a></b></div>";
$special = "~ ! @ # $% ^ & * () _ -";

// Password Validation //
$validpassword = false;
if (($_POST["direction"] == "finalize") && (strlen($_POST["password"]) < '8'))
    echo "<div><b><font color=red>Your password needs at least 8 characters</font></b></div>";
else if (($_POST["direction"] == "finalize") && (!preg_match("#[0-9]+#", $_POST["password"])))
    echo "<div><b><font color=red>Your password needs at least 1 number</font></b></div>";
else if (($_POST["direction"] == "finalize") && (!preg_match("#[A-Z]+#", $_POST["password"])))
    echo "<div><b><font color=red>Your password needs at least 1 capitol letter</font></b></div>";
else if (($_POST["direction"] == "finalize") && (!preg_match("#[a-z]+#", $_POST["password"])))
    echo "<div><b><font color=red>Your password needs at least 1 lowercase letter</font></b></div>";
else if (($_POST["direction"] == "finalize") && (!preg_match("/^[A-Za-z0-9_~\-!@#\$%\^&*\(\)]+$/", $_POST["password"]))) 
    echo "<div><b><font color=red>The only allowed special characters are:<br>".$special."</font></b></div>";
else
    $validpassword = true;

// Validation //
if (!empty($_POST["direction"]) && ($_POST["direction"] != "finalize"))
    echo $universalerror; // Hacking attempt //
else if (empty($_GET['hash']))
    echo $universalerror; // Hacking attempt //
else if (ctype_alnum($_GET['hash']) == false)
    echo $universalerror; // Hash Date Expired //
else // hash is alphanumeric valid //
{
    $fields[] = "hash";
    $values["hash"] = $_GET['hash'];
    $headers = BuildHeader(MASTER, "mypasshashvalid", $fields, "", $values);
    $json = PostURL($headers, "false");
    if ($json["success"]["status"] != 200)
    {
        echo $universalerror; // Display Expired //
    }
    else if (($_POST["direction"] == "finalize") && ($validpassword == true))
    {
        $headers = BuildHeader(MASTER, "mypasshashupdate", $fields, "", $values);
        $json = PostURL($headers, "false");
        $userid = $json['hashupdate']['userid']; // Retain the userid //
        if ($json["success"]["status"] == 200)
        {
            $fields2[] = "systemid";
            $fields2[] = "userid";
            $fields2[] = "password";
            $fields2[] = "remoteaddress";
            $values2["systemid"] = $_GET["siteid"];
            $values2["userid"] = $userid;
            $values2["password"] = $_POST["password"];
            $values2["remoteaddress"] = $_SERVER['REMOTE_ADDR'];
            $headers = BuildHeader(MASTER, "mypassreset", $fields2, "", $values2);
            $json = PostURL($headers, "false");
            if (HandleResponse($json, SUCCESS_NOTHING) == false)
            {
                echo "<b><font color=red>If you see this then the API is down</font><b><br><br>";
            }

            // CHALKCOUTURE force password update on sim server for now //
            $json = PostURL($headers, "true");
            if (HandleResponse($json, SUCCESS_NOTHING) == false)
            {
                echo "<b><font color=red>If you see this then the API is down</font><b><br><br>";
            }
        }

        // Display success message with link //
        echo "<b><font color=green>Your password has been updated</font><b><br><br>";
        echo "<a href='user-login.php?siteid=".$_GET["siteid"]."'>Click here to login</a>";
    }
    else
    {
?>
        <div>
          <input type="password" class="form-control" placeholder="New Password" required="" name="password" value="<?=$_POST['password']?>" />
        </div>
        <div>
          <input type="submit" class="btn btn-default submit" value="Save Password"/>
        </div>
          <input type="hidden" name="direction" value="finalize">
          <input type="hidden" name="hash" value="<?=$_GET["hash"]?>">
          <input type="hidden" name="siteid" value="<?=$_GET["siteid"]?>">
<?php
    }
}

?>
              <div class="clearfix"></div>

              <div class="separator">
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
