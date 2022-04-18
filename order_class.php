<?php

require_once "database.php";
require_once "user_class.php";
require_once "item_class.php";

// This class simply holds constant values for readable error codes in Order functions.
class OrderRtn {
    const Success = 0;
    const DBDisconnect = -1;
    const FailedQuery = -2;
    const InvalidParam = -3;
    const NoOrders = -4;
}

class Order {
    public $orderid;
    public $userid;
    public $cost;
    public $timestamp;
    public $status;
    public $items = [];

    // Simple constructor to add as much information as possible to the object
    // on instantiation.
    public function __construct($orderid, $userid, $cost, $timestamp, $status) {
        $this->orderid = $orderid;
        $this->userid = $userid;
        $this->cost = $cost;
        $this->timestamp = $timestamp;
        $this->status = $status;
    }

    public static function get_order_by_id($id, $user, $page) {
        // If the user isn't NULL and isn't a valid user, error out.
        if (!$user instanceof User && $user != NULL) {
            return OrderRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return OrderRtn::DBDisconnect;
        }

        $query = "";

        // When the user is NULL, it's an admin getting the order, so no user
        // limitation should be needed.
        if ($user == NULL) {
            $query = "SELECT * FROM ORDERS WHERE id=" . $id;
        } else {
            $query = "SELECT * FROM ORDERS WHERE id=" . $id . " AND user=" . $user->userid;
        }

        $result = $db->query($query);

        if (!$result || $result->num_rows != 1) {
            $db->close();
            return OrderRtn::FailedQuery;
        }

        $row = $result->fetch_row();
        // Create an order object with the retrieved order information.
        $order = new Order($row[0], $user == NULL ? NULL : $user->userid, $row[2], $row[3], $row[4]);

        // Get the order's items from the ORDER_ITEMS table.
        $result = $db->query("SELECT * FROM ORDER_ITEMS WHERE orderid=" . $id);

        if (!$result) {
            $db->close();
            return OrderRtn::FailedQuery;
        }

        if ($page >= 1 + $result->num_rows / 10 || $page < 0) {
            $db->close();
            return OrderRtn::InvalidParam;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $items = [];  
        $start;
        $end;

        // Since we want to limit the amount of entries put on screen at once,
        // a paging system is implemented so a page number is given as an argument,
        // and if it is valid (within the range of the amount of items in the order),
        // it will retrieve the order items for that page.
        if ($page == 0) {
            $start = 0;
            $end = count($data);
        } else {
            $start = ($page - 1) * 10;
            $end = $start + 10;
        }

        // Populate the items member of the order object with the ORDER_ITEMS
        // entries.
        for ($i = $start; $i < count($data) && $i < $end; $i++) {
            $item = Item::get_item_by_id($data[$i]["item"]);
            $item->quantity = $data[$i]["quantity"];
            array_push($order->items, $item);
        }

        $db->close();
        return $order;
    }

    public static function get_orders($user, $page, $admin) {
        // If the user is not valid, or the admin boolean isn't right, then error out.
        if (!$user instanceof User || gettype($admin) != "boolean") {
            return OrderRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return OrderRtn::DBDisconnect;
        }

        $query = "SELECT * FROM ORDERS WHERE user=" . $user->userid;

        // Once again, if an admin is using this from the admin panel, we can
        // just show them all orders.
        if ($admin) {
            $query = "SELECT * FROM ORDERS";
        }

        $result = $db->query($query);

        if (!$result) {
            $db->close();
            return OrderRtn::FailedQuery;
        } else if ($result->num_rows == 0) {
            $db->close();
            return OrderRtn::NoOrders;
        }

        // Make sure the given page number is valid.
        if ($page >= 1 + $result->num_rows / 10 || $page < 0) {
            $db->close();
            return OrderRtn::InvalidParam;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $orders = [];  
        $start;
        $end;

        if ($page == 0) {
            $start = 0;
            $end = count($data);
        } else {
            $start = ($page - 1) * 10;
            $end = $start + 10;
        }

        // Populate the array of orders to be returned with the orders corresponding
        // to the given page number.
        for ($i = $start; $i < count($data) && $i < $end; $i++) {
            $order = new Order($data[$i]["id"], $data[$i]["user"], $data[$i]["cost"], $data[$i]["timestamp"], $data[$i]["status"]);
            array_push($orders, $order);
        }

        $db->close();
        return $orders;
    }

    public function update_status($new) {
        $statuses = ["RECEIVED", "PROCESSING", "SHIPPED", "DELIVERED"];

        // Make sure the given status is valid.
        if (!in_array($new, $statuses)) {
            return OrderRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return OrderRtn::DBDisconnect;
        }

        // Update the order entry's status column accordingly.
        $result = $db->query("UPDATE ORDERS SET status='" . $new . "' WHERE id=" . $this->orderid);

        if (!$result) {
            $db->close();
            return OrderRtn::FailedQuery;
        }

        // Update the object with the new status as well to reflect the change.
        $this->status = $new;

        $db->close();
        return OrderRtn::Success;
    }
}

?>