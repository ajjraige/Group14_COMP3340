<?php

require_once "user_class.php";
require_once "item_class.php";

// Check if there is a user logged in.
$user = new User();
$result = $user->get_session();

if ($result == UserRtn::Success) {
    $status = "Success!";
} else {
    $status = "Not success, code: " . $result;
    $user = null;
}

// Only users are allowed to check out.
if ($user == null) {
    header("Location: store.php");
    exit;
} else {
    if (isset($_POST["success"])) {
        $result = Item::checkout($user);

        if ($result == ItemRtn::Success) {
            header("Location: store.php");
            exit;
        } else {
            echo "error code: " . $result;
        }
    } else if (isset($_POST["failed"])) {
        header("Location: cart.php");
        exit;
    }
}

?>

<!DOCTYPE html>

<html lang = "en">
    <head>
        <title>Checkout</title>
        <meta charset = "utf-8">
        <link rel = "stylesheet" href = "style.css">
    </head>
    <body>
        <div id = "authbox">
            <b>Checkout</b><br>
            <?php

            $items = Item::get_cart($user, 0);
            $price  = 0;

            if (gettype($items) == "array") {
                if (empty($items) && !isset($_POST["success"])) {
                    header("Location: cart.php");
                }

                foreach($items as $item) {
                    $price += $item->price * $item->quantity;
                }

                echo "Do you accept the $" . $price . " charge for this order?";
            } else {
                header("Location: cart.php");
            }

            ?>
            <br>
            <form method = "post">
                <input type = "submit" name = "success" value = "Yes">
                <input type = "submit" name = "failed" value = "No">
            </form>
        </div>
    </body>
</html>