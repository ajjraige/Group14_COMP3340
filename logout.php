<?php

require_once "user_class.php";

$user = new User();
$result = $user->get_session();

if ($result == UserRtn::Success) {
    $user->logout();
}

header("Location: home.php");
exit;

?>