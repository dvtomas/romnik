<?php
include_once("common.php");

// result of the export
$wordsAndVariants = array();
$paragraphs = array();

// Helper variables. Yup, global variables are ugly, but I try to keep the whole thing as simple
$currentParagraphIndex = 0;
$lastWord = NULL;

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

function traverseTranslationParagraph($node)
{
    global $allVariants;
    global $lastWord;
    global $wordsAndVariants;
    global $currentParagraphIndex;

    if ($node instanceof DOMText) {
    }
    else {
        if ($node->getAttribute("style") == "color:#000070;") {
            $currentWord = $node->nodeValue;
            if (!is_null($lastWord)) {
                warn("Poslední slovo nemělo žádné variety a nebude zaindexováno. Poslední slovo = " . $lastWord . ", současné slovo = " . $currentWord . "<br />");
            }
            $lastWord = $currentWord;
        }
        elseif (endsWith($node->getAttribute("style"), "font-size:8pt;")) {
            if (!is_null($lastWord)) {
                $raw_variants = preg_split("/[  ]/", $node->nodeValue); // ordinary and non-breakable space
                $variants = array();
                foreach ($raw_variants as $raw_variant) {
                    $variant = trim($raw_variant, "  "); // The last space is actualy U+00A0, nbsp
                    if ($variant == "")
                        continue;
                    if (!in_array($variant, $allVariants))
                        warn("Neznámá varieta '" . $variant . "' ve slově '" . $lastWord . "'.");
                    else
                        $variants[] = $variant;
                }
                $wordsAndVariants[] = array(
                    "word" => $lastWord,
                    "ascii" => utf8ToAscii($lastWord),
                    "paragraph_id" => $currentParagraphIndex,
                    "variants" => $variants);
                $lastWord = NULL;
            }
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
    global $dictionaryFile;
    global $allVariants;
    global $wordsAndVariants;
    global $paragraphs;

    echo "<h3>Definované variety</h3>";
    d($allVariants);
    echo "<h3>Naindexované položky pro vyhledávání</h3>";
    d($wordsAndVariants);
    echo "<h3>Přeložené texty</h3>";
    d($paragraphs);
}

function populateDatabase()
{
    global $paragraphs;
    global $wordsAndVariants;
    global $allVariants;

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
    function variantToVariantDefinition($variant)
    {
        return $variant . " BOOLEAN ";
    }

    ;
    $sql = "
        CREATE TABLE WORDS
        (
          WORD VARCHAR(100),
          ASCII VARCHAR(100),
          PARAGRAPH_ID INTEGER,
          " . implode(", ", array_map("variantToVariantDefinition", $allVariants)) . ",
          PRIMARY KEY (WORD)
        )";
    mysql_query_or_die($sql);
    foreach ($wordsAndVariants as $wordAndVariants) {
        $variantValues = array();
        foreach ($allVariants as $variant) {
            if (in_array($variant, $wordAndVariants["variants"]))
                $variantValues[] = "1";
            else
                $variantValues[] = "0";
        }
        $sql = "INSERT INTO WORDS VALUES('" .
            mysql_real_escape_string($wordAndVariants["word"]) . "', '" .
            mysql_real_escape_string($wordAndVariants["ascii"]) . "', " .
            mysql_real_escape_string($wordAndVariants["paragraph_id"]) . ", " .
            implode(", ", $variantValues) .
            ")";

        mysql_query_or_die($sql);
    }

    /*    $result = mysql_query_or_die("SELECT * FROM WORDS");
    while ($row = mysql_fetch_assoc($result))
        d($row);
    mysql_free_result($result);*/
}

?>