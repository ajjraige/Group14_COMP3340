<?php

require_once "user_class.php";
require_once "item_class.php";

$status = "";

// Check if there is a user logged in.
$user = new User();
$result = $user->get_session();

if ($result != UserRtn::Success) {
    $user = null;
}

// Only users are allowed to check out.
if ($user == null) {
    header("Location: home.php");
    exit;
} else {
    // The success variable is for if the user accepts the transaction,
    // and failed is for when they cancel it.
    if (isset($_POST["success"])) {
        $result = Item::checkout($user);

        if ($result == ItemRtn::Success) {
            header("Location: home.php");
            exit;
        } else {
            if ($result == ItemRtn::InsufficientStock) {
                $status = "ERROR: One or more items in cart has exceeded stock quantity.";
            } else {
                $status = "ERROR: The shopping cart contents could not be checked out, error code: " . $result;
            }
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
    <meta name="description" content="The website aims to provide best online buying experience of numerous plants and gardening tools. You can find the perfect plant or flower for your home!">
	<meta name="keywords" content="plants, flowering plants, indoor plants, vegetables, gardening tools, fertilizer, pesticide">
    <title>Checkout</title>
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
            <h3>Checkout</h3>
            <?php

            // Some checks to see if we display error messages or if we display
            // the actual content.
            if ($status == "") {
                // Calculate the total price of the order.
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
    
                echo "<br><form method = \"post\"><input type = \"submit\" name = \"success\" value = \"Yes\">";
                echo "<input type = \"submit\" name = \"failed\" value = \"No\"></form>";
            } else {
                echo "<div class = \"errormsg\">" . $status . "</div>";
                echo "<br><a href = \"cart.php\">Go back</a>";
            }

            ?>
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