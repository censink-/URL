<?php
$db_host = "localhost"; //usually 'localhost'
$db_name = "url"; //database name
$db_user = "root"; //database user that has access to given database
$db_pass = "1337"; //password of given database user

$external_ip = "80.114.84.147"; //external ip of server (or your home router)

//END OF SETTINGS

$db = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$db) {
    die("Database connection failed, sorry!");
}