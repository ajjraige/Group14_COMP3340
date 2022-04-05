<?php

require_once "database.php";
require_once "user_class.php";

class ItemRtn {
    const Success = 0;
    const DBDisconnect = -1;
    const FailedQuery = -2;
    const InvalidParam = -3;
    const NoCartItem = -4;
}

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

    public function __construct($itemid, $name, $description, $manufacturer, $price, $quantity, $category) {
        $this->itemid = $itemid;
        $this->name = $name;
        $this->description = $description;
        $this->manufacturer = $manufacturer;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->category = $category;
    }

    public static function get_item_by_id($id) {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        $result = $db->query("SELECT * FROM ITEMS WHERE id=" . $id);

        if (!$result || $result->num_rows != 1) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $row = $result->fetch_row();
        $item = new Item($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6]);

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

        if ($option == ItemOpts::None) {
            $result = $db->query("SELECT * FROM ITEMS");

            if (!$result || $result->num_rows == 0) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        } else if ($option == ItemOpts::Category) {
            $result = $db->query("SELECT * FROM ITEMS WHERE category=" . $value);

            if (!$result || $result->num_rows == 0) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        } // ADD SEARCH FUNCTIONALITY HERE

        if ($page > 1 + $result->num_rows / 10 || $page < 1) {
            $db->close();
            return ItemRtn::InvalidParam;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $start = ($page - 1) * 10;
        $items = [];

        for ($i = $start; $i < count($data) && $i < $start + 10; $i++) {
            $item = new Item($data[$i]["id"], $data[$i]["name"], $data[$i]["description"], $data[$i]["manufacturer"], $data[$i]["price"], $data[$i]["quantity"], $data[$i]["category"]);
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

        $result = $db->query("SELECT ITEMS.id, ITEMS.name, ITEMS.description, ITEMS.manufacturer, ITEMS.price, CART_ITEMS.quantity FROM CART_ITEMS INNER JOIN ITEMS ON CART_ITEMS.item = ITEMS.id");

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

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

        for ($i = $start; $i < count($data) && $i < $end; $i++) {
            $item = new Item($data[$i]["id"], $data[$i]["name"], $data[$i]["description"], $data[$i]["manufacturer"], $data[$i]["price"], $data[$i]["quantity"], 0);
            array_push($items, $item);
        }

        $db->close();
        return $items;
    }

    public static function checkout($user) {
        if (!$user instanceof User) {
            return ItemRtn::InvalidParam;
        }

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

        $cur_time = time();
        $result = $db->query("INSERT INTO ORDERS VALUES(DEFAULT, " . $user->userid . ", 0, " . $cur_time . ")");

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $result = $db->query("SELECT * FROM ORDERS WHERE user= " . $user->userid . " AND timestamp=" . $cur_time);

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $row = $result->fetch_row();
        $orderid = $row[0];
        $price = 0;

        foreach($cart as $item) {
            $result = $db->query("INSERT INTO ORDER_ITEMS VALUES(" . $orderid . ", " . $item->itemid . ", " . $item->quantity . ")");
            $price += $item->price * $item->quantity;
        }

        $result = $db->query("UPDATE ORDERS SET cost=" . $price . "WHERE id=" . $orderid);

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $result = $db->query("DELETE FROM CART_ITEMS WHERE user=" . $user->userid);

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        $db->close();
        return ItemRtn::Success;
    }

    public static function add_item($name, $description, $manufacturer, $price, $quantity, $category) {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        $result = $db->query("INSERT INTO ITEMS VALUES (DEFAULT, '" . $name . "', '" . $description . "', '" . $manufacturer . "', " . $price . ", " . $quantity . ", " . $category . ")");

        echo $result;

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        return ItemRtn::Success;
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

        foreach ($list as $key => $entry) {
            if ($key != "category" && $key != "price" && $key != "quantity") {
                $query .= $key . "='" . $entry . "', ";
            } else {
                $query .= $key . "=" . $entry . ", ";
            }    
        }

        $query = rtrim($query, ", ");

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

        $db->close();
        return ItemRtn::Success;
    }

    public function delete() {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return ItemRtn::DBDisconnect;
        }

        $result = $db->query("DELETE FROM ITEMS WHERE id=" . $this->itemid);

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

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

        $result = $db->query("SELECT * FROM CART_ITEMS WHERE item=" . $this->itemid . " AND user=" . $user->userid);

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        } else if ($result->num_rows == 1) {
            $row = $result->fetch_row();

            $result = $db->query("UPDATE CART_ITEMS SET quantity=" . ($row[3] + $quantity) . " WHERE id=" . $row[0]);

            if (!$result) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        } else if ($result->num_rows == 0) {
            $result = $db->query("INSERT INTO CART_ITEMS VALUES(DEFAULT, '" . $this->itemid . "', '" . $user->userid . "', " . $quantity . ")");

            if (!$result) {
                $db->close();
                return ItemRtn::FailedQuery;
            }
        }

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

        $result = $db->query("SELECT * FROM CART_ITEMS WHERE item=" . $this->itemid . " AND user=" . $user->userid);

        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        } else if ($result->num_rows != 1) {
            $db->close();
            return ItemRtn::NoCartItem;
        }

        $row = $result->fetch_row();

        if ($row[3] <= $quantity) {
            $result = $db->query("DELETE FROM CART_ITEMS WHERE id=" . $row[0]);
        } else {
            $result = $db->query("UPDATE CART_ITEMS SET quantity=" . ($row[3] - $quantity) . " WHERE id=" . $row[0]);

        }
        
        if (!$result) {
            $db->close();
            return ItemRtn::FailedQuery;
        }

        return ItemRtn::Success;   
    }
}

?>