<?php

require_once "database.php";
require_once "user_class.php";

// This class simply holds constant values for readable error codes in Item functions.
class ItemRtn {
    const Success = 0;
    const DBDisconnect = -1;
    const FailedQuery = -2;
    const InvalidParam = -3;
    const NoCartItem = -4;
    const InsufficientStock = -5;
}

// This class simply holds constant values for the get_items function, to allow
// for it to retrieve items only in a specific category or search criteria.
class ItemOpts {
    const None = 0;
    const Category = -1;
    const Search = -2;
}

class Item {
    public $itemid;
    public $name;
    public $description;
    public $manufacturer;
    public $price;
    public $quantity;
    public $category;
    public $imgpath;
    public $is_cart_item;

    // Simple constructor to give all the item's members a value upon instantiation.
    public function __construct($itemid, $name, $description, $manufacturer, $price, $quantity, $category, $imgpath, $is_cart_item) {
        $this->itemid = $itemid;
        $this->name = $name;
        $this->description = $description;
        $this->manufacturer = $manufacturer;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->category = $category;
        $this->imgpath = $imgpath;
        $this->is_cart_item = $is_cart_item;
    }

    public static function get_item_by_id($id) {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Attempt to find the item by item id.
        $result = $db->query("SELECT * FROM ITEMS WHERE id=" . $db->escape($id));

        if (!$result || $result->num_rows != 1) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        // If successful, create a new item with all the row data and return it.
        $row = $result->fetch_row();
        $item = new Item($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], false);

        $db->close();
        return $item;
    }

    public static function get_items($option, $value, $page) {
        if ($option > ItemOpts::None || $option < ItemOpts::Search) {
            return ItemRtn::InvalidParam;
        }

        $db = new DBConnection();
        $result;

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // The query used to retrieve items depends upon the limiting criteria
        // for the items, where an extra clause for searching by category or
        // by search criteria must be used in the query.
        if ($option == ItemOpts::None) {
            $result = $db->query("SELECT * FROM ITEMS");

            if (!$result || $result->num_rows == 0) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        } else if ($option == ItemOpts::Category) {
            $result = $db->query("SELECT * FROM ITEMS WHERE category=" . $db->escape($value));

            if (!$result || $result->num_rows == 0) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        } else if ($option == ItemOpts::Search) {
            $result = $db->query("SELECT * FROM ITEMS WHERE name LIKE '%" . $db->escape($value) . "%' OR description LIKE '%" . $db->escape($value) . "%'");

            if (!$result || $result->num_rows == 0) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        }

        // Make sure the page is valid with the data found.
        if ($page >= 1 + $result->num_rows / 10 || $page < 1) {
            $db->close();
            return ItemRtn::InvalidParam;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $start = ($page - 1) * 10;
        $items = [];

        // Populate the items list with the items at that page.
        for ($i = $start; $i < count($data) && $i < $start + 10; $i++) {
            $item = new Item($data[$i]["id"], $data[$i]["name"], $data[$i]["description"], $data[$i]["manufacturer"], $data[$i]["price"], $data[$i]["quantity"], $data[$i]["category"], $data[$i]["imgpath"], false);
            array_push($items, $item);
        }

        $db->close();
        return $items;
    }

    public static function get_categories() {
        $db = new DBConnection();
        $result;

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        $result = $db->query("SELECT * FROM CATEGORIES");

        if (!$result || $result->num_rows == 0) {
            return ItemRtn::FailedQuery;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $list = [];

        foreach($data as $entry) {
            if ($entry["id"] != 1) {
                array_push($list, array("id"=>$entry["id"], "name"=>$entry["name"]));
            }         
        }

        $db->close();
        return $list;
    }

    public static function get_cart($user, $page) {
        if (!$user instanceof User) {
            return ItemRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Since we also need the actual item information (which isn't stored in CART_ITEMS),
        // an inner join is used to easily retrieve the actual item information in the ITEMS
        // table with each CART_ITEMS entry belonging to the user.
        $result = $db->query("SELECT ITEMS.id, ITEMS.name, ITEMS.description, ITEMS.manufacturer, ITEMS.price, ITEMS.imgpath, CART_ITEMS.quantity FROM CART_ITEMS INNER JOIN ITEMS ON CART_ITEMS.item = ITEMS.id");

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        // Make sure the page is valid.
        if ($page > 1 + $result->num_rows / 10 || $page < 0) {
            $db->close();
            return ItemRtn::InvalidParam;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $items = [];  
        $start;
        $end;

        if ($page == 0) {
            $start = 0;
            $end = count($data);
        } else {
            $start = ($page - 1) * 10;
            $end = $start + 10;
        }

        // Add the items in the given page to the items list.
        for ($i = $start; $i < count($data) && $i < $end; $i++) {
            $item = new Item($data[$i]["id"], $data[$i]["name"], $data[$i]["description"], $data[$i]["manufacturer"], $data[$i]["price"], $data[$i]["quantity"], 0, $data[$i]["imgpath"], true);
            array_push($items, $item);
        }

        $db->close();
        return $items;
    }

    public static function checkout($user) {
        if (!$user instanceof User) {
            return ItemRtn::InvalidParam;
        }

        // Make sure the cart is not empty before checking out.
        $cart = Item::get_cart($user, 0);
        if (gettype($cart) != "array") {
            return $cart;
        } else if (empty($cart)) {
            return ItemRtn::NoCartItem;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Make sure the amount to be purchased of each item is less or equal to
        // the amount of the item in stock.
        $result = $db->query("SELECT id, quantity FROM ITEMS WHERE id IN (SELECT item FROM CART_ITEMS WHERE user=" . $db->escape($user->userid) . ")");

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $items = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($cart as $item) {
            foreach ($items as $item2) {
                if ($item->itemid == $item2["id"]) {
                    if ($item->quantity > $item2["quantity"]) {
                        $db->close();
                        return ItemRtn::InsufficientStock;
                    }
                    break;
                }
            }
        }

        // Create the order entry in the ORDERS table with a starting price of 0, which will be updated
        // after the order items are added.
        $cur_time = time();
        $result = $db->query("INSERT INTO ORDERS VALUES(DEFAULT, " . $db->escape($user->userid) . ", 0, " . $db->escape($cur_time) . ", DEFAULT)");

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        // Since ids are auto_incremented, we don't know what the order id for our new order is, so we must find it based
        // on user id and timestamp, which should be unique enough.
        $result = $db->query("SELECT * FROM ORDERS WHERE user= " . $db->escape($user->userid) . " AND timestamp=" . $db->escape($cur_time));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $row = $result->fetch_row();
        $orderid = $row[0];
        $price = 0;

        // Now that we have our id, we add all the items of our order to their own ORDER_ITEMS entries.
        // The quantity of the item in the ITEMS table is also reduced by the amount purchased, and the
        // price of each order item (times its quantity) is summed up into the price variable.
        foreach($cart as $item) {
            $result = $db->query("INSERT INTO ORDER_ITEMS VALUES(" . $db->escape($orderid) . ", " . $db->escape($item->itemid) . ", " . $db->escape($item->quantity) . ")");
            $result = $db->query("UPDATE ITEMS SET quantity=quantity - " . $db->escape($item->quantity) . " WHERE id=" . $db->escape($item->itemid));
            $price += $item->price * $item->quantity;
        }

        // Failsafe in case somehow something failed before. The price really shouldn't be 0.
        if ($price == 0) {
            $price = $cart[0]->price * $cart[0]->quantity;
        }

        // Now we update our order entry with the total cost of the order.
        $result = $db->query("UPDATE ORDERS SET cost=" . $db->escape($price) . " WHERE id=" . $db->escape($orderid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        // And we empty the user's shopping cart.
        $result = $db->query("DELETE FROM CART_ITEMS WHERE user=" . $db->escape($user->userid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $db->close();
        return ItemRtn::Success;
    }

    public static function add_item($name, $description, $manufacturer, $price, $quantity, $category, $imgpath) {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        $result = $db->query("INSERT INTO ITEMS VALUES (DEFAULT, '" . $db->escape($name) . "', '" . $db->escape($description) . "', '" . $db->escape($manufacturer) . "', " . $db->escape($price) . ", " . $db->escape($quantity) . ", " . $db->escape($category) . ", '" . $db->escape($imgpath) . "')");

        echo $result;

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $db->close();
        return ItemRtn::Success;
    }

    public static function get_no_stock_items() {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Find all items that are out of stock.
        $result = $db->query("SELECT * FROM ITEMS WHERE quantity=0");

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $names = [];

        foreach ($data as $item) {
            array_push($names, $item["name"]);
        }

        $db->close();
        return $names;
    }

    public function update_info($list) {
        if (gettype($list) != "array") {
            return ItemRtn::InvalidParam;
        } else if (empty($list)) {
            return ItemRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        $query = "UPDATE ITEMS SET ";

        // Since we can't know for sure which columns were updated, and the list
        // given already uses the corresponding column name for the field as their
        // keys, we can build up the query ourselves using the key for the column
        // name, an =, and then the value for that key. These assignments must
        // be comma separated, so a check is done to make sure there is no 
        // trailing comma at the end.
        foreach ($list as $key => $entry) {
            if ($key != "category" && $key != "price" && $key != "quantity") {
                $query .= $db->escape($key) . "='" . $db->escape($entry) . "'";
            } else {
                $query .= $db->escape($key) . "=" . $db->escape($entry);
            }   

            if ($key !== array_key_last($list)) {
                $query .= ", ";
            }
        }

        $query .= " WHERE id=" . $this->itemid;

        $result = $db->query($query);

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        // Now that the update was successful, let's update the object's information.
        if (isset($list["name"])) {
            $this->name = $list["name"];
        }

        if (isset($list["description"])) {
            $this->description = $list["description"];
        }

        if (isset($list["manufacturer"])) {
            $this->manufacturer = $list["manufacturer"];
        }

        if (isset($list["price"])) {
            $this->price = $list["price"];
        }

        if (isset($list["quantity"])) {
            $this->quantity = $list["quantity"];
        }

        if (isset($list["category"])) {
            $this->category = $list["category"];
        }

        if (isset($list["imgpath"])) {
            // Delete the old image This causes some warnings in PHP due to 
            // the deletion of a file outside of its specified directory in
            // DirectAdmin, since the setting that doesn't allow it cannot be
            // changed. It's not ideal, but I have little choice but to let
            // that warning continue, as I don't want the img folder of the
            // site to fill up with unused images.
            unlink($this->imgpath);
            $this->imgpath = $list["imgpath"];
        }

        $db->close();
        return ItemRtn::Success;
    }

    public function delete() {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Get the image associated with the product and delete it first.
        $result = $db->query("SELECT imgpath FROM ITEMS WHERE id=" . $db->escape($this->itemid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $array = $result->fetch_all(MYSQLI_ASSOC);

        unlink($array["imgpath"]);

        // Then delete the item. This will cascade in the database to items
        // in the cart and also for orders. For the cart this is desirable,
        // but less so for the orders. However, implementing a way around this
        // would be quite tedious.
        $result = $db->query("DELETE FROM ITEMS WHERE id=" . $db->escape($this->itemid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $db->close();
        return ItemRtn::Success;
    }

    public function add_to_cart($user, $quantity) {
        if (!$user instanceof User || $quantity < 1) {
            return ItemRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Get the current quantity of the item in stock, so we can tell if 
        // we're trying to add more to our cart than the store can supply.
        $result = $db->query("SELECT quantity FROM ITEMS WHERE id=" . $db->escape($this->itemid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $itemqty = $data[0]["quantity"];

        // If the item is already in the cart, add the existing cart quantity
        // to what we're adding so we don't go over the item's stock quantity.
        // If not, we can just compare the stock quantity and the quantity we're
        // adding directly. If we go over the stock quantity, error out.
        if ($this->is_cart_item == true) {
            $result = $db->query("SELECT quantity FROM CART_ITEMS WHERE item=" . $db->escape($this->itemid));
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $cartqty = $data[0]["quantity"];

            if ($cartqty + $quantity > $itemqty) {
                $db->close();
                return ItemRtn::InsufficientStock;
            }
        } else {
            if ($quantity > $itemqty) {
                $db->close();
                return ItemRtn::InsufficientStock;
            }
        }

        // Check if the item's already in the cart.
        $result = $db->query("SELECT * FROM CART_ITEMS WHERE item=" . $db->escape($this->itemid) . " AND user=" . $db->escape($user->userid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        } else if ($result->num_rows == 1) {
            // If so, add the new quantity to the old one.
            $row = $result->fetch_row();

            $result = $db->query("UPDATE CART_ITEMS SET quantity=" . $db->escape($row[3] + $quantity) . " WHERE id=" . $db->escape($row[0]));

            if (!$result) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        } else if ($result->num_rows == 0) {
            // If not, create a new cart item entry for the item.
            $result = $db->query("INSERT INTO CART_ITEMS VALUES(DEFAULT, '" . $db->escape($this->itemid) . "', '" . $db->escape($user->userid) . "', " . $db->escape($quantity) . ")");

            if (!$result) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        }

        $db->close();
        return ItemRtn::Success;
    }

    public function remove_from_cart($user, $quantity) {
        if (!$user instanceof User || $quantity < 1) {
            return ItemRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Get the current cart item quantity.
        $result = $db->query("SELECT * FROM CART_ITEMS WHERE item=" . $db->escape($this->itemid) . " AND user=" . $db->escape($user->userid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        } else if ($result->num_rows != 1) {
            $db->close();
            return ItemRtn::NoCartItem;
        }

        $row = $result->fetch_row();

        // If the cart item quantity is reduced to 0 or less by this quantity subtraction,
        // delete the item from the cart. If not, the reduce the cart item quantity
        // by the amount listed.
        if ($row[3] <= $quantity) {
            $result = $db->query("DELETE FROM CART_ITEMS WHERE id=" . $db->escape($row[0]));
        } else {
            $result = $db->query("UPDATE CART_ITEMS SET quantity=" . $db->escape($row[3] - $quantity) . " WHERE id=" . $db->escape($row[0]));

        }
        
        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $db->close();
        return ItemRtn::Success;   
    }

    public function rate($user, $rating) {
        if (!$user instanceof User || $rating < 1 || $rating > 5) {
            return ItemRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Check to see if the rating already exists.
        $result = $db->query("SELECT * FROM RATINGS WHERE user=" . $db->escape($user->userid) . " AND item=" . $db->escape($this->itemid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $id = $data[0]["id"];
        
        // If not, create a new rating for the item and the user. If not,
        // update the old rating.
        if ($result->num_rows == 0) {
            $result = $db->query("INSERT INTO RATINGS VALUES (DEFAULT, " . $db->escape($this->itemid) . ", " . $db->escape($user->userid) . ", " . $db->escape($rating) . ")");

            if (!$result) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        } else {
            $result = $db->query("UPDATE RATINGS SET rating=" . $db->escape($rating) . " WHERE id=" . $db->escape($id));

            if (!$result) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        }

        $db->close();
        return ItemRtn::Success;
    }

    public function get_avg_rating() {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Select the average score of all the ratings for the item, and the number of ratings for the item.
        $result = $db->query("SELECT AVG(rating) avg_rating, COUNT(rating) num_ratings FROM RATINGS WHERE item=" . $db->escape($this->itemid));

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        // Store them in an associative array for easy handling of the data.
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $final = [];
        $final["avg"] = $data[0]["avg_rating"];
        $final["num"] = $data[0]["num_ratings"];

        $db->close();
        return $final;
    }

    public function get_user_rating($user) {
        if (!$user instanceof User) {
            return ItemRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        // Get the rating for the item given by the user.
        $result = $db->query("SELECT id, rating FROM RATINGS WHERE user=" . $db->escape($user->userid) . " AND item=" . $db->escape($this->itemid));

        if (!$result || $result->num_rows != 1) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $rating = null;
        
        if (is_array($data)) {
            $rating = $data[0]["rating"];
        }
        

        $db->close();
        return $rating;
    }
}

?>