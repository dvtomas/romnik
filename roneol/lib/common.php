<?php
$common_path = realpath(dirname(__FILE__)."/../../lib/common.php");
include_once($common_path);

$dictionaryFile="dictionary.html";

function getMySQLConnection() {
    $connection = mysql_connect('localhost', 'romnik', 'ablaka');
    mysql_select_db("roneol");
    return $connection;
}

?>