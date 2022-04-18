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
    if (isset($_GET["logout"])) {
        header("Location: logout.php");
        exit;
    } else if ($user->admin == false) {
        header("Location: home.php");
        exit;
    }
} else {
    header("Location: home.php");
    exit;
}

// The admin panel is also subdivided into pages via GET requests, to know
// which data to fetch and display.
if (isset($_GET["orders"])) {
    $type = 1;
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    $_SESSION["lastpage"] = $page;

    // Populate the orders list with orders from database.
    $list = Order::get_orders($user, $page, true);

    if ($list == OrderRtn::InvalidParam) {
        header("Location: admin.php?orders");
        exit;
    } else if ($list == OrderRtn::NoOrders) {
        $status = "There are no orders to display.";
    } else if (gettype($list) != "array") {
        $status = "There was an error retrieving orders, error code: " . $list;
    }
} else if (isset($_GET["users"])) {
    $type = 2;
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    $_SESSION["lastpage"] = $page;

    // Populate the users list with users from database.
    $list = User::get_users($page);

    if ($list == UserRtn::InvalidParam) {
        header("Location: admin.php?users");
        exit;
    } else if ($list == UserRtn::FailedQuery) {
        $status = "Could not retrieve users.";
    } else if (gettype($list) != "array") {
        $status = "There was an error retrieving users, error code: " . $list;
    }
} else if (isset($_GET["products"])) {
    $type = 3;
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    $_SESSION["lastpage"] = $page;

    // Populate the items list with items from database.
    $list = Item::get_items(ItemOpts::None, null, $page);

    if ($list == ItemRtn::InvalidParam) {
        header("Location: admin.php?products");
        exit;
    } else if ($list == ItemRtn::FailedQuery) {
        $status = "Could not retrieve products.";
    } else if (gettype($list) != "array") {
        $status = "There was an error retrieving products, error code: " . $list;
    }
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
    <title>Admin</title>
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

                    // We know that the user must be an admin to be at the admin
                    // panel, so we don't have to have alternate submenus.
                    // There is a check to make sure the user isn't null though,
                    // just in case somehow the location change and exit fail,
                    // which shouldn't be possible.
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

                // The sidebar menu for the admin panel shouldn't have a link
                // to the page the user is currently on, so we echo out the 
                // links differently depending on which page we're on, determined
                // by the type variable.
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

                // Now we output the content for the subpage we're on.
                switch($type) {
                    case 0:
                        // For the overview, have a link to the admin documentation,
                        // and print out any out of stock items to the page.
                        echo "<h3>Welcome to the admin panel!</h3>";
                    	echo "<p>Need help? Consult the admin documentation <a href = \"admin_docs.php\">here</a>.</p>";

                        $nostock_itemnames = Item::get_no_stock_items();

                        if (empty($nostock_itemnames)) {
                            echo "<p>There are no items currently out of stock!</p>";
                        } else {
                            echo "<p>The following items are currently out of stock: <br><br>";

                            foreach ($nostock_itemnames as $name) {
                                echo "<b>" . $name . "</b>";
                            }

                            echo "</p>";
                        }
                        break;
                    case 1:                       
                        if ($status != "") {
                            echo "<div class = \"errormsg\">" . $status . "</div>";
                        } else {
                            // For the orders page, we create a table for orders, and populate it.
                            echo "<table><tr><th>Order #</th><th>User #</th><th>Cost</th><th>Date</th><th>Status</th></tr>";

                            foreach($list as $order) {
                                echo "<tr class = \"product\" onclick = \"window.location.href='adminorder.php?orderid=" . $order->orderid . "'\">";
                                echo "<td>" . $order->orderid . "</td>";
                                echo "<td>" . $order->userid. "</td>";
                                echo "<td>" . $order->cost. "</td>";
                                $date = new DateTime();
                                $date->setTimestamp($order->timestamp);
                                $fmt = $date->format("F j, Y, g:i a");
                                echo "<td>" . $fmt . "</td>";
                                echo "<td>" . $order->status . "</td>";
                                echo "</tr>";
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
                        }

                        break;
                    case 2:                          
                        if ($status != "") {
                            echo "<div class = \"errormsg\">" . $status . "</div>";
                        } else {
                            // Much like the orders table, but this time for user information.
                            echo "<table><tr><th>User #</th><th>Username</th><th>E-mail</th><th>Admin</th><th>Banned</th></tr>";

                            foreach($list as $luser) {
                                echo "<tr class = \"product\" onclick = \"window.location.href='adminuser.php?user=" . $luser->userid . "'\">";
                                echo "<td>" . $luser->userid . "</td>";
                                echo "<td>" . $luser->username . "</td>";
                                echo "<td>" . $luser->email . "</td>";
                                echo "<td>" . ($luser->admin ? "YES" : "NO") . "</td>";
                                echo "<td>" . ($luser->banned ? "YES" : "NO") . "</td>";
                                echo "</tr>";
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
                        }

                        break;
                    case 3:                            
                        if ($status != "") {
                            echo "<div class = \"errormsg\">" . $status . "</div>";
                        } else {
                            // This time, we're populating a table with item information.
                            echo "<table><tr><th>Name</th><th>Description</th><th>Manufacturer</th><th>Price</th><th>Quantity</th></tr>";

                            foreach($list as $item) {
                                echo "<tr class = \"product\" onclick = \"window.location.href='adminproduct.php?item=" . $item->itemid . "'\">";
                                echo "<td>" . $item->name . "</td>";
                                echo "<td>" . $item->description. "</td>";
                                echo "<td>" . $item->manufacturer . "</td>";
                                echo "<td>$" . $item->price . "</td>";
                                echo "<td>" . $item->quantity . "</td>";
                                echo "</tr>";
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
                        }

                        break;
                    default:
                        echo "<div class = \"errormsg\">Unknown Error.</div>";
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