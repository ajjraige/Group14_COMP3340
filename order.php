<?php

require_once "user_class.php";
require_once "order_class.php";

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
    }
} else {
    header("Location: home.php");
    exit;
}

// We use a GET request to determine which order id the user wants to see.
if (isset($_GET["orderid"])) {
    $id = htmlspecialchars($_GET["orderid"]);
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    
    // Try to find the given order id associated with the currently logged in user.
    $order = Order::get_order_by_id($id, $user, $page);
    
    if (gettype($order) != "object") {
        header("Location: account.php");
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
    <div class = "container">
        <h2 class = "title">My Account</h2>
        <div id = "pagetable">
            <div id = "sidebar">
                <b>My Account</b> <br>
                <a href = "account.php">Edit account information</a> <br>
                My Orders
            </div>
            <div id = "content">
                <?php

                // Output order information at the top.
                $date = new DateTime();
                $date->setTimestamp($order->timestamp);
                $fmt = $date->format("F j, Y, g:i a");
                    
                echo "<b>Order #" . $order->orderid . " ($" . $order->cost . ") at " . $fmt . "</b> (" . $order->status . ")";

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

                    // Populate the items table with items from the order.
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
                    
                <?php

                $lastpno = 1;

                if (isset($_SESSION["lastpage"])) {
                    $lastpno = $_SESSION["lastpage"];
                }

                echo "<a href = \"account.php?orders&page=" . $lastpno . "\">Go back </a>";

                ?>

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