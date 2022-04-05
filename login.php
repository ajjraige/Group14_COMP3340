<?php

require_once "user_class.php";

$status = "";
$user = new User();
$loggedin = $user->get_session();

// If the user is already logged in, they shouldn't be at the login page.
if ($loggedin == UserRtn::Success) {
    header("Location: home.php");
    exit;
}

if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Attempt to log the user in.
    $result = $user->login($username, $password);
    switch($result) {
        case UserRtn::Success:
            $status = "Success!";
            header("Location: home.php");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
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
            <h3>Log in</h3>
            <?php

            if ($status != "") {
                echo "<div class = \"errormsg\">" . $status . "</div><br>";
            } else {
                echo "<br>";
            }

            ?>
            <form method = "post">
                <label for = "username">Username</label>
                <input type = "textbox" name = "username"><br>
                <label for = "password">Password</label>
                <input type = "password" name = "password"><br>
                <input type = "submit" value = "Log in">
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