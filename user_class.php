<?php

require_once "database.php";

class UserRtn {
    const Success = 0;
    const DBDisconnect = -1;
    const FailedQuery = -2;
    const IncorrectUser = -3;
    const IncorrectPassword = -4;
    const ExpiredSession = -5;
    const InvalidSession = -6;
    const NoSession = -7;
    const NoLogin = -8;
    const IncorrectEmail = -9;
    const BannedUser = -10;
    const InvalidParam = -11;
}

class User {
    public $userid;
    public $username;
    public $fname;
    public $lname;
    public $address;
    public $zip;
    public $email;
    public $admin;
    public $banned;
    private $valid;
    
    public function __construct() {
        $this->userid = 0;
        $this->username = "";
        $this->fname = "";
        $this->lname = "";
        $this->address = "";
        $this->zip = "";
        $this->email = "";
        $this->admin = false;
        $this->banned = false;
        $this->valid = false;
    }

    public static function get_users($page) {
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return UserRtn::DBDisconnect;
        }

        $result = $db->query("SELECT * FROM USERS");

        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;
        }

        if ($page > 1 + $result->num_rows / 10 || $page < 0) {
            $db->close();
            return UserRtn::InvalidParam;
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $users = [];  
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
            $user = new User();
            $user->userid = $data[$i]["id"];
            $user->username = $data[$i]["username"];
            $user->fname = $data[$i]["first_name"];
            $user->lname = $data[$i]["last_name"];
            $user->address = $data[$i]["address"];
            $user->zip = $data[$i]["zip"];
            $user->email = $data[$i]["email"];
            $user->admin = $data[$i]["admin"];
            $user->banned = $data[$i]["banned"];
            array_push($users, $user);
        }

        $db->close();
        return $users;
    }

    public function new_session() {
        // Initialize new session.
        session_start();
        session_regenerate_id(true);
        $_SESSION["uid"] = $this->userid;
        // Since session ids are available to the client, let's use a separate
        // token stored inside the session to make sure nobody but our server 
        // can read it.
        $token = random_int(10000000, 99999999);
        $_SESSION["token"] = $token;
        // Make a new database connection.
        $db = new DBConnection();
        
        if (!$db->is_valid()) {
            return UserRtn::DBDisconnect;
        }

        // Insert the session into the database.
        $result = $db->query("UPDATE USERS SET token='" . password_hash($token, PASSWORD_DEFAULT) . "', expires=" . (time() + 15 * 60) . " WHERE id=" . $this->userid);

        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;
        }

        $db->close();
        return UserRtn::Success;
    }

    public function get_session() {
        // Resume session if it needs to.
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["uid"]) || !isset($_SESSION["token"])) {
            return UserRtn::NoSession;
        }

        // Make a new database connection.
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return UserRtn::DBDisconnect;
        }

        // Check if the current session token is in the database.
        $result = $db->query("SELECT * FROM USERS WHERE id='" . $_SESSION["uid"] . "'");
        $row = $result->fetch_row();

        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;
        } else if ($result->num_rows != 1) {
            $db->close();
            session_destroy();
            return UserRtn::IncorrectUser;
        }

        if ($row[9]) {
            $this->logout();
            $db->close();
            return UserRtn::BannedUser;
        }

        // Verify that the token is correct.
        if (password_verify($_SESSION["token"], $row[10])) {
            // Populate User object since we have a user with the right id and token.
            $this->userid = $row[0];
            $this->username = $row[1];
            $this->fname = $row[3];
            $this->lname = $row[4];
            $this->address = $row[5];
            $this->zip = $row[6];
            $this->email = $row[7];
            $this->admin = $row[8];
            $this->banned = $row[9];
            $this->valid = true;

            if ($row[11] > time()) {
                // Update the expiration time since we're clearly still
                // using the session.
                $newexp = time() + 15 * 60;
                $result = $db->query("UPDATE USERS SET expires=" . $newexp . " WHERE id='" . $this->userid . "'");
            } else {
                // Log the user out to clean up the invalid session.
                $this->logout();
                $db->close();
                return UserRtn::ExpiredSession;
            }
        } else {
            $this->logout();
            $db->close();
            return UserRtn::InvalidSession;
        }

        $db->close();
        return UserRtn::Success;
    }

    public function login($username, $password) {
        // Make a new database connection.
        $db = new DBConnection();

        // If the db connection failed, the login fails.
        if (!$db->is_valid()) {
            return UserRtn::DBDisconnect;
        }

        // Get the password hash from the database.
        $result = $db->query("SELECT * FROM USERS WHERE username='" . $db->escape($username) . "'");

        // Check if the query returns false (fail) because of connection issues
        // or a non-existant username.
        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;        
        } else if ($result->num_rows != 1) {
            $db->close();
            return UserRtn::IncorrectUser;
        }

        // Verify the password with the hash.
        $row = $result->fetch_row();

        if (!password_verify($password, $row[2])) {
            $db->close();
            return UserRtn::IncorrectPassword;
        }

        // Populate User object.
        $this->userid = $row[0];
        $this->username = $row[1];
        $this->fname = $row[3];
        $this->lname = $row[4];
        $this->address = $row[5];
        $this->zip = $row[6];
        $this->email = $row[7];
        $this->admin = $row[8];
        $this->banned = $row[9];
        $this->valid = true;

        if ($this->banned) {
            $db->close();
            return UserRtn::BannedUser;
        }

        // Make a session now that we have a valid session id.
        $result = $this->new_session();
        
        if ($result != UserRtn::Success) {
            $db->close();
            return $result;
        }

        $db->close();
        return UserRtn::Success;
    }

    public function register($username, $password, $fname, $lname, $addr, $zip, $email) {
        // Make a new database connection so we can check if the username
        // doesn't already exist.
        $db = new DBConnection();

        // If the db connection failed, the registration fails.
        if (!$db->is_valid()) {
            return UserRtn::DBDisconnect;
        }

        // Attempt to add user to the database.
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $result = $db->query("INSERT INTO USERS VALUES (DEFAULT, '" . $db->escape($username) . "', '" . $db->escape($hash) . "', '" . $db->escape($fname) . "', '" . $db->escape($lname) . "', '" . $db->escape($addr) . "', '" . $db->escape($zip) . "', '" . $db->escape($email) . "', 0, 0, DEFAULT, 0)");

        // Check if the query returns false (fail) because of connection issues
        // or an already existing user.
        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;        
        } else if (gettype($result) == "string") {
            $db->close();
            if (strpos($result, "username") !== false) {
                return UserRtn::IncorrectUser;
            } else if (strpos($result, "email") !== false) {
                return UserRtn::IncorrectEmail;
            } else {
                return UserRtn::FailedQuery;
            }
        }

        // Get user id from database.
        $result = $db->query("SELECT * FROM USERS WHERE username='" . $db->escape($username) . "'");

        if (!$result || $result->num_rows != 1) {
            $db->close();
            return UserRtn::FailedQuery;
        }

        $row = $result->fetch_row();

        // Populate User object.
        $this->userid = $row[0];
        $this->username = $username;
        $this->fname = $fname;
        $this->lname = $lname;
        $this->address = $addr;
        $this->zip = $zip;
        $this->email = $email;
        $this->admin = false;
        $this->banned = $row[9];

        $db->close();
        return UserRtn::Success;
    }

    public function logout() {
        // Make sure this user is actually logged in first.
        if ($_SESSION["uid"] != $this->userid || !$this->valid) {
            return UserRtn::NoLogin;
        }

        // Connect to database.
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return UserRtn::DBDisconnect;
        }

        // Check if the current session id is in the database.
        $result = $db->query("SELECT token, expires FROM USERS WHERE id='" . $this->userid . "'");
        $row = $result->fetch_row();

        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;
        } else if ($result->num_rows != 1 || $row[0] == "N/A" || $row[1] == 0) {
            $db->close();
            session_destroy();
            return UserRtn::InvalidSession;
        }

        // Remove session data from database.
        $result = $db->query("UPDATE USERS SET token=DEFAULT, expires=0 WHERE id=" . $this->userid);
        $db->close();

        // Destroy the current session.
        session_destroy();

        $this->valid = false;
        return UserRtn::Success;
    }

    public function update_password($old, $new) {
        if (!$this->valid) {
            return UserRtn::NoLogin;
        }

        // Connect to database.
        $db = new DBConnection();

        if (!$db->is_valid()) {
            return UserRtn::DBDisconnect;
        }

        // Get the password hash from the database.
        $result = $db->query("SELECT * FROM USERS WHERE id=" . $this->userid);

        // Check if the query returns false (fail) because of connection issues
        // or a non-existant username.
        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;        
        } else if ($result->num_rows != 1) {
            $db->close();
            return UserRtn::IncorrectUser;
        }

        // Verify the password with the hash.
        $row = $result->fetch_row();

        if (!password_verify($old, $row[2])) {
            $db->close();
            return UserRtn::IncorrectPassword;
        }

        // The old password matches, so now update the password to the new one.
        $hash = password_hash($new, PASSWORD_DEFAULT);

        $result = $db->query("UPDATE USERS SET hash='" . $hash . "' WHERE id=" . $this->userid);

        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;
        }

        $db->close();
        return UserRtn::Success;
    }

    public function update_billing($list) {
        if (gettype($list) != "array") {
            return UserRtn::InvalidParam;
        } else if (empty($list)) {
            return UserRtn::InvalidParam;
        }

        $db = new DBConnection();

        if (!$db->is_valid()) {
            return UserRtn::DBDisconnect;
        }

        $query = "UPDATE USERS SET ";

        foreach ($list as $key => $entry) {
            $query .= $key . "='" . $entry . "' ";
        }

        $query .= "WHERE id=" . $this->userid;

        $result = $db->query($query);

        if (!$result) {
            $db->close();
            return UserRtn::FailedQuery;
        }

        // Now that the update was successful, let's update the object's information.
        if (isset($list["first_name"])) {
            $this->fname = $list["first_name"];
        }

        if (isset($list["last_name"])) {
            $this->lname = $list["last_name"];
        }

        if (isset($list["address"])) {
            $this->address = $list["address"];
        }

        if (isset($list["zip"])) {
            $this->zip = $list["zip"];
        }

        if (isset($list["email"])) {
            $this->email = $list["email"];
        }

        $db->close();
        return UserRtn::Success;
    }
}

?>