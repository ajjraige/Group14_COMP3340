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

// To know which information to display, we use a GET request for the given order id
// in the database.
if (isset($_GET["orderid"])) {
    $id = htmlspecialchars($_GET["orderid"]);
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    
    // Find the order with that ID, passing in NULL since normally, get_order_by_id
    // searches for orders by id only if they associate with a given userid, as 
    // a normal user should not be able to access another user's orders.
    $order = Order::get_order_by_id($id, NULL, $page);
    
    if (gettype($order) != "object") {
        header("Location: admin.php?orders");
        exit;
    }
}

// We also check to see if the status has been updated by the admin.
if (isset($_POST["status"])) {
    $new = htmlspecialchars($_POST["status"]);

    $result = $order->update_status($new);

    if ($result != OrderRtn::Success) {
        $status = "Failed to update order status.";
    } else {
        $status = "Success";
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
    <title>My Account</title>
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

                    // We know the user is an admin, so alternate submenus are not
                    // necessary.
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
                <a href = "admin.php">Overview</a><br>
                View Orders <br>
                <a href = "admin.php?users">Manage Users</a><br>
                <a href = "admin.php?products">Manage Products</a><br>
                <a href = "adminproduct.php">Add New Product</a>
            </div>
            <div id = "content">
                <?php

                // Print out order information.
                $date = new DateTime();
                $date->setTimestamp($order->timestamp);
                $fmt = $date->format("F j, Y, g:i a");
                    
                echo "<b>Order #" . $order->orderid . " ($" . $order->cost . ") at " . $fmt . "</b>";

                if ($status != "") {
                    if ($status == "Success") {
                        echo "<div class = \"successmsg\">Successfully updated order status.</div>";
                    } else {
                        echo "<div class = \"errormsg\">" . $status . "</div>";
                    }                  
                }

                ?>

                <table>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Manufacturer</th>
                        <th>Price</th>
                        <th>Quantity</th>
                    </tr>

                    <?php

                    foreach($order->items as $item) {
                        echo "<tr class = \"product\" onclick = \"window.location.href='product.php?item=" . $item->itemid . "'\">";
                        echo "<td>" . $item->name . "</td>";
                        echo "<td>" . $item->description. "</td>";
                        echo "<td>" . $item->manufacturer . "</td>";
                        echo "<td>$" . $item->price . "</td>";
                        echo "<td>" . $item->quantity . "</td>";
                        echo "</tr>";
                    }

                    ?>
                </table>
                <br>
                <div class = "next-btn">
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
                <form method = "post">
                    <label for = "status">Order Status</label>
                    <select name = "status">
                    <?php

                    // This code could have been just done using HTML without PHP,
                    // but I wanted the dropdown's selected status start with
                    // the current status for the order, which required the select
                    // options be populated using PHP.
                    $statuses = ["RECEIVED", "PROCESSING", "SHIPPED", "DELIVERED"];

                    foreach ($statuses as $option) {
                        $selected = "";

                        if ($option == $order->status) {
                            $selected = "selected";
                        }

                        echo "<option value = \"" . $option . "\" " . $selected . ">" . $option . "</option>";
                    }

                    ?>
                    </select>
                    <input type = "submit" value = "Update Order Status">
                </form>
                <?php

                // Each user session holds a value containing the last page of the table that the user accessed
                // (in this case, for the "View Orders" subpage of the admin panel). So, we can remember where
                // they left off, and return them to that, so they don't return to the orders page at page 1 every time.
                $opts = "";

                if (isset($_SESSION["lastpage"])) {
                    $opts .= "&page=" . $_SESSION["lastpage"];
                }

                echo "<a href = \"admin.php?orders" . $opts . "\">Go back</a>";

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