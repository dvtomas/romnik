<?php
include("dd.php");
ini_set('display_errors', 'On');
setlocale(LC_ALL, 'cs_CZ.utf8');

$variants = array(array("A", 14), array("B", 3), array("C", 6));

$allVariants = array();
foreach($variants as $variant)
    for ($i = 1; $i <= $variant[1]; $i++)
        $allVariants[] = $variant[0] . $i;

$dictionaryFile="data/dictionary.html";

?>
