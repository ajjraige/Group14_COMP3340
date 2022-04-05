<?php

require_once "user_class.php";
require_once "item_class.php";

$status = "";
$user;
$item = null;
$added = false;

// Check if there is a user logged in.
$user = new User();
$result = $user->get_session();

if ($result == UserRtn::Success) {
    $status = "Success!";

    if (isset($_GET["logout"])) {
        header("Location: logout.php");
        exit;
    }
} else {
    $user = null;
}

// Check for a GET request for an item id.
if (isset($_GET["item"])) {
    // Get the item from the database.
    $item = Item::get_item_by_id(htmlspecialchars($_GET["item"])); 
}

// Check for a POST request to add the item to the user's cart.
if (isset($_POST["addtocart"]) && $user != null && $item != null) {
    $result = $item->add_to_cart($user, htmlspecialchars($_POST["addtocart"]));

    if ($result == ItemRtn::Success) {
        $added = true;
    } else {
        $added = $result;
    }
}

// Get category list.
$categories = Item::get_categories();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
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
                    <br><br>
					<?php

                    if ($user != null) {
                        echo "<li>Hi, " . $user->username . "!</li>";

                        if ($user->admin) {
                            echo "<li><a href = \"admin.php\">Admin</a></li>";
                        }       

                        echo "<li><a href = \"logout.php\">Log Out</a></li>";
                    } else {
                        echo "<li><a href = \"login.php\">Log in</a> or <a href = \"register.php\">Register</a></li>";
                    }

                    ?>
				</ul>
			</nav>
		</div>
    </div>
<!--SINGLE ITEM -->
    <div class="single-item">
        <div class="row">
            <!-- TURN THIS INTO REAL PHP DYNAMIC IMAGE LATERS -->
            <div class="col-4">
                <img src="https://jahad.myweb.cs.uwindsor.ca/Logo.png" width="100%">
            </div>
            <div class="col-4">
                <?php

                if (gettype($added) == "boolean" && $added) {
                    echo "Item successfully added to your cart.<br>";
                } else if (gettype($added) == "integer") {
                    echo "Could not add item to cart, error code: " . $added . "<br>";
                }

                if ($item == null || gettype($item) == "integer") {
                    header("Location: products.php");
                    exit;
                } else {
                    foreach ($categories as $category) {
                        if ($category["id"] == $item->category) {
                            echo "<p>" . $category["name"] . "</p>";
                        }
                    }
                    
                    echo "<h2>" . $item->name. "</h2>";
                    echo "<h4>$" . $item->price . " (" . $item->quantity . " left) </h4>";

                    if ($user != null) {
                        echo "<form method = \"post\"><input type = \"number\" value = \"1\" name = \"addtocart\" style = \"display: inline-block;\">";
                        echo "<input type = \"submit\" value = \"Add to cart\" style = \"display: inline-block;\"></form>";
                    }

                    echo "<h3>Manufacturer</h3>";
                    echo "<p>" . $item->manufacturer . "</p>";
                    echo "<h3>Product Description</h3>";
                    echo "<p>" . $item->description . "</p>";
                    echo "<p>Rating: x/x</p><br>";  
                }

                $opts = "";

                if (isset($_SESSION["lastpage"])) {
                    $opts .= "?page=" . $_SESSION["lastpage"];
                }

                if (isset($_SESSION["lastcategory"])) {
                    $opts .= "&category=" . $_SESSION["lastcategory"];
                }

                echo "<a href = \"products.php" . $opts . "\">Go back</a>";

                ?>
            </div>
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