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

<html lang = "en">
    <head>
        <title>Store</title>
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
            <h1>Store</h1>
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
                        if ($category != null) {
                            echo "<br><a href = \"?page=1\">All items</a>";
                        } else {
                            echo "<br>All items";
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
                </div>
                <div id = "items">
                    <table id = "itemslist">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Manufacturer</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>

                        <?php

                        if ($items < 0) {
                            echo "Could not retrive items, error code: " . $items;
                        } else {
                            foreach($items as $item) {
                                echo "<tr class = \"item\" onclick = \"window.location.href='item.php?item=" . $item->itemid . "'\">";
                                echo "<td>" . $item->name . "</td>";
                                echo "<td>" . $item->description. "</td>";
                                echo "<td>" . $item->manufacturer . "</td>";
                                echo "<td>$" . $item->price . "</td>";
                                echo "<td>" . $item->quantity . "</td>";
                                echo "</tr>";
                            }
                        }

                        ?>
                    </table>
                    <br>
                    <div id = "nextpage">
                        <?php
                        
                        $extra = "";

                        if ($category != null) {
                            $extra = "&category=" . $category;
                        }

                        if ($page > 1) {
                            echo "<a href = \"?page=" . ($page - 1) . $extra ."\">Previous</a>"; 
                        }

                        echo " Page " . $page . " ";

                        if (gettype(Item::get_items($category != null ? ItemOpts::Category : ItemOpts::None, $category, $page + 1)) == "array") {
                            echo "<a href = \"?page=" . ($page + 1) . $extra . "\">Next</a>"; 
                        }

                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>