<?php

require_once "user_class.php";
require_once "order_class.php";

$status = "";
$user;
$page = 0;
$orders = [];

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

if (isset($_POST["passchange"])) {
    $old = $_POST["oldpass"];
    $new = $_POST["newpass"];
    $result = $user->update_password($old, $new);

    if ($result != UserRtn::Success) {
        echo "error changing password: " . $result;
    }
} else if (isset($_POST["billchange"])) {
    $list = [];

    if (isset($_POST["fname"]) && $_POST["fname"] != $user->fname) {
        $list["first_name"] = htmlspecialchars($_POST["fname"]);
    }

    if (isset($_POST["lname"]) && $_POST["lname"] != $user->lname) {
        $list["last_name"] = htmlspecialchars($_POST["lname"]);
    }

    if (isset($_POST["addr"]) && $_POST["addr"] != $user->address) {
        $list["address"] = htmlspecialchars($_POST["addr"]);
    }

    if (isset($_POST["zip"]) && $_POST["zip"] != $user->zip) {
        $list["zip"] = htmlspecialchars($_POST["zip"]);
    }

    if (isset($_POST["email"]) && $_POST["email"] != $user->email) {
        $list["email"] = htmlspecialchars($_POST["email"]);
    }
    
    if (!empty($list)) {
        $result = $user->update_billing($list);

        if ($result != UserRtn::Success) {
            echo "error updating billing information: " . $result;
        }
    }
}

if (isset($_GET["orders"])) {
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    $_SESSION["lastpage"] = $page;

    // Populate the orders list with orders from database.
    $orders = Order::get_orders($user, $page, false);

    if ($orders == OrderRtn::InvalidParam) {
        header("Location: user.php?orders");
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
                    <b>My Account</b><br>
                    <?php

                    if ($page == 0) {
                        echo "Edit account information <br><a href = \"?orders\">My Orders</a>";
                    } else {
                        echo "<a href = \"user.php\">Edit account information</a> <br>My Orders";
                    }

                    ?>
                </div>
                <div id = "items">
                    <?php
                        if ($page == 0) {
                            echo "<h3>Change password</h3>";
                            echo "<form method = \"post\">";
                            echo "<label for = \"oldpass\">Current Password</label>";
                            echo "<input type = \"password\" name = \"oldpass\">";
                            echo "<label for = \"newpass\">New Password</label>";
                            echo "<input type = \"password\" name = \"newpass\">";
                            echo "<input type = \"submit\" name = \"passchange\" value = \"Change password\"></form>";
                            echo "<h3>Update billing information</h3>";
                            echo "<form method = \"post\">";
                            echo "<label for = \"fname\">First Name</label>";
                            echo "<input type = \"text\" name = \"fname\" value = \"" . $user->fname . "\">";
                            echo "<label for = \"lname\">Last Name</label>";
                            echo "<input type = \"text\" name = \"lname\" value = \"" . $user->lname . "\">";
                            echo "<label for = \"addr\">Address</label>";
                            echo "<input type = \"text\" name = \"addr\" value = \"" . $user->address . "\">";
                            echo "<label for = \"zip\">Zip Code</label>";
                            echo "<input type = \"text\" name = \"zip\" value = \"" . $user->zip . "\">";
                            echo "<label for = \"email\">E-mail</label>";
                            echo "<input type = \"text\" name = \"email\" value = \"" . $user->email . "\">";
                            echo "<input type = \"submit\" name = \"billchange\" value = \"Save Changes\"></form>";
                        } else {
                            echo "<table id = \"itemslist\"><tr><th>Order #</th><th>Cost</th><th>Date</th></tr>";
                            
                            if ($orders < 0) {
                                echo "Could not retrive items, error code: " . $orders;
                            } else {
                                foreach($orders as $order) {
                                    echo "<tr class = \"item\" onclick = \"window.location.href='order.php?orderid=" . $order->orderid . "'\">";
                                    echo "<td>" . $order->orderid . "</td>";
                                    echo "<td>" . $order->cost. "</td>";
                                    $date = new DateTime();
                                    $date->setTimestamp($order->timestamp);
                                    $fmt = $date->format("F j, Y, g:i a");
                                    echo "<td>" . $fmt . "</td>";
                                    echo "</tr>";
                                }
                            }

                            echo "</table><br><div id = \"nextpage\">";
        
                            if ($page > 1) {
                                echo "<a href = \"?orders&page=" . ($page - 1) . "\">Previous</a>"; 
                            }
        
                            echo " Page " . $page . " ";
        
                            if (gettype(Order::get_orders($user, $page + 1, false)) == "array") {
                                echo "<a href = \"?orders&page=" . ($page + 1) . "\">Next</a>"; 
                            }

                            echo "</div>";
                        }
                    ?>
                </div>
            </div>
        </div>
    </body>
</html>