<?php

require_once "user_class.php";
require_once "order_class.php";

$status = "";
$user;
$item;

// Check if there is a user logged in.
$user = new User();
$result = $user->get_session();

if ($result == UserRtn::Success) {
    $status = "Success!";

    if (isset($_GET["logout"])) {
        header("Location: logout.php");
        exit;
    } else if ($user->admin == false) {
        header("Location: store.php");
        exit;
    }
} else {
    $status = "Not success, code: " . $result;
    header("Location: store.php");
    exit;
}

if (isset($_GET["item"])) {
    $itemid = htmlspecialchars($_GET["item"]);

    // Populate the orders list with orders from database.
    $item = Item::get_item_by_id($itemid);

    if (gettype($item) != "object") {
        header("Location: admin.php");
        exit;
    }
} else {
    $item = null;
}

if (isset($_POST["update"])) {
    $list = [];

    if (isset($_POST["name"]) && $_POST["name"] != $item->name) {
        $list["name"] = htmlspecialchars($_POST["name"]);
    }

    if (isset($_POST["description"]) && $_POST["description"] != $item->description) {
        $list["description"] = htmlspecialchars($_POST["description"]);
    }

    if (isset($_POST["manufacturer"]) && $_POST["manufacturer"] != $item->manufacturer) {
        $list["manufacturer"] = htmlspecialchars($_POST["manufacturer"]);
    }

    if (isset($_POST["price"]) && floatval($_POST["price"]) != $item->price) {
        if (floatval($_POST["price"]) > 0) {
            $list["price"] = floatval(htmlspecialchars($_POST["price"]));
        }     
    }

    if (isset($_POST["quantity"]) && intval($_POST["quantity"]) != $item->quantity) {
        if (intval($_POST["quantity"]) > 0) {
            $list["quantity"] = intval(htmlspecialchars($_POST["quantity"]));
        }  
    }

    if (isset($_POST["category"]) && intval($_POST["category"]) != $item->category) {
        if (intval($_POST["category"]) > 0) {
            $list["category"] = intval(htmlspecialchars($_POST["category"]));
        }       
    }
    
    if (!empty($list)) {
        $result = $item->update_info($list);

        if ($result != ItemRtn::Success) {
            echo "error updating item information: " . $result;
        }
    }
} else if (isset($_POST["add"])) {
    $name = htmlspecialchars($_POST["name"]);
    $description = htmlspecialchars($_POST["description"]);
    $manufacturer = htmlspecialchars($_POST["manufacturer"]);
    $price = floatval(htmlspecialchars($_POST["price"]));
    $quantity = intval(htmlspecialchars($_POST["quantity"]));
    $category = intval(htmlspecialchars($_POST["category"]));

    if ($category == 0) {
        $category = 1;
    }

    if ($price > 0 && $quantity > 0) {
        $result = Item::add_item($name, $description, $manufacturer, $price, $quantity, $category);

        if ($result != ItemRtn::Success) {
            exit;
        }
    }
} else if (isset($_POST["delete"])) {
    $result = $item->delete();

    if ($result != ItemRtn::Success) {
        echo "error deleting item: " . $result;
    }

    header("Location: admin.php?products");
    exit;
}

?>

<!DOCTYPE html>

<html lang = "en">
    <head>
        <title>Admin</title>
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
                        $extra = "Admin ";
                    }

                    echo "Hello, " . $user->username . "! <a href = \"cart.php\">My Cart</a> <a href = \"user.php\">My Account</a> " . $extra . "<a href = \"?logout=true\">Log out</a>";
                } else {
                    echo "<a href = \"login.php\">Log in</a> or <a href = \"register.php\">Register</a>";
                }

                ?>
            </div>
            <h1>Admin</h1>
            <div id = "store">
                <div id = "sidebar">
                    <b>Admin</b><br>
                    <?php

                    if ($item != null) {
                        echo "<a href = \"admin.php\">Overview</a> <br> <a href = \"admin.php?orders\">View Orders</a> <br> <a href = \"admin.php?users\">Manage Users</a> <br> Manage Products <br> <a href = \"\">Add New Product</a>";
                    } else {
                        echo "<a href = \"admin.php\">Overview</a> <br> <a href = \"admin.php?orders\">View Orders</a> <br> <a href = \"admin.php?users\">Manage Users</a> <br> <a href = \"admin.php?products\">Manage Products</a> <br> Add New Product";
                    }    

                    ?>
                </div>
                <div id = "items">
                    <?php
                    if ($item != null) {
                        echo "<h3>Edit Item</h3>";
                        echo "<form method = \"post\">";
                        echo "<label for = \"name\">Item Name</label>";
                        echo "<input type = \"text\" name = \"name\" value = \"" . $item->name . "\">";
                        echo "<label for = \"description\">Description</label>";
                        echo "<textarea name=\"description\" rows=\"4\" cols=\"50\">" . $item->description . "</textarea>";
                        echo "<label for = \"manufacturer\">Manufacturer</label>";
                        echo "<input type = \"text\" name = \"manufacturer\" value = \"" . $item->manufacturer . "\">";
                        echo "<label for = \"lname\">Price</label>";
                        echo "<input type = \"text\" name = \"price\" value = \"" . $item->price . "\">";
                        echo "<label for = \"quantity\">Quantity</label>";
                        echo "<input type = \"text\" name = \"quantity\" value = \"" . $item->quantity . "\">";
                        echo "<select name = \"category\">";
                        echo "<option value = \"1\">Uncategorized</option>";

                        $categories = Item::get_categories();

                        foreach ($categories as $category) {
                            $selected = "";

                            if ($category["id"] == $item->category) {
                                echo "selected id: " . $category["id"];
                                $selected = "selected = \"selected\"";
                            }

                            echo "<option value = \"" . $category["id"] . "\"" . $selected . ">" . $category["name"] . "</option>";
                        }

                        echo "</select>";

                        echo "<input type = \"submit\" name = \"delete\" value = \"Delete Item\">";
                        echo "<input type = \"submit\" name = \"update\" value = \"Save Changes\"></form>";

                        $opts = "";

                        if (isset($_SESSION["lastpage"])) {
                            $opts .= "&page=" . $_SESSION["lastpage"];
                        }

                        echo "<a href = \"admin.php?products" . $opts . "\">Go back</a>";
                    } else {
                        echo "<h3>New Item</h3>";
                        echo "<form method = \"post\">";
                        echo "<label for = \"name\">Item Name</label>";
                        echo "<input type = \"text\" name = \"name\">";
                        echo "<label for = \"description\">Description</label>";
                        echo "<textarea name=\"description\" rows=\"4\" cols=\"50\"></textarea>";
                        echo "<label for = \"manufacturer\">Manufacturer</label>";
                        echo "<input type = \"text\" name = \"manufacturer\">";
                        echo "<label for = \"lname\">Price</label>";
                        echo "<input type = \"text\" name = \"price\">";
                        echo "<label for = \"quantity\">Quantity</label>";
                        echo "<input type = \"text\" name = \"quantity\">";
                        echo "<select name = \"category\">";
                        echo "<option value = \"1\">Uncategorized</option>";

                        $categories = Item::get_categories();

                        foreach ($categories as $category) {
                            echo "<option value = \"" . $category["id"] . "\">" . $category["name"] . "</option>";
                        }

                        echo "</select>";
                        echo "<input type = \"submit\" name = \"add\" value = \"Add Item\"></form>";
                    }

                    ?>
                </div>
            </div>
        </div>
    </body>
</html>