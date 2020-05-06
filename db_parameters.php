<?php
/**
 * Created by PhpStorm.
 * User: Johan Griesel (Stratusolve (Pty) Ltd)
 * Date: 2017/02/18
 * Time: 10:07 AM
 */
session_start();
header("Content-Type: text/plain");
require("assets/php/controller.php");
$DBLinkObj = TestConfig::connectDatabase();
echo "Server version: ".$DBLinkObj->server_version."; ".$DBLinkObj->server_info."\r\n";
$SqlStr = "show variables";
$ResultObj = $DBLinkObj->query($SqlStr);
while ($row = mysqli_fetch_assoc($ResultObj)) {
    echo $row['Variable_name'].':'.$row['Value']."\r\n";
}
$DBLinkObj->close();













