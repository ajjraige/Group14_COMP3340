<?php

class DBConnection {
    private $mysqli;

    public function __construct() {
        // Make sure errors are reported.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->mysqli = new mysqli("localhost", "killen2_3340finalproj", "P0pl@rTr33!!!", "killen2_3340finalproj");
            $this->active = true;

        } catch (mysqli_sql_exception $e) {
            $this->mysqli = null;
        }
    }
    
    public function is_valid() {
        if ($this->mysqli == null) {
            return false;
        }

        return true;
    }

    public function escape($str) {
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

        try {
            $result = $this->mysqli->query($query);
        } catch (mysqli_sql_exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    public function ping() {
        if (!$this->is_valid()) {
            return false;
        }

        return $this->mysqli->ping();
    }

    public function close() {
        if (!$this->is_valid()) {
            return false;
        }

        return $this->mysqli->close();
    }
}
   
?>