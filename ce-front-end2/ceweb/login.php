<?php
include "includes/inc.ce-comm.php";

// Handle logout //
if ($_GET['logout'] == "true")
{ 
    if (!empty($_SESSION['authemail']))
    {
        $fields[] = "email";
        $values["email"] = $_SESSION['authemail'];
        $headers = BuildHeader(MASTER, "logoutsysuserlog", $fields, "", $values);
        $json = PostURL($headers, "false");
    }

    // Clear everyting out on logout //
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
}

// Handle account creation //
if (($_POST["direction"] == "createaccount") && ($_POST["accesscode"] != "Controlpad1"))
{
    $errorcode = "<h3 align=center><font color=red>Invalid Access Code</font></h3>";
}
else if ($_POST["direction"] == "createaccount")
{
    $fields[] = "firstname";
    $fields[] = "lastname";
    $fields[] = "email";
    $fields[] = "password";
    $fields[] = "remoteaddress";
    $values["firstname"] = $_POST["firstname"];
    $values["lastname"] = $_POST["lastname"];
    $values["email"] = $_POST["email"];
    $values["password"] = $_POST["password"];
    $values["remoteaddress"] = $_SERVER['REMOTE_ADDR'];
    $headers = BuildHeader(MASTER, "addsystemuser", $fields, "", $values);

    // If successful, then redirect to index.php //
    $json = PostURL($headers, "false");
    if (HandleResponse($json, SUCCESS_NOTHING) == true)
    {
        $_SESSION['sysuserloggedin'] = "true"; // Allow into admin portal //
        $_SESSION['loggedintime'] = time(); // Starting point for 24 minutes // 
        $_SESSION['authemail'] = $_POST['email']; // Needed for API authentication //
        $_SESSION['authpass'] = $_POST['password']; // Needed for API authentication //

        SingleSystemCheck(); // If single system, then set as default //
        header("Location: index.php");
        exit();
    }
}

// Handle Login //
if ($_POST["direction"] == "login")
{
    $headers = [];
    $headers[] = "command: loginsystemuser";
    $headers[] = "authemail: ".$_POST['email'];
    $headers[] = "authpass: ".$_POST['password'];
    $headers[] = "remoteaddress: ".$_SERVER['REMOTE_ADDR'];

     // If successful, then redirect to index.php //
    $json = PostURL($headers, "false");
    if (HandleResponse($json, SUCCESS_NOTHING) == true)
    {
        // Set vars //
        $_SESSION['sysuserloggedin'] = "true"; // Allow into admin portal //
        $_SESSION['loggedintime'] = time(); // starting point for 24 minutes //
        $_SESSION['authemail'] = $_POST['email']; // Needed for API authentication //
        $_SESSION['authpass'] = $_POST['password']; // Needed for API authentication //

        // If single system, then set as default //
        SingleSystemCheck(); 
        header("Location: index.php");
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
              <h1>Admin Login</h1>
              <div>
                <input type="text" class="form-control" placeholder="Email" required="" name='email' value='<?=$_POST['email']?>' />
              </div>
              <div>
                <input type="password" class="form-control" placeholder="Password" required="" name='password' value='<?=$_POST['password']?>' />
              </div>
              <div>
                <!--<a class="btn btn-default submit" href="login.php">Log in</a>-->
                <input type="submit" class="btn btn-default submit" value="Log in"/>
                <a class="reset_pass" href="passreset.php">Lost your password?</a>
              </div>

              <div class="clearfix"></div>

              <div class="separator">
                <p class="change_link">New to site?
                  <a href="#signup" class="to_register"> Create Account </a>
                </p>
                <p class="change_link">Affiliate?
                  <a href="user-login.php" class="to_register"> Login </a>
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

        <div id="register" class="animate form registration_form">
          <section class="login_content">
            <form method="POST" action=''>
              <input type="hidden" name="direction" value="createaccount">

              <h1>Create Account</h1>

              <div>
                  <?=$errorcode?>
              </div>

              <div>
                <input type="text" class="form-control" name="accesscode" placeholder="AccessCode" required="" value="<?=$_POST['accesscode']?>" />
              </div>
              <div>
                <input type="text" class="form-control" name="firstname" placeholder="Firstname" required="" value="<?=$_POST['firstname']?>" />
              </div>
              <div>
                <input type="text" class="form-control" name="lastname" placeholder="Lastname" required="" value="<?=$_POST['lastname']?>" />
              </div>
              <div>
                <input type="email" class="form-control" name="email" placeholder="Email" required="" value="<?=$_POST['email']?>" />
              </div>
              <div>
                <input type="password" class="form-control" name="password" placeholder="Password" required="" value="<?=$_POST['password']?>" />
              </div>
              <div>
                <!--<a class="btn btn-default submit" href="login.php?#signup">Submit</a>-->
                <input type="submit" class="btn btn-default submit" value="Create Account"/>
              </div>

              <div class="clearfix"></div>

              <div class="separator">
                <p class="change_link">Already a member ?
                  <a href="#signin" class="to_register"> Log in </a>
                </p>

                <div class="clearfix"></div>
                <br />

                <div>
                  <h1><i class="fa fa-gears"></i> Commission Engine</h1>
                  <p>©2017 All Rights Reserved.<br>ControlPad - Commission Engine. Privacy and Terms</p>
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </body>
</html>
