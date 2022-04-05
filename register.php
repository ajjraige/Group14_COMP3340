<?php

require_once "user_class.php";

$status = "";
$vars = ["username", "password", "first_name", "last_name", "address", "zip", "email"];
$vals = [];

// Make this whole section less horrible.
foreach($vars as $var) {
    if (isset($_POST[$var])) {
        $value = trim($_POST[$var]);

        if ($var == "username") {
            $bad_chars = ["<", ">", "\"", "'", "-", "\\", "/"];
            $found = false;

            foreach ($bad_chars as $char) {
                if (strpos($_POST[$var], $char)) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $status = "Invalid character in username.";
                break;
            }
        } else if ($var != "password") {
            $value = htmlspecialchars($value);
        }

        array_push($vals, $value);
    } else {
        break;
    }
}

$user = new User();
$loggedin = $user->get_session();

// If the user is already logged in, they shouldn't be at the register page.
if ($loggedin == UserRtn::Success) {
    header("Location: store.php");
    exit;
}

if (count($vals) == count($vars)) {
    $result = $user->register($vals[0], $vals[1], $vals[2], $vals[3], $vals[4], $vals[5], $vals[6]);
    switch($result) {
        case UserRtn::Success:
            $status = "Success!";
            header("Location: login.php");
            exit;
            break;
        case UserRtn::IncorrectUser:
            $status = "Username taken.";
            break;
        case UserRtn::IncorrectEmail:
            $status = "Email in use.";
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
        <title>Register</title>
        <meta charset = "utf-8">
        <link rel = "stylesheet" href = "style.css">
    </head>
    <body>
        <div id = "authbox">
            <b>Register</b>
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
                <label for="username">Username:</label>
                <input type = "text" id = "username" name = "username"><br>
                <label for="password">Password:</label>
                <input type = "password" id = "password" name = "password"><br>
                <label for="first_name">First Name:</label>
                <input type = "text" id = "first_name" name = "first_name"><br>
                <label for="last_name">Last Name:</label>
                <input type = "text" id = "last_name" name = "last_name"><br>
                <label for="address">Address:</label>
                <input type = "text" id = "address" name = "address"><br>
                <label for="zip">Zip Code:</label>
                <input type = "text" id = "zip" name = "zip"><br>
                <label for="email">E-mail:</label>
                <input type = "text" id = "email" name = "email"><br>
                <input type = "submit" value = "Register">
            </form>
        </div>
    </body>
</html>