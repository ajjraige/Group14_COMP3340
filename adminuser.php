<?php

require_once "user_class.php";

$status = "";
$success = null;
$user;
$user_viewed;

// Check if there is a user logged in.
$user = new User();
$result = $user->get_session();

if ($result == UserRtn::Success) {
    $status = "Success!";

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

// We use a GET request to know which user the admin wants to access.
if (isset($_GET["user"])) {
    $userid = htmlspecialchars($_GET["user"]);

    // Attempt to get the given user id from the database.
    $user_viewed = User::get_user_by_id($userid);

    if (gettype($user_viewed) != "object") {
        header("Location: admin.php?users");
        exit;
    }
} else {
    header("Location: admin.php?users");
    exit;
}

// We don't want to update user information if the value of the checkbox is
// the same as the value for the user's value for that status. This takes
// a bit of tedious use of if statements. Also we prevent updating special statuses
// for the admin themselves to prevent them from accidentally locking themselves
// out of their admin panel (via the admin column) or their account entirely
// (via the banned column).
if (isset($_POST["update"]) && $user_viewed->userid != $user->userid) {
    if (isset($_POST["admin"])) {  
        // Frustratingly, checkboxes only POST a value if they are active,
        // and will POST nothing if inactive. So, we only want to toggle a user's
        // status if the checkbox value and the admin's value for that attribute
        // are not equal.
        if ($user_viewed->admin != true) {
            $result = $user_viewed->toggle_admin();
    
            if ($result != UserRtn::Success) {
                $status = "Could not change the user's admin status. Error code: " . $result;
            } else {
                $status = "User changes were made successfully.";
                $success = true;
            }
        }
    } else {
        if ($user_viewed->admin != false) {
            $result = $user_viewed->toggle_admin();
    
            if ($result != UserRtn::Success) {
                $status = "Could not change the user's admin status. Error code: " . $result;
            } else {
                $status = "User changes were made successfully.";
                $success = true;
            }
        }
    }
    
    if (isset($_POST["banned"])) {
        if ($user_viewed->banned != true) {
            $result = $user_viewed->toggle_ban();
    
            if ($result != UserRtn::Success) {
                $status = "Could not change the user's ban status. Error code: " . $result;
            } else {
                if ($success == null) {
                    $status = "User changes were made successfully.";
                    $success = true;
                }
            }
        }
    } else {
        if ($user_viewed->banned != false) {
            $result = $user_viewed->toggle_ban();
    
            if ($result != UserRtn::Success) {
                $status = "Could not change the user's ban status. Error code: " . $result;
            } else {
                if ($success == null) {
                    $status = "User changes were made successfully.";
                    $success = true;
                }
            }
        }
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

                    // The admin is the only user allowed in the admin panel, so we don't need alternate submenus.
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
                <a href = "admin.php?orders">View Orders</a><br>
                Manage Users<br>
                <a href = "admin.php?products">Manage Products</a><br>
                <a href = "adminproduct.php">Add New Product</a>
            </div>
            <div id = "content">
                <?php

                echo "<h3>View User</h3>";

                if ($success != null) {
                    if ($success) {
                        echo "<div class = \"successmsg\">" . $status . "</div>";
                    } else {
                        echo "<div class = \"errormsg\">" . $status . "</div>";
                    }
                }

                // We output the form in PHP to have the page know all the user's information
                // beforehand. I thought the look of disabling all the information that shouldn't 
                // be changed was kinda neat, so that's why all this non-editable information is
                // put in a form as a disabled input instead of elsewhere.
                echo "<form method = \"post\">";
                echo "<label for = \"username\">Username</label>";
                echo "<input type = \"text\" name = \"username\" value = \"" . $user_viewed->username . "\" disabled>";
                echo "<label for = \"fname\">First Name</label>";
                echo "<input type = \"text\" name = \"fname\" value = \"" . $user_viewed->fname . "\" disabled>";
                echo "<label for = \"lname\">Last Name</label>";
                echo "<input type = \"text\" name = \"lname\" value = \"" . $user_viewed->lname . "\" disabled>";
                echo "<label for = \"address\">Address</label>";
                echo "<input type = \"text\" name = \"address\" value = \"" . $user_viewed->address . "\" disabled>";
                echo "<label for = \"zip\">Zip Code</label>";
                echo "<input type = \"text\" name = \"zip\" value = \"" . $user_viewed->zip . "\" disabled>";
                echo "<label for = \"email\">E-mail</label>";
                echo "<input type = \"text\" name = \"email\" value = \"" . $user_viewed->email . "\" disabled>";
                echo "<label for = \"admin\">Is User an Admin?</label>";

                $disable = $user_viewed->userid == $user->userid ? "disabled" : ""; 

                // Disable or enable the checkbox depending on if that user is an admin or not.
                if ($user_viewed->admin) {
                    echo "<input type = \"checkbox\" name = \"admin\" value = \"true\" checked " . $disable . ">";
                } else {
                    echo "<input type = \"checkbox\" name = \"admin\" value = \"true\"" . $disable . ">";
                }
                echo "<label for = \"banned\">Is User Banned?</label>";
                // Disable or enable the checkbox depending on if that user is banned or not.
                if ($user_viewed->banned) {
                    echo "<input type = \"checkbox\" name = \"banned\" value = \"true\" checked" . $disable . ">";
                } else {
                    echo "<input type = \"checkbox\" name = \"banned\" value = \"true\"" . $disable . ">";
                }
                echo "<input type = \"submit\" name = \"update\" value = \"Save Changes\"" . $disable . "></form>";

                // And now we use the lastpage session variable to remember where the user left off
                // (page number wise) on their previous page, so the user can return back to 
                // the page they were at in the users list instead of returning back to page 1
                // every time.
                $opts = "";

                if (isset($_SESSION["lastpage"])) {
                    $opts .= "&page=" . $_SESSION["lastpage"];
                }

                echo "<a href = \"admin.php?users" . $opts . "\">Go back</a>";
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