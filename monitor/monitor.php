<?php

$site_active = false;
$db_active = false;

// We can't try and use a client-side solution with something like a GET request to the
// other page because of the same-origin policy, which prevents requests between resources
// that aren't of the same origin, since it's a vector for XSS. Instead, we must ping the 
// address using sockets.
$start = microtime(true);
$file = fsockopen("https://killen2.myweb.cs.uwindsor.ca/flowerpot/home.php", 443, $errno, $errstr, 10);
$end = microtime(true);

$sitetime = ($end - $start) * 1000;
$sitemsg = "";

if (file) {
    fclose($file);
    $site_active = true;
    $sitemsg = "(Response time: " . round($sitetime, 2) . " ms)";
}

// Connect to the database and try to ping it. Since we don't have access to a web server outside
// of the uWindsor DirectAdmin hosting, we still connect using localhost. Connecting to the 
// database outside of the DirectAdmin hosting would also be a nightmare, however, since it is 
// at the very least required to connect using the school's VPN, which does not have a linux binary
// install, and at worst, the MySQL server that is run completely disallows connections that aren't local.
$start = microtime(true);
$mysqli = new mysqli("localhost", "killen2_3340finalproj", "P0pl@rTr33!!!", "killen2_3340finalproj");
$result = $mysqli->ping();
$end = microtime(true);

$dbtime = ($end - $start) * 1000;
$dbmsg = "";

if ($result) {
    $db_active = true;
    $dbmsg = "(Response time: " . round($dbtime, 2) . " ms)";
}

?>

<!DOCTYPE html>
    <head>
        <meta charset = 'utf-8'>
        <title>Flowerpot Status</title>
        <link rel = "stylesheet" href = "style.css">
    </head>
    <body>
    <div>
        <h2>Flowerpot Site Status</h2>

        <?php

        echo "<b>Web Server status:</b><br><strong class = \"" . ($site_active ? "green" : "red") . "\">" . ($site_active ? "ONLINE" : "OFFLINE") . "</strong> " . $sitemsg . "<br><br>";
        echo "<b>Database status:</b><br><strong class = \"" . ($db_active ? "green" : "red") . "\">" . ($db_active ? "ONLINE" : "OFFLINE") . "</strong> " . $dbmsg;

        ?>
    </div>
    </body>
</html>