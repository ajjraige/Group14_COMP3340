<?php

require_once "user_class.php";
require_once "item_class.php";

// Check if there is a user logged in.
$user = new User();
$result = $user->get_session();

if ($result == UserRtn::Success) {
    $status = "Success!";
} else {
    $status = "Not success, code: " . $result;
    $user = null;
}

// Only users are allowed to check out.
if ($user == null) {
    header("Location: home.php");
    exit;
} else {
    if (isset($_POST["success"])) {
        $result = Item::checkout($user);

        if ($result == ItemRtn::Success) {
            header("Location: products.php");
            exit;
        } else {
            echo "error code: " . $result;
        }
    } else if (isset($_POST["failed"])) {
        header("Location: cart.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
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
            <h3>Checkout</h3>
            <?php

            $items = Item::get_cart($user, 0);
            $price  = 0;

            if (gettype($items) == "array") {
                if (empty($items) && !isset($_POST["success"])) {
                    header("Location: cart.php");
                }

                foreach($items as $item) {
                    $price += $item->price * $item->quantity;
                }

                echo "Do you accept the $" . $price . " charge for this order?";
            } else {
                header("Location: cart.php");
                exit;
            }

            ?>
            <br>
            <form method = "post">
                <input type = "submit" name = "success" value = "Yes">
                <input type = "submit" name = "failed" value = "No">
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