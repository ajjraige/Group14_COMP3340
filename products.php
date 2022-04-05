<?php

require_once "user_class.php";
require_once "item_class.php";

$status = "";
$user;

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

// Check to see if we need to get a specific page number or sort by search/category.
$page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
$category = isset($_GET["category"]) ? htmlspecialchars($_GET["category"]) : null;

// Get category list.
$categories = Item::get_categories();

$_SESSION["lastpage"] = $page;
$_SESSION["lastcategory"] = array_search($category, $categories);

// Populate the items list with store items from database.
$items = Item::get_items($category != null ? ItemOpts::Category : ItemOpts::None, $category, $page);

if ($items == ItemRtn::InvalidParam) {
    header("Location: store.php");
    exit;
}

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
    <div class="container">
        <h2 class="title">Products</h2>
        <!--SEARCH BAR-->
        <div class="search-bar">
            <input class="search-txt" type="text" name="search" placeholder="Search">
            <input style = "display: inline-block;" type = "submit" value = "Search">
        </div>
        <!--CATEGORIES-->
        <div id="menu">
            <details>
                <summary>Categories</summary>
                <?php

                if ($categories < 0) {
                    echo "Could not retrieve categories, error code: " . $categories;
                } else {
                    if ($category != null) {
                        echo "<a href = \"?page=1\">All items</a>";
                    } else {
                        echo "All items";
                    }
    
                    foreach ($categories as $entry) {
                        if ($entry["id"] == $category) {
                            echo "<br>" . $entry["name"];
                        } else {
                            echo "<br><a href = \"?category=" . $entry["id"] . "\">" . $entry["name"] . "</a>";
                        }
                    }
                }

                ?>
            </details>
        </div>   
        <div class="row">
            <?php

            if ($items < 0) {
                echo "Could not retrive items, error code: " . $items;
            } else {
                foreach($items as $item) {
                    echo "<div class = \"col-3\" onclick = \"window.location.href='product.php?item=" . $item->itemid . "'\">";
                    echo "<img src = \"https://jahad.myweb.cs.uwindsor.ca/Logo.png\">";
                    echo "<h4>" . $item->name. "</h4>";
                    echo "<p>$" . $item->price . " (" . $item->quantity . " remaining)</p>";
                    echo "</div>";
                }
            }

            ?>
        </div>
    </div>
    <!--NEXT PAGE BUTTON-->
    <div class="next-btn">
        <?php
                        
        $extra = "";

        if ($category != null) {
            $extra = "&category=" . $category;
        }

        if ($page > 1) {
            echo "<a href = \"?page=" . ($page - 1) . $extra ."\">Previous</a>"; 
        }

        echo "  Page " . $page . "  ";

        if (gettype(Item::get_items($category != null ? ItemOpts::Category : ItemOpts::None, $category, $page + 1)) == "array") {
            echo "<a href = \"?page=" . ($page + 1) . $extra . "\">Next</a>"; 
        }

        ?>
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