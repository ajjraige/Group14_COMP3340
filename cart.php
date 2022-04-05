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
    $status = "Not success, code: " . $result;
    header("Location: store.php");
    exit;
}

// Check to see if an action was performed on a cart item.
if (isset($_POST["item"])) {
    $item = Item::get_item_by_id(htmlspecialchars($_POST["item"]));

    if ($item != null) {
        if (isset($_POST["addone"])) {
            $item->add_to_cart($user, 1);
        } else if (isset($_POST["removeone"])) {
            $item->remove_from_cart($user, 1);
        } else if (isset($_POST["delete"])) {
            $item->remove_from_cart($user, $item->quantity);
        }
    }
}

// Check to see if we need to get a specific page number.
$page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;

// Populate the shopping cart list with data from database.
$items = Item::get_cart($user, $page);

if ($items == ItemRtn::InvalidParam) {
    header("Location: cart.php");
    exit;
}

?>

<!DOCTYPE html>

<html lang = "en">
    <head>
        <title>My Cart</title>
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

                    echo "Hello, " . $user->username . "! My Cart <a href = \"user.php\">My Account</a> " . $extra . "<a href = \"?logout=true\">Log out</a>";
                } else {
                    echo "<a href = \"login.php\">Log in</a> or <a href = \"register.php\">Register</a>";
                }

                ?>
            </div>
            <h1>My Cart</h1>
            <div id = "store">
                <div id = "sidebar">
                    <b>Options</b><br>
                    <?php

                    $opt = "";
                    if (empty($items)) {
                        $opt = "disabled";
                    }

                    echo "<button onclick = \"window.location = 'checkout.php';\" " . $opt . ">Proceed to Checkout</button>"

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
                            <th>Action</th>
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
                                echo "<td><form method =\"post\">";
                                echo "<input type = \"hidden\" name = \"item\" value = \"" . $item->itemid . "\">";
                                echo "<input type = \"submit\" name = \"addone\" value = \"+\">";
                                echo "<input type = \"submit\" name = \"removeone\" value = \"-\">";
                                echo "<input type = \"submit\" name = \"delete\" value = \"X\">";
                                echo "</form></td>";
                                echo "</tr>";
                            }
                        }

                        ?>
                    </table>
                    <br>
                    <div id = "nextpage">
                        <?php
                        
                        $extra = "";

                        if ($page > 1) {
                            echo "<a href = \"?page=" . ($page - 1) . "\">Previous</a>"; 
                        }

                        echo " Page " . $page . " ";

                        if (gettype(Item::get_cart($user, $page + 1)) == "array") {
                            echo "<a href = \"?page=" . ($page + 1) . "\">Next</a>"; 
                        }

                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>