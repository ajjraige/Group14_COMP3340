<?php

require_once "user_class.php";

$status = "";
$user = new User();
$loggedin = $user->get_session();

// If the user is already logged in, they shouldn't be at the login page.
if ($loggedin == UserRtn::Success) {
    header("Location: store.php");
    exit;
}

if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    // SANITIZE INPUTS / DO BETTER SECURITY LATER

    // Attempt to log the user in.
    $result = $user->login($username, $password);
    switch($result) {
        case UserRtn::Success:
            $status = "Success!";
            header("Location: store.php");
            exit;
            break;
        case UserRtn::IncorrectUser:
            $status = "User does not exist.";
            break;
        case UserRtn::IncorrectPassword:
            $status = "Incorrect password.";
            break;
        case UserRtn::BannedUser;
            $status = "Banned account.";
            break;
        default:
            $status = "Unknown error: ". $result;
            break;
    }
}

?>

<!DOCTYPE html>

<html lang = "en">
    <head>
        <title>Log in</title>
        <meta charset = "utf-8">
        <link rel = "stylesheet" href = "style.css">
    </head>
    <body>
        <div id = "authbox">
            <b>Log in</b>
            <br>
            <strong id = "error">
                <?php

                if ($status != "") {
                    echo $status;
                } else {
                    echo "<br>";
                }

                ?>
            </strong>
            <form method = "post">
                <label for = "username">Username</label>
                <input type = "textbox" name = "username"><br>
                <label for = "password">Password</label>
                <input type = "password" name = "password"><br>
                <input type = "submit" value = "Log in">
            </form>
        </div>
    </body>
</html>