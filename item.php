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

<html lang = "en">
    <head>
        <title>Item</title>
        <meta charset = "utf-8">
        <link rel = "stylesheet" href = "style.css">
    </head>
    <body>
        <div id = "container">
            <div id = "header">
                <div class = "cell">
                    <b>My Storefront</b>
                </div>  
                <div class = "cell">
                    <a href = "">Home</a>
                </div>   
                <div class = "cell">
                    <a href = "store.php">Store</a>
                </div>
                <div class = "cell">
                    <a href = "">About</a>
                </div>  
                <div class = "cell">
                    <a href = "">Contact Us</a>
                </div>          
            </div>
            <div id = "userbar">
                <?php
                
                if ($user != null) {
                    $extra = "";

                    if ($user->admin) {
                        $extra = "<a href = \"admin.php\">Admin</a> ";
                    }

                    echo "Hello, " . $user->username . "! <a href = \"cart.php\">My Cart</a> <a href = \"user.php\">My Account</a> " . $extra . "<a href = \"?logout=true\">Log out</a>";
                } else {
                    echo "<a href = \"login.php\">Log in</a> or <a href = \"register.php\">Register</a>";
                }

                ?>
            </div>
            <h1>Item</h1>
            <div id = "store">
                <div id = "sidebar">
                    <label for = "search" style = "font-weight: bold;">Search</label>
                    <input type = "textbox" name = "search">
                    <input type = "button" value = "Go"> <br>
                    <b>Categories</b>
                    <?php

                    if ($categories < 0) {
                        echo "Could not retrieve categories, error code: " . $categories;
                    } else {
                        echo "<br><a href = \"?page=1\">All items</a>";
                        
                        foreach ($categories as $entry) {
                            echo "<br><a href = \"store.php?category=" . $entry["id"] . "\">" . $entry["name"] . "</a>";           
                        }
                    }

                    ?>
                </div>
                <div id = "items">
                    <div id = "itemdisplay">
                        <div id = "left">
                            <?php

                            if (gettype($added) == "boolean" && $added) {
                                echo "Item successfully added to your cart.<br>";
                            } else if (gettype($added) == "integer") {
                                echo "Could not add item to cart, error code: " . $added . "<br>";
                            }

                            if ($item == null || gettype($item) == "integer") {
                                echo "Could not retrieve item, error code: " . $item;
                            } else {
                                echo "<h2>" . $item->name . "</h2>";
                                echo "<b>" . $item->manufacturer . "</b><br>";
                                echo "$" . $item->price . " (" . $item->quantity . " left)";
                                echo "<p>" . $item->description . "</p>";
                                echo "Rating: x/x";

                                if ($user != null) {
                                    echo "<form method = \"post\"><input type = \"number\" value = \"1\" name = \"addtocart\">";
                                    echo "<input type = \"submit\" value = \"Add to cart\"></form>";
                                }
                            }

                            ?>                 
                        </div>
                        <div id = "right">
                            <img src = "item.png">
                        </div>                        
                    </div>
                    <?php

                    $opts = "";

                    if (isset($_SESSION["lastpage"])) {
                        $opts .= "?page=" . $_SESSION["lastpage"];
                    }

                    if (isset($_SESSION["lastcategory"])) {
                        $opts .= "&category=" . $_SESSION["lastcategory"];
                    }

                    echo "<a href = \"store.php" . $opts . "\">Go back</a>";

                    ?>
                </div>          
            </div>
        </div>
    </body>
</html>