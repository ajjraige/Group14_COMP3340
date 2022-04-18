<?php

require_once "user_class.php";
require_once "item_class.php";

$user;

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

// Check to see if we need to get a specific page number or sort by search/category.
$page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
$category = isset($_GET["category"]) ? htmlspecialchars($_GET["category"]) : null;
$search = isset($_GET["search"]) ? htmlspecialchars($_GET["search"]) : null;

// Get category list.
$categories = Item::get_categories();

// Save the given values in the session so they can be returned to later after 
// viewing a product.
$_SESSION["lastpage"] = $page;
$_SESSION["lastcategory"] = array_search($category, $categories);
$_SESSION["lastsearch"] = $search;

// Populate the items list with store items from database.
$items;

if ($search != null) {
    $items = Item::get_items(ItemOpts::Search, $search, $page);
} else {
    $items = Item::get_items($category != null ? ItemOpts::Category : ItemOpts::None, $category, $page);
}


if ($items == ItemRtn::InvalidParam) {
    header("Location: home.php");
    exit;
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
    <div class="container">
        <h2 class="title">Products</h2>
        <!--SEARCH BAR-->
        <div class="search-bar">
            <form method = "get">
                <input class="search-txt" type="text" name="search" placeholder="Search">
                <input style = "display: inline-block;" type = "submit" value = "Search">
            </form>
        </div>
        <!--CATEGORIES-->
        <div id="menu">
            <details>
                <summary>Categories</summary>
                <?php

                // Populate the list of categories, and make sure that there is 
                // no link for the current category.
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

            // Output the items for this page onto the store.
            if ($items < 0) {
                echo "Could not retrive items, error code: " . $items;
            } else {
                foreach($items as $item) {
                    echo "<div class = \"col-3\" onclick = \"window.location.href='product.php?item=" . $item->itemid . "'\">";
                    echo "<img src = \"" . $item->imgpath . "\" alt = \"" . $item->name . "\">";
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

        // Preserve search or category sorting between pages.
        $extra = "";
        
        if ($category != null) {
            $extra = "&category=" . $category;
        } else if ($search != null) {
            $extra = "&search=" . $search;
        }

        if ($page > 1) {
            echo "<a href = \"?page=" . ($page - 1) . $extra ."\">Previous</a>"; 
        }

        echo "  Page " . $page . "  ";

        // The next page needs to check with get_items on the next page to make
        // sure that there are actually items on that page to display. Since
        // get_items has separate options to handle categories, search and none 
        // of the above, they must be separated into different calls, and 
        // the call that's needed for the situation should be used.
        if ($category != null) {
            if (gettype(Item::get_items(ItemOpts::Category, $category, $page + 1)) == "array") {
                echo "<a href = \"?page=" . ($page + 1) . $extra . "\">Next</a>"; 
            }
        } else if ($search != null) {
            if (gettype(Item::get_items(ItemOpts::Search, $search, $page + 1)) == "array") {
                echo "<a href = \"?page=" . ($page + 1) . $extra . "\">Next</a>"; 
            }
        } else {
            if (gettype(Item::get_items(ItemOpts::None, null, $page + 1)) == "array") {
                echo "<a href = \"?page=" . ($page + 1) . $extra . "\">Next</a>"; 
            }
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