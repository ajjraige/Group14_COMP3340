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
    if (isset($_GET["logout"])) {
        header("Location: logout.php");
        exit;
    }
} else {
    header("Location: home.php");
    exit;
}

// Check to see which form was submitted, so we know which POST variables to look
// for.
if (isset($_POST["passchange"])) {
    // htmlspecialchars is not needed to escape these passwords, since they will
    // never be outputted to any HTML document. The mysql escape occurs in the
    // user functions, so we can use the post variables as-is.
    $old = $_POST["oldpass"];
    $new = $_POST["newpass"];
    $result = $user->update_password($old, $new);

    if ($result != UserRtn::Success) {
        if ($result == UserRtn::IncorrectPassword) {
            $status = "Invalid current password.";
        } else {
            $status = "Could not change password, error code: " . $result;
        }      
    } else {
        $status = "Success";
    }
} else if (isset($_POST["billchange"])) {
    // We need to determine which of the inputs in the form were changed from
    // their original values, so an associative array is used holding the name
    // of the column in the database that the field corresponds to, and the new
    // value for it. If the value is the same as the current value, it is not 
    // added to the list.
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
            $status = "Could not update user information, error code: " . $result;
        } else {
            $status = "Success";
        }
    }
}

// This page has two "sub-pages", which are switched with a GET request.
if (isset($_GET["orders"])) {
    $page = isset($_GET["page"]) ? htmlspecialchars($_GET["page"]) : 1;
    $_SESSION["lastpage"] = $page;

    // Populate the orders list with orders from database.
    $orders = Order::get_orders($user, $page, false);

    if ($orders == OrderRtn::InvalidParam) {
        header("Location: account.php?orders");
        exit;
    } else if ($orders == OrderRtn::NoOrders) {
        $status = "There are no orders to be displayed.";
    } else if (gettype($orders) != "array") {
        $status = "Error retrieving user orders, error code: " . $orders;
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
                <b>My Account</b><br>
                <?php

                // Since only the "My Orders" page makes use of paging,  if the
                // page is at 0, which is invalid, as pages start at 1, then
                // we know we are on the account information page.
                if ($page == 0) {
                    echo "Edit account information <br><a href = \"account.php?orders\">My Orders</a>";
                } else {
                    echo "<a href = \"account.php\">Edit account information</a> <br>My Orders";
                }

                ?>
            </div>
            <div id = "content">
                <?php

                if ($page == 0) {
                    if ($status != "") {
                        if ($status == "Success") {
                            echo "<div class = \"successmsg\">User information changed successfully!</div>";
                        } else {
                            echo "<div class = \"errormsg\">" . $status . "</div>";
                        }
                    }

                    // Since we want to input the current values for each field into the
                    // inputs, so the user knows what their info is so they can change what
                    // is wrong, the entire form must be outputted through echos. No field is
                    // made required for the billing information, as which fields must be changed
                    // cannot be determined before submission.
                    echo "<h3>Change password</h3>";
                    echo "<form method = \"post\">";
                    echo "<label for = \"oldpass\">Current Password</label>";
                    echo "<input type = \"password\" name = \"oldpass\" required>";
                    echo "<label for = \"newpass\">New Password</label>";
                    echo "<input type = \"password\" name = \"newpass\" required>";
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
                    if ($status != "") {
                        echo "<div class = \"errormsg\">" . $status . "</div>";
                    } else {
                        // Since we are working with a separate page system between user information and orders,
                        // the table and all its contents must also be created in the PHP.
                        echo "<table><tr><th>Order #</th><th>Cost</th><th>Date</th><th>Status</th></tr>";

                        // Make each row for every order.
                        foreach($orders as $order) {
                            echo "<tr class = \"product\" onclick = \"window.location.href='order.php?orderid=" . $order->orderid . "'\">";
                            echo "<td>" . $order->orderid . "</td>";
                            echo "<td>" . $order->cost. "</td>";
                            // Orders are given a UNIX timestamp in the database, which must be converted to a 
                            // human-readable datetime before output.
                            $date = new DateTime();
                            $date->setTimestamp($order->timestamp);
                            $fmt = $date->format("F j, Y, g:i a");
                            echo "<td>" . $fmt . "</td>";
                            echo "<td>" . $order->status . "</td>";
                            echo "</tr>";
                        }

                        echo "</table><br><div class = \"next-btn\">";
                        
                        // Since paging is handled with GET requests, we can use links directing to the 
                        // same page, but with a new page value one greater or less than the current
                        // one.
                        if ($page > 1) {
                            echo "<a href = \"?orders&page=" . ($page - 1) . "\">Previous</a>"; 
                        }
        
                        echo " Page " . $page . " ";
        
                        // Since get_orders only retrieves 10 entries at a time, the only way
                        // to know if there are any items on the next page (and thus if we should
                        // have a link to the next page), is to call get_orders again on the next
                        // page, and see if it returns a list of items or an error code.
                        if (gettype(Order::get_orders($user, $page + 1, false)) == "array") {
                            echo "<a href = \"?orders&page=" . ($page + 1) . "\">Next</a>"; 
                        }

                        echo "</div>";
                    }
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