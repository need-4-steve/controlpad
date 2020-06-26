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
              <h1>Reset Password</h1>
<?php

// Scrub/verify email input //
if ((!empty($_POST['email'])) && ($_POST["direction"] == "resetpassword"))
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

// Handle Login //
if ((empty($_POST['email'])) && ($_POST["direction"] == "resetpassword"))
{
    $text = "The email can't be empty<br><br>";
    echo "<div><b><font color=red>".$text."</font></b></div>";
}
else if (($_POST["direction"] == "resetpassword") && ($invalid == false))
{
    // Grab a siteid //
    if (!empty($_POST['siteid']))
        $siteid = $_POST['siteid'];
    else if (!empty($_GET['siteid']))
        $siteid = $_GET['siteid'];

    // Talk to API to reset the hash. If successful then send email //
    $fields[] = "systemid";
    $fields[] = "email";
    $values["systemid"] = $siteid;
    $values["email"] = $_POST['email'];
    $headers = BuildHeader(MASTER, "myuservalidcheck", $fields, "", $values);
    $json = PostURL($headers, "false");
    if ($json["success"]["status"] == 200)
    {
        $fields[] = "remoteaddress";
        $values["remoteaddress"] = $_SERVER['REMOTE_ADDR'];
        $headers = BuildHeader(MASTER, "mypasshashgen", $fields, "", $values);
        $json = PostURL($headers, "false");
        if ($json["success"]["status"] == 200)
        {
            // Retain returned hash //
            $hash = $json['hashgen']['hash'];

            // Send email //
            $from_name = "CommissionEngine";
            $from_email = "no-reply@controlpad.com";
            $to_email = $_POST['email'];
            $subject = "Reset Password";

            // Handle the link building //
            if (strstr($_SERVER['REQUEST_URI'], "?") == NULL)
                $link = $g_protocol.$_SERVER['SERVER_NAME'].str_replace("user-passreset.php", "user-passreset-finish.php", $_SERVER['REQUEST_URI'])."?hash=".$hash;
            else
                $link = $g_protocol.$_SERVER['SERVER_NAME'].str_replace("user-passreset.php", "user-passreset-finish.php", $_SERVER['REQUEST_URI'])."&hash=".$hash;

            if (strstr($link, "siteid=&") != NULL)
                $link = str_replace("siteid=&", "siteid=".$_POST['siteid']."&", $link);
            if (strstr($link, "siteid=") == NULL)
                $link = $link."&siteid=".$_POST['siteid'];

            $message = "Use the following link to reset your password for the Commission Engine at Controlpad.com\r\n";
            $message = $message."The link will be active for 30 minutes\r\n\r\n";
            $message = $message.$link;
            SendEmail($from_name, $from_email, $to_email, $subject, $message);
        }
    }

    $text = "If a valid email then a reset link<br>will be sent to your email address<br><br>";
    echo "<div><b><font color=green>".$text."</font></b></div>";
    $emailsent = "true";
}

if ($emailsent != "true")
{
?>
              <div>
                <input type="text" class="form-control" placeholder="Site ID" required="" name='siteid' value='<?=$_GET['siteid']?>' />
              </div>
              <div>
                <input type="text" class="form-control" placeholder="Email" required="" name='email' value='<?=$_POST['email']?>' />
              </div>
              <div>
                <input type="submit" class="btn btn-default submit" value="Reset Password"/>
              </div>
              <div class="clearfix"></div>
<?php
}
?>
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