<?php
$common_path = realpath(dirname(__FILE__)."/../../lib/common.php");
include_once($common_path);

$variant_definitions = array(array("A", 14), array("B", 3), array("C", 6));

$allVariants = array();
$allVariantGroups = array();
foreach($variant_definitions as $variant) {
    $variantGroup = array();
    for ($i = 1; $i <= $variant[1]; $i++) {
        $allVariants[] = $variant[0] . $i;
        $variantGroup[] = $variant[0] . $i;
    }
    $allVariantGroups[] = $variantGroup;
}

$dictionaryFile="dictionary.html";

function getMySQLConnection() {
    $connection = mysql_connect('localhost', 'romni1334146623', 'ablaka');
    mysql_select_db("romni1334146623");
    return $connection;
}

function mysql_query_or_die($sql) {
    $result = mysql_query($sql);
    if (!$result) die("Chyba při vykonávání sql " . $sql . ": " . mysql_error());
    return $result;
}

?>