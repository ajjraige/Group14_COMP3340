<?php

require_once "user_class.php";
require_once "order_class.php";

$status = "";
$user;
$type = 0;
$page = 0;
$list = [];

// Check if there is a user logged in.
$user = new User();
$result = $user->get_session();

if ($result == UserRtn::Success) {
    $status = "Success!";

    if (isset($_GET["logout"])) {
        header("Location: logout.php");
        exit;
    } else if ($user->admin == false) {
        header("Location: products.php");
        exit;
    }
} else {
    $status = "Not success, code: " . $result;
    header("Location: products.php");
    exit;
}

if (isset($_GET["orders"])) {
    $type = 1;
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    $_SESSION["lastpage"] = $page;

    // Populate the orders list with orders from database.
    $list = Order::get_orders($user, $page, true);

    if ($list == OrderRtn::InvalidParam) {
        header("Location: admin.php");
        exit;
    }
} else if (isset($_GET["users"])) {
    $type = 2;
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    $_SESSION["lastpage"] = $page;

    // Populate the orders list with orders from database.
    $list = User::get_users($page);

    if ($list == ItemRtn::InvalidParam) {
        header("Location: admin.php");
        exit;
    }
} else if (isset($_GET["products"])) {
    $type = 3;
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    $_SESSION["lastpage"] = $page;

    // Populate the orders list with orders from database.
    $list = Item::get_items(ItemOpts::None, null, $page);

    if ($list == ItemRtn::InvalidParam) {
        header("Location: admin.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
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
                        echo "<li>Admin</li>";
                        echo "<li><a href = \"logout.php\">Log Out</a></li>";
                    }

                    ?>
				</ul>
			</nav>
		</div>
    </div>
    <div class = "container">
        <h2 class = "title">Admin</h2>
        <div id = "pagetable">
            <div id = "sidebar">
                <b>Admin</b><br>
                <?php

                switch ($type) {
                    case 0:
                        echo "Overview <br> <a href = \"?orders\">View Orders</a> <br> <a href = \"?users\">Manage Users</a> <br> <a href = \"?products\">Manage Products</a> <br> <a href = \"adminproduct.php\">Add New Product</a>";
                        break;
                    case 1:
                        echo "<a href = \"admin.php\">Overview</a> <br> View Orders <br> <a href = \"?users\">Manage Users</a> <br> <a href = \"?products\">Manage Products</a> <br> <a href = \"adminproduct.php\">Add New Product</a>";
                        break;
                    case 2:
                        echo "<a href = \"admin.php\">Overview</a> <br> <a href = \"?orders\">View Orders</a> <br> Manage Users <br> <a href = \"?products\">Manage Products</a> <br> <a href = \"adminproduct.php\">Add New Product</a>";
                        break;
                    case 3:
                        echo "<a href = \"admin.php\">Overview</a> <br> <a href = \"?orders\">View Orders</a> <br> <a href = \"?users\">Manage Users</a> <br> Manage Products <br> <a href = \"adminproduct.php\">Add New Product</a>";
                        break;
                    default:
                        echo "Error.";
                }

                ?>
            </div>
            <div id = "content">
                <?php

                switch($type) {
                    case 0:
                        echo "<h3>Welcome to the admin panel!</h3>";
                        break;
                    case 1:
                        echo "<table><tr><th>Order #</th><th>User #</th><th>Cost</th><th>Date</th></tr>";
                            
                        if ($list < 0) {
                            echo "Could not retrive items, error code: " . $list;
                        } else {
                            foreach($list as $order) {
                                echo "<tr class = \"product\" onclick = \"window.location.href='adminorder.php?orderid=" . $order->orderid . "'\">";
                                echo "<td>" . $order->orderid . "</td>";
                                echo "<td>" . $order->userid. "</td>";
                                echo "<td>" . $order->cost. "</td>";
                                $date = new DateTime();
                                $date->setTimestamp($order->timestamp);
                                $fmt = $date->format("F j, Y, g:i a");
                                echo "<td>" . $fmt . "</td>";
                                echo "</tr>";
                            }
                        }

                        echo "</table><br><div class = \"next-btn\">";
        
                        if ($page > 1) {
                            echo "<a href = \"?orders&page=" . ($page - 1) . "\">Previous</a>"; 
                        }
        
                        echo " Page " . $page . " ";
        
                        if (gettype(Order::get_orders($user, $page + 1, true)) == "array") {
                            echo "<a href = \"?orders&page=" . ($page + 1) . "\">Next</a>"; 
                        }

                        echo "</div>";
                        break;
                    case 2:
                        echo "<table><tr><th>User #</th><th>Username</th><th>E-mail</th><th>Admin</th><th>Banned</th></tr>";
                            
                        if ($list < 0) {
                            echo "Could not retrive items, error code: " . $list;
                        } else {
                            foreach($list as $luser) {
                                echo "<tr class = \"product\" onclick = \"window.location.href='adminuser.php?item=" . $luser->userid . "'\">";
                                echo "<td>" . $luser->userid . "</td>";
                                echo "<td>" . $luser->username . "</td>";
                                echo "<td>" . $luser->email . "</td>";
                                echo "<td>" . ($luser->admin ? "YES" : "NO") . "</td>";
                                echo "<td>" . ($luser->banned ? "YES" : "NO") . "</td>";
                                echo "</tr>";
                            }
                        }

                        echo "</table><br><div class = \"next-btn\">";
        
                        if ($page > 1) {
                            echo "<a href = \"?users&page=" . ($page - 1) . "\">Previous</a>"; 
                        }
        
                        echo " Page " . $page . " ";
        
                        if (gettype(User::get_users($page + 1)) == "array") {
                            echo "<a href = \"?users&page=" . ($page + 1) . "\">Next</a>"; 
                        }

                        echo "</div>";
                        break;
                    case 3:
                        echo "<table><tr><th>Name</th><th>Description</th><th>Manufacturer</th><th>Price</th><th>Quantity</th></tr>";
                            
                        if ($list < 0) {
                            echo "Could not retrive items, error code: " . $list;
                        } else {
                            foreach($list as $item) {
                                echo "<tr class = \"product\" onclick = \"window.location.href='adminproduct.php?item=" . $item->itemid . "'\">";
                                echo "<td>" . $item->name . "</td>";
                                echo "<td>" . $item->description. "</td>";
                                echo "<td>" . $item->manufacturer . "</td>";
                                echo "<td>$" . $item->price . "</td>";
                                echo "<td>" . $item->quantity . "</td>";
                                echo "</tr>";
                            }
                        }

                        echo "</table><br><div class = \"next-btn\">";
        
                        if ($page > 1) {
                            echo "<a href = \"?products&page=" . ($page - 1) . "\">Previous</a>"; 
                        }
        
                        echo " Page " . $page . " ";
        
                        if (gettype(Item::get_items(ItemOpts::None, null, $page + 1)) == "array") {
                            echo "<a href = \"?products&page=" . ($page + 1) . "\">Next</a>"; 
                        }

                        echo "</div>";
                        break;
                    default:
                        echo "Error.";
                }

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