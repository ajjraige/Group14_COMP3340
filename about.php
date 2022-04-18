<?php

require_once "user_class.php";
require_once "item_class.php";

// Check if there is a user logged in.
$user = new User();
$result = $user->get_session();

if ($result != UserRtn::Success) {
    $user = null;
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
    <title>About Us</title>
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
    <!--ABOUT US CONTENT-->
    <div class="about">
        <h2>About US</h2>
        <p>The Flower Pot is a local shop in Windsor, Ontario Canada that sells many different flowers and plants.<br>
            Our mission is to provide the community with a great place to go to when they are in need of flowers<br> or plants
            whether it be for a special occasion or just for fun. You can find us at 401 Sunset Ave, Windsor, ON.<br>
            You can also take a look at the map below for precise location.</p>
        <div id="map">
            <p><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2991.904367984015!2d-83.0695961233704!3d42.30597207328623!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x883b2d7a070cc1df%3A0xeb94ac24e6e11167!2sLeddy%20Library%20West%2C%20401%20Sunset%20Ave%2C%20Windsor%2C%20ON%20N9C%202J9!5e0!3m2!1sen!2sca!4v1649171955938!5m2!1sen!2sca" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></p>
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