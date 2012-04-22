<?php
include_once("dd.php");
ini_set('display_errors', 'On');
setlocale(LC_ALL, 'cs_CZ.utf8');

function utf8ToAscii($str) {
    return iconv('utf8', 'ascii//TRANSLIT', $str);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    $start = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

function getInnerHtml($node)
{
    $innerHTML = '';
    $children = $node->childNodes;
    foreach ($children as $child) {
        $innerHTML .= $child->ownerDocument->saveXML($child);
    }

    return $innerHTML;
}

function warn($message) {
    echo '<div class="error">' . $message . "</div><br />";
}

function mysql_query_or_die($sql) {
    $result = mysql_query($sql);
    if (!$result) die("Chyba při vykonávání sql " . $sql . ": " . mysql_error());
    return $result;
}

?>
