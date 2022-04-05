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
    header("Location: home.php");
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Shopping Cart</title>
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

    <div class="Shopping-cart cart-page">
        <h2 class = "title">My Shopping Cart</h2>
        <?php
        
        $opt = "";
        if (empty($items)) {
            $opt = "disabled";
        }

        echo "<button onclick = \"window.location = 'checkout.php';\" " . $opt . ">Proceed to Checkout</button><br><br>";
        
        ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Manufacturer</th>
                <th>Price</th>
                <th>Quantity</th>
            </tr>
            <?php

            if ($items < 0) {
                echo "<tr><td colspan = \"5\" style = \"text-align: center;\">No items to display.</td></tr>";
            } else {
                foreach($items as $item) {
                    echo "<tr class = \"product\" onclick = \"window.location.href='product.php?item=" . $item->itemid . "'\">";
                    echo "<td><div class = \"info\"><img src = \"https://jahad.myweb.cs.uwindsor.ca/Logo.png\">"; // Make this the actual image later.
                    echo "<div><p>" . $item->name . "<br><form method = \"post\">";
                    echo "<input type = \"hidden\" name = \"item\" value = \"" . $item->itemid . "\">";
                    echo "<input type = \"submit\" name = \"delete\" value = \"X\"></form></div></div></td>";
                    echo "<td>" . $item->description. "</td>";
                    echo "<td><form method = \"post\">";
                    echo "<input type = \"hidden\" name = \"item\" value = \"" . $item->itemid . "\">";
                    echo "<input type = \"submit\" name = \"addone\" value = \"+\">";
                    echo "<input type = \"submit\" name = \"removeone\" value = \"-\"></form></td>";
                    echo "<td>" . $item->manufacturer . "</td>";
                    echo "<td>$" . $item->price . "</td>";
                    echo "<td>" . $item->quantity . "</td>";
                    echo "</tr>";
                }
            }

            ?>
        </table>
    </div>
    <div class="next-btn">
        <?php

        if ($page > 1) {
            echo "<a href = \"?page=" . ($page - 1) . "\">Previous</a>"; 
        }

        echo "  Page " . $page . "  ";

        if (gettype(Item::get_cart($user, $page + 1)) == "array") {
            echo "<a href = \"?page=" . ($page + 1) . "\">Next</a>"; 
        }

        ?>
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