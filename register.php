<?php

require_once "user_class.php";

$status = "";
$vars = ["username", "password", "first_name", "last_name", "address", "zip", "email"];
$vals = [];

// While not by any means a clean piece of code, this ensures that all our inputs
// for the register page are trimmed of whitespace, escaped using htmlspecialchars
// if they are going to be displayed on pages, and if the input is the username,
// make sure that some nasty special characters can't be used (escaping the
// username unfortunately has issues with verification).
foreach($vars as $var) {
    if (isset($_POST[$var])) {
        $value = trim($_POST[$var]);

        if ($var == "username") {
            $bad_chars = ["<", ">", "\"", "'", "-", "\\", "/", "="];
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
    // Attempt to register the user with the given values.
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
    <meta name="description" content="The website aims to provide best online buying experience of numerous plants and gardening tools. You can find the perfect plant or flower for your home!">
	<meta name="keywords" content="plants, flowering plants, indoor plants, vegetables, gardening tools, fertilizer, pesticide">
    <title>Register</title>
    <link rel="stylesheet" href="storestyle.css">
</head>
<body>
    <div class="container">
		<div class="navbar">
			<div class="logo">
				<img src="https://jahad.myweb.cs.uwindsor.ca/Logo.png" width="125" alt = "logo">
			</div>
			<nav>
				<ul>
					<li><a href="home.php">Home</a></li>
					<li><a href="products.php">Products</a></li>
					<li><a href="cart.php">Shopping Cart</a></li>
					<li><a href="account.php">Account</a></li>
					<li><a href="about.php">About</a></li>
					<li><a href="contact.php">Contact</a></li>
				</ul>
			</nav>
		</div>
    </div>
    <div class = "container">
        <div class = "smallbox">
            <h3>Register</h3>
            <?php

            // Display errors if there are any.
            if ($status != "") {
                echo "<div class = \"errormsg\">" . $status . "</div><br>";
            } else {
                echo "<br>";
            }

            ?>
            <form method = "post">
                <label for="username">Username:</label>
                <input type = "text" id = "username" name = "username" required><br>
                <label for="password">Password:</label>
                <input type = "password" id = "password" name = "password" required><br>
                <label for="first_name">First Name:</label>
                <input type = "text" id = "first_name" name = "first_name" required><br>
                <label for="last_name">Last Name:</label>
                <input type = "text" id = "last_name" name = "last_name" required><br>
                <label for="address">Address:</label>
                <input type = "text" id = "address" name = "address" required><br>
                <label for="zip">Zip Code:</label>
                <input type = "text" id = "zip" name = "zip" required><br>
                <label for="email">E-mail:</label>
                <input type = "text" id = "email" name = "email" required><br>
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
                        <li><a href = "help.php" style = "color: white;">Help</a></li>
                        <li>Coupons</li>
                        <li>Return Policy</li>
                        <li>Account</li>
                    </ul>
                </div>
                <div class="footer-col-2">
                    <img src="https://jahad.myweb.cs.uwindsor.ca/Logo.png" alt = "logo">
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