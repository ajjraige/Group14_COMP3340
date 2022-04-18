<?php

require_once "user_class.php";
require_once "item_class.php";

$status = "";
$success = null;
$user;
$item;

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

// We use GET requests to determine which item the user wants to see.
if (isset($_GET["item"])) {
    $itemid = htmlspecialchars($_GET["item"]);

    // Try to find the item at the given id.
    $item = Item::get_item_by_id($itemid);

    if (gettype($item) != "object") {
        header("Location: admin.php?products");
        exit;
    }
} else {
    $item = null;
}

// Since the admin product page is used both for creating new items and updating/deleting
// existing ones, a POST variable must be used to determine which option the user wants,
// so we know what other POST variables to look for.
if (isset($_POST["update"])) {
    // Since in the case of updating an item, there are any number of the fields
    // that could be updated, we must once again check all of them, and add ones
    // that have been changed from their original value to an associative array,
    // with a key for the corresponding column name in the database, and a value
    // for the new value.
    $list = [];

    if (isset($_POST["name"]) && $_POST["name"] != $item->name) {
        $list["name"] = htmlspecialchars($_POST["name"]);
    }

    if (isset($_POST["description"]) && $_POST["description"] != $item->description) {
        $list["description"] = htmlspecialchars($_POST["description"]);
    }

    if (isset($_POST["manufacturer"]) && $_POST["manufacturer"] != $item->manufacturer) {
        $list["manufacturer"] = htmlspecialchars($_POST["manufacturer"]);
    }

    if (isset($_POST["price"]) && floatval($_POST["price"]) != $item->price) {
        if (floatval($_POST["price"]) > 0) {
            $list["price"] = floatval(htmlspecialchars($_POST["price"]));
        }     
    }

    if (isset($_POST["quantity"]) && intval($_POST["quantity"]) != $item->quantity) {
        if (intval($_POST["quantity"]) > 0) {
            $list["quantity"] = intval(htmlspecialchars($_POST["quantity"]));
        }  
    }

    if (isset($_POST["category"]) && intval($_POST["category"]) != $item->category) {
        if (intval($_POST["category"]) > 0) {
            $list["category"] = intval(htmlspecialchars($_POST["category"]));
        }       
    }

    // Since items are also associated with an image, we must handle image uploads as well.
    if (isset($_FILES["image"]) && $_FILES["image"]["size"] > 0) {
        // To prevent users from uploading whatever file they want (very prone to security issues),
        // we limit the supported MIME types to JPEG, SVG, GIF and PNG.
        $supported_types = ["image/jpeg", "image/svg+xml", "image/gif", "image/png"];

        // Prevent users from hogging bandwidth by uploading files greater than 10MB.
        if ($_FILES["image"]["size"] > 10485760) {
            $success = false;
            $status = "Images larger than 10 MB in size are not allowed.";
        } else if (!in_array($_FILES["image"]["type"], $supported_types)) {
            $success = false;
            $status = "Image uploaded is not an accepted file type. Please upload a JPEG, PNG, GIF or SVG image.";
        } else if ($_FILES["image"]["error"] != UPLOAD_ERR_OK) {
            $success = false;
            $status = "Image upload failed, error code: " . $_FILES["image"]["error"];
        } else {
            // If the image meets all the requirements, move it from the temporary PHP location to our
            // img folder for long-term storage. The original name is preserved as it doesn't matter,
            // its path (including name) is saved in the database.
            if (move_uploaded_file($_FILES["image"]["tmp_name"], "img/" . basename($_FILES["image"]["name"]))) {
                $list["imgpath"] = "img/" . basename($_FILES["image"]["name"]);
            }
        }
    }
    
    if (!empty($list) && $success !== false) {
        $result = $item->update_info($list);

        if ($result != ItemRtn::Success) {
            $status = "Failed to update product information, error code: " . $result;
            $success = false;
        } else {
            $status = "Successfully updated product information.";
            $success = true;
        }
    }
} else if (isset($_POST["add"])) {
    // Since each field in the add new item form is required, we can just work
    // with all the field variables immediately.
    $name = htmlspecialchars($_POST["name"]);
    $description = htmlspecialchars($_POST["description"]);
    $manufacturer = htmlspecialchars($_POST["manufacturer"]);
    $price = floatval(htmlspecialchars($_POST["price"]));
    $quantity = intval(htmlspecialchars($_POST["quantity"]));
    $category = intval(htmlspecialchars($_POST["category"]));
    $imgpath = "";

    // Once again, we must handle image uploads, exactly as done above in the update
    // section.
    if (isset($_FILES["image"])) {
        $supported_types = ["image/jpeg", "image/svg+xml", "image/gif", "image/png"];

        if ($_FILES["image"]["size"] > 10485760) {
            $success = false;
            $status = "Images larger than 10 MB in size are not allowed.";
        } else if (!in_array($_FILES["image"]["type"], $supported_types)) {
            $success = false;
            $status = "Image uploaded is not an accepted file type. Please upload a JPEG, PNG, GIF or SVG image.";
        } else if ($_FILES["image"]["error"] != UPLOAD_ERR_OK) {
            $success = false;
            $status = "Image upload failed, error code: " . $_FILES["image"]["error"];
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], "img/" . basename($_FILES["image"]["name"]))) {
                $imgpath = "img/" . basename($_FILES["image"]["name"]);
            }
        }
    }

    if ($category == 0) {
        $category = 1;
    }

    if ($price > 0 && $quantity > 0 && $imgpath != "") {
        $result = Item::add_item($name, $description, $manufacturer, $price, $quantity, $category, $imgpath);

        if ($result != ItemRtn::Success) {
            $status = "Failed to add new item, error code: " . $result;
            $success = false;
        } else {
            $status = "Successfully added new product.";
            $success = true;
        }
    }
} else if (isset($_POST["delete"])) {
    // The delete button has no extra fields, so we can just delete the item.
    $result = $item->delete();

    if ($result != ItemRtn::Success) {
        $status = "Could not delete product, error code: " . $result;
        $success = false;
    } else {
        $status = "Product successfully deleted.";
        $success = true;
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

                    // Since the admin panel is only accessible to admins,
                    // there is no need for alternate menus.
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

                // The sidebar shouldn't have an active link to its own page,
                // so since the adminproduct page is used for two separate
                // parts of the sidebar, we change the sidebar output depending
                // on if we're editing an existing item, or making a new one.
                if ($item != null) {
                    echo "<a href = \"admin.php\">Overview</a> <br> <a href = \"admin.php?orders\">View Orders</a> <br> <a href = \"admin.php?users\">Manage Users</a> <br> Manage Products <br> <a href = \"?\">Add New Product</a>";
                } else {
                    echo "<a href = \"admin.php\">Overview</a> <br> <a href = \"admin.php?orders\">View Orders</a> <br> <a href = \"admin.php?users\">Manage Users</a> <br> <a href = \"admin.php?products\">Manage Products</a> <br> Add New Product";
                }    

                ?>
            </div>
            <div id = "content">
                <?php

                if ($item != null) {
                    echo "<h3>Edit Item</h3>";
                    if ($success != null) {
                        if ($success) {
                            echo "<div class = \"successmsg\">" . $status . "</div>";
                        } else {
                            echo "<div class = \"errormsg\">" . $status . "</div>";
                        }
                    }

                    // If we deleted the product, we shouldn't try to access its information,
                    // as it will cause errors.
                    if ($status != "Product successfully deleted.") {
                        // To allow for inputs to start with the values of their current column data
                        // in the database, we output the entire form with PHP.
                        echo "<form method = \"post\" enctype = \"multipart/form-data\">";
                        echo "<label for = \"name\">Item Name</label>";
                        echo "<input type = \"text\" name = \"name\" value = \"" . $item->name . "\">";
                        echo "<label for = \"description\">Description</label>";
                        echo "<textarea name=\"description\" rows=\"4\" cols=\"50\">" . $item->description . "</textarea>";
                        echo "<label for = \"manufacturer\">Manufacturer</label>";
                        echo "<input type = \"text\" name = \"manufacturer\" value = \"" . $item->manufacturer . "\">";
                        echo "<label for = \"lname\">Price</label>";
                        echo "<input type = \"text\" name = \"price\" value = \"" . $item->price . "\">";
                        echo "<label for = \"quantity\">Quantity</label>";
                        echo "<input type = \"text\" name = \"quantity\" value = \"" . $item->quantity . "\">";
                        echo "<select name = \"category\">";
                        echo "<option value = \"1\">Uncategorized</option>";

                        // Dynamically acquire all current categories and populate them
                        // in the dropdown so items can be assigned categories that 
                        // existed only after the item was created.
                        $categories = Item::get_categories();

                        foreach ($categories as $category) {
                            $selected = "";

                            if ($category["id"] == $item->category) {
                                $selected = "selected = \"selected\"";
                            }

                            echo "<option value = \"" . $category["id"] . "\"" . $selected . ">" . $category["name"] . "</option>";
                        }

                        echo "</select>";
                        echo "<br><label for = \"image\">Item Image Upload</label>";
                        echo "<input type = \"hidden\" name = \"MAX_FILE_SIZE\" value = \"10485760\">";
                        echo "<input type = \"file\" name = \"image\">";
                        echo "<input type = \"submit\" name = \"delete\" value = \"Delete Item\">";
                        echo "<input type = \"submit\" name = \"update\" value = \"Save Changes\"></form>";
                    }

                    $opts = "";

                    if (isset($_SESSION["lastpage"])) {
                        $opts .= "&page=" . $_SESSION["lastpage"];
                    }

                    echo "<a href = \"admin.php?products" . $opts . "\">Go back</a>";
                } else {
                    echo "<h3>New Item</h3>";
                    if ($success != null) {
                        if ($success) {
                            echo "<div class = \"successmsg\">" . $status . "</div>";
                        } else {
                            echo "<div class = \"errormsg\">" . $status . "</div>";
                        }
                    }

                    // Even though we have no input values to enter at the start,
                    // this form must also be outputted in PHP, since it should only
                    // show up if there is no item GET request given.
                    echo "<form method = \"post\" enctype = \"multipart/form-data\">";
                    echo "<label for = \"name\">Item Name</label>";
                    echo "<input type = \"text\" name = \"name\" required>";
                    echo "<label for = \"description\">Description</label>";
                    echo "<textarea name=\"description\" rows=\"4\" cols=\"50\" required></textarea>";
                    echo "<label for = \"manufacturer\">Manufacturer</label>";
                    echo "<input type = \"text\" name = \"manufacturer\" required>";
                    echo "<label for = \"lname\">Price</label>";
                    echo "<input type = \"text\" name = \"price\" required>";
                    echo "<label for = \"quantity\">Quantity</label>";
                    echo "<input type = \"text\" name = \"quantity\" required>";
                    echo "<select name = \"category\" required>";
                    echo "<option value = \"1\">Uncategorized</option>";

                    $categories = Item::get_categories();

                    foreach ($categories as $category) {
                        echo "<option value = \"" . $category["id"] . "\">" . $category["name"] . "</option>";
                    }

                    echo "</select>";
                    echo "<br><label for = \"image\">Item Image Upload</label>";
                    echo "<input type = \"file\" name = \"image\" required>";
                    echo "<input type = \"submit\" name = \"add\" value = \"Add Item\"></form>";
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