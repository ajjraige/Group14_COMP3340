<?php

require_once "user_class.php";

$user = new User();
$result = $user->get_session();

if ($result == UserRtn::Success) {
    $status = "Success!";
    $user->logout();
} else {
    $status = "Not success, code: " . $result;
}

header("Location: home.php");
exit;

?>