<?php

require_once "database.php";
require_once "user_class.php";
require_once "item_class.php";

class OrderRtn {
    const Success = 0;
    const DBDisconnect = -1;
    const FailedQuery = -2;
    const InvalidParam = -3;
}

class Order {
    public $orderid;
    public $userid;
    public $cost;
    public $timestamp;
    public $items = [];

    public function __construct($orderid, $userid, $cost, $timestamp) {
        $this->orderid = $orderid;
        $this->userid = $userid;
        $this->cost = $cost;
        $this->timestamp = $timestamp;
    }

    public static function get_order_by_id($id, $user, $page) {
        if (!$user instanceof User) {
            return OrderRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return OrderRtn::DBDisconnect;
        }

        $result = $db->query("SELECT * FROM ORDERS WHERE id=" . $id . " AND user=" . $user->userid);

        if (!$result || $result->num_rows != 1) {
            $db->close();
            return OrderRtn::FailedQuery;
        }

        $row = $result->fetch_row();
        $order = new Order($row[0], $user->userid, $row[2], $row[3]);

        $result = $db->query("SELECT * FROM ORDER_ITEMS WHERE orderid=" . $id);

        if (!$result) {
            $db->close();
            return OrderRtn::FailedQuery;
        }

        if ($page > 1 + $result->num_rows / 10 || $page < 0) {
            $db->close();
            return OrderRtn::InvalidParam;
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
            $item = Item::get_item_by_id($data[$i]["item"]);
            $item->quantity = $data[$i]["quantity"];
            array_push($order->items, $item);
        }

        $db->close();
        return $order;
    }

    public static function get_orders($user, $page, $admin) {
        if (!$user instanceof User || gettype($admin) != "boolean") {
            return OrderRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return OrderRtn::DBDisconnect;
        }

        $query = "SELECT * FROM ORDERS WHERE user=" . $user->userid;

        if ($admin) {
            $query = "SELECT * FROM ORDERS";
        }

        $result = $db->query($query);

        if (!$result) {
            $db->close();
            return OrderRtn::FailedQuery;
        }

        if ($page > 1 + $result->num_rows / 10 || $page < 0) {
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

        for ($i = $start; $i < count($data) && $i < $end; $i++) {
            $order = new Order($data[$i]["id"], $data[$i]["user"], $data[$i]["cost"], $data[$i]["timestamp"]);
            array_push($orders, $order);
        }

        $db->close();
        return $orders;
    }
}

?>