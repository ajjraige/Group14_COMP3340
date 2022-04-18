<?php

class DBConnection {
    private $mysqli;

    public function __construct() {
        // Make sure errors are reported.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // The mysqli_report function now means any error (even non-fatal ones)
        // from the database will throw an exception, so we must wrap our
        // creation of a new database connection in a try-catch statement.
        try {
            $this->mysqli = new mysqli("localhost", "killen2_3340finalproj", "P0pl@rTr33!!!", "killen2_3340finalproj");
            $this->active = true;

        } catch (mysqli_sql_exception $e) {
            $this->mysqli = null;
        }
    }
    
    public function is_valid() {
        // Since mysqli is private, for other files to know if the connection
        // succeeded or not, this function must be used to check.
        if ($this->mysqli == null) {
            return false;
        }

        return true;
    }

    public function escape($str) {
        // Similarly, an escape function is needed, as the mysqli variable cannot
        // be used to call real_escape_string directly.
        if ($this->mysqli == null) {
            return false;
        }

        return $this->mysqli->real_escape_string($str);
    }

    public function query($query) {
        if (!$this->is_valid()) {
            return false;
        }

        $result = false;

        // Catch any errors and simply return false if they happen.
        try {
            $result = $this->mysqli->query($query);
        } catch (mysqli_sql_exception $e) {
            $result = false;
        }

        return $result;
    }

    public function ping() {
        // Ping function to get around the private mysqli variable.
        if (!$this->is_valid()) {
            return false;
        }

        return $this->mysqli->ping();
    }

    public function close() {
        // Close function to get around the private mysqli variable.
        if (!$this->is_valid()) {
            return false;
        }

        return $this->mysqli->close();
    }
}
   
?>