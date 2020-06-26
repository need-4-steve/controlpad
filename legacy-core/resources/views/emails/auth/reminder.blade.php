<!DOCTYPE html>
<html lang="en-US">
    <head>
        <meta charset="utf-8">
        <title>Reset Password Instructions</title>
    </head>
    <body>
        <h1>How to Reset Your Password</h1>
        <p>
            To reset your password, click on the following link and complete the form: {{ URL::to('password/reset', array($token)) }}.<br/>
        </p>
        <p>
            This link will expire in {{ Config::get('auth.reminder.expire', 60) }} minutes.
        </p>
    </body>
</html>
