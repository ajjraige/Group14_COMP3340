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
    header("Location: home.php");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="storestyle.css">
</head>
<body>
    <div class="container">
		<div class="navbar">
			<div class="logo">
				<img src="https://jahad.myweb.cs.uwindsor.ca/Logo.png" width="125px">
			</div>
			<nav>
				<ul>
					<li><a href="home.php">Home</a></li>
					<li><a href="products.php">Products</a></li>
					<li><a href="cart.php">Shopping Cart</a></li>
					<li><a href="account.php">Account</a></li>
					<li><a href="About.html">About</a></li>
					<li><a href="contact.html">Contact</a></li>
				</ul>
			</nav>
		</div>
    </div>
    <div class = "container">
        <div class = "smallbox">
            <h3>Register</h3>
            <?php

            if ($status != "") {
                echo "<div class = \"errormsg\">" . $status . "</div><br>";
            } else {
                echo "<br>";
            }

            ?>
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
    </div>
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="footer-col-1">
                    <h3>Useful Links</h3>
                    <ul>
                        <li>Coupons</li>
                        <li>Contact Support</li>
                        <li>Return Policy</li>
                        <li>Account</li>
                    </ul>
                </div>
                <div class="footer-col-2">
                    <img src="https://jahad.myweb.cs.uwindsor.ca/Logo.png">
                </div>
                <div class="footer-col-3">
                    <h3>Social Media</h3>
                    <ul>
                        <li>Facebook</li>
                        <li>Instagram</li>
                        <li>Twitter</li>
                        <li>Youtube</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>    
</body>
</html>