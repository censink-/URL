<?php
include_once 'config.php';
$url = null;
$id = 0;
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($db, $_GET['id']);
    $geturlquery = mysqli_query($db, "SELECT * FROM urls WHERE id = " . $id . " LIMIT 1");
    $geturl = mysqli_fetch_assoc($geturlquery);
    $url = $geturl['url'];
    $active = $geturl['active'];
}
if ($url == null || !$active) {
    $id = 0;
    $getdefaultquery = mysqli_query($db, "SELECT value FROM settings WHERE setting = 'default_url';");
    if (!$getdefaultquery) {
        echo "Couldn't find that redirect, and no default url was provided. Sorry!";
        exit;
    } else {
        $getdefault = mysqli_fetch_assoc($getdefaultquery);
        $default = $getdefault['value'];
    }
    $url = $default;
}
$ip = $_SERVER['REMOTE_ADDR'];
$setclickquery = mysqli_query($db, "INSERT INTO visits VALUES (null, '" . $ip . "', null, " . $id . ");");
if (!$setclickquery) {
    die(mysqli_error($db));
}
header("Location: " . $url);
exit;