<?php

require_once "user_class.php";
require_once "order_class.php";

$status = "";
$user;
$page = 1;
$order;

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

if (isset($_GET["orderid"])) {
    $id = htmlspecialchars($_GET["orderid"]);
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    
    // Populate the orders list with orders from database.
    $order = Order::get_order_by_id($id, $user, $page);
    
    if ($order == ItemRtn::InvalidParam) {
        header("Location: user.php");
        exit;
    }
}

?>

<!DOCTYPE html>

<html lang = "en">
    <head>
        <title>My Account</title>
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

                    echo "Hello, " . $user->username . "! <a href = \"cart.php\">My Cart</a> My Account " . $extra . "<a href = \"?logout=true\">Log out</a>";
                } else {
                    echo "<a href = \"login.php\">Log in</a> or <a href = \"register.php\">Register</a>";
                }

                ?>
            </div>
            <h1>My Account</h1>
            <div id = "store">
                <div id = "sidebar">
                    <b>My Account</b> <br>
                    <a href = "user.php">Edit account information</a> <br>
                    My Orders
                </div>
                <div id = "items">
                    <?php

                    $date = new DateTime();
                    $date->setTimestamp($order->timestamp);
                    $fmt = $date->format("F j, Y, g:i a");
                    
                    echo "<b>Order #" . $order->orderid . " ($" . $order->cost . ") at " . $fmt . "</b>";

                    ?>

                    <table id = "itemslist">
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Manufacturer</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>

                        <?php

                        if ($order < 0) {
                            echo "Could not retrive items, error code: " . $order;
                        } else {
                            foreach($order->items as $item) {
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
                    
                    <?php

                    $lastpno = 1;

                    if (isset($_SESSION["lastpage"])) {
                        $lastpno = $_SESSION["lastpage"];
                    }

                    echo "<a href = \"user.php?orders&page=" . $lastpno . "\">Go back </a>";

                    ?>

                    <br>
                    <div id = "nextpage">
                        <?php
    
                        if ($page > 1) {
                            echo "<a href = \"?orderid=" . $order->orderid . "&page=" . ($page - 1) . "\">Previous</a>"; 
                        }
    
                        echo " Page " . $page . " ";

                        $nextorder = Order::get_order_by_id($id, $user, $page + 1);
    
                        if (gettype($nextorder) == "object") {
                            echo "<a href = \"?orderid=" . $order->orderid . "&page=" . ($page + 1) . "\">Next</a>"; 
                        }

                        ?>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>