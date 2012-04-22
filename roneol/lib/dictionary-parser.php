<?php
include_once("common.php");

// result of the export
$paragraphs = array();
$words = array();

// Helper variables. Yup, global variables are ugly, but I try to keep the whole thing as simple
$currentParagraphIndex = 0;


function getTranslationParagraphs($document)
{
    $result = array();
    $body = $document->getElementsByTagName("body")->item(0);
    foreach ($body->childNodes as $node) {
        if ($node instanceof DOMElement && 0 == strcasecmp($node->tagName, "p") && $node->hasAttribute("dir")) {
            $result[] = $node;
        }
    }
    return $result;
}

function addWord($word) {
    global $currentParagraphIndex;
    global $words;
    $words[] = array(
        "word" => $word,
        "ascii" => utf8ToAscii($word),
        "paragraph_id" => $currentParagraphIndex
    );
}

function traverseTranslationParagraph($node)
{
    global $allVariants;
    global $lastWord;
    global $wordsAndVariants;
    $currentWordAndTranslations = array();

    if ($node instanceof DOMText) {
    }
    else {
        if ($node->getAttribute("style") == "color:#0080C0;") {
            addWord($node->nodeValue);
        }
        elseif (endsWith($node->getAttribute("style"), "color:#DD0000;")) {
            foreach(explode(",", $node->nodeValue) as $rawTranslation)
                addWord(trim($rawTranslation));
        }
        $children = $node->childNodes;
        if (!is_null($children)) {
            for ($i = 0; $i < $children->length; $i++) {
                $recursive_result = traverseTranslationParagraph($children->item($i));
            }
        }
    }
}

/**
 * Parses a given file. The result is in the global variables $wordsAndVariants and $paragraphs.
 * @param $filename Name of the file to index
 */
function parseDictionaryFile($filename)
{
    global $currentParagraphIndex;
    global $paragraphs;

    echo "<hr />";
    echo "<h2>Indexování souboru s exportem</h2>";
    echo "<h3>Varování při indexování, pokud nějaká</h3>";

    $doc = new DOMDocument();
    $doc->loadHTMLFile($filename);
    foreach (getTranslationParagraphs($doc) as $paragraph) {
        $currentParagraphIndex++;
        $paragraphs[] = array("index" => $currentParagraphIndex, "html" => getInnerHtml($paragraph));
        traverseTranslationParagraph($paragraph);
    }
}

function dumpParseResult()
{
    global $paragraphs;
    global $words;

    echo "<h3>Naindexované položky pro vyhledávání</h3>";
    d($words);
    echo "<h3>Přeložené texty</h3>";
    d($paragraphs);
}

function populateDatabase()
{
    global $paragraphs;
    global $words;

    $connection = getMySQLConnection();

    // PARAGRAPHS
    mysql_query("DROP TABLE PARAGRAPHS");
    $sql = "
        CREATE TABLE PARAGRAPHS
        (
          ID INT,
          PARAGRAPH TEXT,
          PRIMARY KEY (ID)
        )";
    mysql_query_or_die($sql);
    foreach ($paragraphs as $paragraph) {
        $sql = "INSERT INTO PARAGRAPHS VALUES(" . mysql_real_escape_string($paragraph["index"]) . ", '" . mysql_real_escape_string($paragraph["html"]) . "')";
        mysql_query_or_die($sql);
    }

    // WORDS AND VARIANTS
    mysql_query("DROP TABLE WORDS");
    $sql = "
        CREATE TABLE WORDS
        (
          WORD VARCHAR(100),
          ASCII VARCHAR(100),
          PARAGRAPH_ID INTEGER
        )";
    mysql_query_or_die($sql);
    foreach ($words as $word) {
        $sql = "INSERT INTO WORDS VALUES('" .
            mysql_real_escape_string($word["word"]) . "', '" .
            mysql_real_escape_string($word["ascii"]) . "', " .
            mysql_real_escape_string($word["paragraph_id"]) .
            ")";

        mysql_query_or_die($sql);
    }
/*
    $result = mysql_query_or_die("SELECT * FROM WORDS");
    while ($row = mysql_fetch_assoc($result))
        d($row);
    mysql_free_result($result);*/
}

?>