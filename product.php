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

if (isset($_POST["rating"]) && $user != null && $item != null) {
    $result = $item->rate($user, intval(htmlspecialchars($_POST["rating"])));

    if ($result == ItemRtn::Success) {
        $status = "Success";
    } else {
        if ($result == ItemRtn::InvalidParam) {
            $status = "ERROR: Rating not in range of 1 to 5.";
        } else {
            $status = "Could not rate item, error code: " . $result;
        }      
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
    <meta name="description" content="The website aims to provide best online buying experience of numerous plants and gardening tools. You can find the perfect plant or flower for your home!">
	<meta name="keywords" content="plants, flowering plants, indoor plants, vegetables, gardening tools, fertilizer, pesticide">
    <title>Products</title>
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
                <br><br>
                <ul>     
					<?php

                    // Choose which submenu to show based on if a user is logged in (or an admin).
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
            <div class="col-4">
                <?php

                echo "<img src=\"" . $item->imgpath . "\" width=\"100%\" alt = \"" . $item->name . "\">";

                ?>
            </div>
            <div class="col-4">
                <?php

                // First, we check for the status of the action to add the item in a given amount to the
                // cart, if an action occurred.
                if (gettype($added) == "boolean" && $added) {
                    echo "<div class = \"successmsg\">Item successfully added to your cart</div>";
                } else if (gettype($added) == "integer") {
                    if ($added == ItemRtn::InsufficientStock) {
                        echo "<div class = \"errormsg\">The amount of the item you were attempting to add to your cart exceeds the current stock.</div>";
                    } else {
                        echo "<div class = \"errormsg\">Could not add item to cart, error code: " . $added . "</div>";
                    }                   
                }

                // Then we check to see if an item was given a rating, and display the corresponding message
                // depending on if it was successful or not.
                if ($status != "") {
                    if ($status == "Success") {
                        echo "<div class = \"successmsg\">Item rated successfully.</div>";
                    } else {
                        echo "<div class = \"errormsg\">" . $status . "</div>";
                    }
                }

                if ($item == null || gettype($item) == "integer") {
                    header("Location: products.php");
                    exit;
                } else {
                    // Find the category name for our item, and display it on top.
                    foreach ($categories as $category) {
                        if ($category["id"] == $item->category) {
                            echo "<p>" . $category["name"] . "</p>";
                        }
                    }
                    
                    // Display the name and price of the item.
                    echo "<h2>" . $item->name. "</h2>";
                    echo "<h4>$" . $item->price . " (" . $item->quantity . " left) </h4>";

                    // Users should be able to add items to their cart and rate them,
                    // but non logged-in users should not be able to see those inputs,
                    // so check for a user before outputting them.
                    if ($user != null) {
                        if ($item->quantity != 0) {
                            echo "<form method = \"post\"><input type = \"number\" value = \"1\" name = \"addtocart\" style = \"display: inline-block;\">";
                            echo "<input type = \"submit\" value = \"Add to cart\" style = \"display: inline-block;\"></form>";
                        } else {
                            echo "<b style = \"color: red;\">OUT OF STOCK</b>";
                        }

                        // Find the user rating and use that as the starting value if it exists.
                        $rating = $item->get_user_rating($user);

                        if ($rating < 1) {
                            $rating = 5;
                        }

                        echo "<form method = \"post\"><input type = \"number\" value = \"" . $rating . "\" name = \"rating\" style = \"display: inline-block;\">";
                        echo "<input type = \"submit\" value = \"Rate Product\" style = \"display: inline-block;\"></form>";
                    }

                    // Display the item manufacturer and description.
                    echo "<h3>Manufacturer</h3>";
                    echo "<p>" . $item->manufacturer . "</p>";
                    echo "<h3>Product Description</h3>";
                    echo "<p>" . $item->description . "</p><br>"; 

                    // Get overall rating information (average score, number of ratings).
                    $rating_info = $item->get_avg_rating();

                    if (gettype($rating_info) == "array") {
                        if ($rating_info["num"] != 0) {
                            echo "<p>Rating: " . intval($rating_info["avg"]) . "/5 (" . $rating_info["num"] . " ratings)</p><br>";
                        } else {
                            // Instead of outputting 0/5 with 0 ratings, this seemed like a cleaner output
                            // for when there are no ratings for a product.
                            echo "<p>No ratings have been made for this product yet.</p><br>";
                        }                      
                    } else {
                        echo "<p>Could not retrieve rating information.</p><br>";
                    } 
                }

                // Since the products page has several limiting criteria for its items
                // (category sorts, searching for keywords, and pages), we must keep
                // all these in mind (using their last saved values in the session)
                // when formulating our go back button, so the user doesn't lose where
                // they were in the products page after looking at a given item.
                $opts = "";

                if (isset($_SESSION["lastpage"])) {
                    $opts .= "?page=" . $_SESSION["lastpage"];
                }

                if (isset($_SESSION["lastcategory"]) && $_SESSION["lastcategory"] != null) {
                    $opts .= "&category=" . $_SESSION["lastcategory"];
                }

                if (isset($_SESSION["lastsearch"]) && $_SESSION["lastsearch"] != null) {
                    $opts .= "&search=" . $_SESSION["lastsearch"];
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