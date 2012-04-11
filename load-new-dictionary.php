<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!-- Consider adding a manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!-->
<html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">

    <!-- Use the .htaccess and remove these lines to avoid edge case issues.
 More info: h5bp.com/i/378 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>Romsko-český slovník - Nahrávání nového slovníku</title>
    <meta name="description" content="">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <link rel="stylesheet" href="css/style.css">

    <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

    <!-- All JavaScript at the bottom, except this Modernizr build.
Modernizr enables HTML5 elements & feature detects for optimal performance.
Create your own custom Modernizr build: www.modernizr.com/download/ -->
    <script src="js/libs/modernizr-2.5.3.min.js"></script>
</head>
<body>
<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6.
chromium.org/developers/how-tos/chrome-frame-getting-started -->
<!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a
    different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a>
    to experience this site.</p><![endif]-->
<header>

</header>
<div role="main">
    <?php
    include("common.php");

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

    function getInnerHtml($node)
    {
        $innerHTML = '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML($child);
        }

        return $innerHTML;
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        $start = $length * -1; //negative
        return (substr($haystack, $start) === $needle);
    }

    // Yup, global variables are ugly, but I try to keep the whole thing as simple
    $currentParagraphIndex = 0;
    $lastWord = NULL;

    $wordsAndVariants = array();
    $paragraphs = array();

    function traverseTranslationParagraph($node)
    {
        global $lastWord;
        global $wordsAndVariants;
        global $currentParagraphIndex;

        if ($node instanceof DOMText) {
        }
        else {
            if ($node->getAttribute("style") == "color:#000070;") {
                $currentWord = $node->nodeValue;
                if (!is_null($lastWord)) {
                    echo "Warning: last word didn't have any variants, and will not be indexed. Last word = " . $lastWord . ", current word = " . $currentWord . "<br />";
                }
                $lastWord = $currentWord;
            }
            elseif (endsWith($node->getAttribute("style"), "font-size:8pt;")) {
                if (!is_null($lastWord)) {
                    $wordsAndVariants[] = array(
                        "word" => $lastWord,
                        "ascii" => iconv('utf8', 'ascii//TRANSLIT', $lastWord),
                        "paragraph_index" => $currentParagraphIndex,
                        "variants" => explode(" ", $node->nodeValue));
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
    function parseFileIndex($filename) {
        global $currentParagraphIndex;
        global $paragraphs;

        $doc = new DOMDocument();
        $doc->loadHTMLFile($filename);
        foreach (getTranslationParagraphs($doc) as $paragraph) {
            $currentParagraphIndex++;
            $paragraphs[] = array("index" => $currentParagraphIndex, "html" => getInnerHtml($paragraph));
            traverseTranslationParagraph($paragraph);
        }
    }

    echo "<h2>Indexuji položky pro vyhledávání</h2>";
    global $dictionaryFile;
    parseFileIndex($dictionaryFile);
    echo "<h2>Definované variety</h2>";
    d($allVariants);
    echo "<h2>Naindexované položky pro vyhledávání</h2>";
    d($wordsAndVariants);
    echo "<h2>Přeložené texty</h2>";
    d($paragraphs);
    echo "<br />-o-<br />";
?>
</div>
<footer>

</footer>


    <!-- JavaScript at the bottom for fast page loading -->

    <!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.1.min.js"><\/script>')</script>

    <!-- scripts concatenated and minified via build script -->
    <script src="js/plugins.js"></script>
    <script src="js/script.js"></script>
    <!-- end scripts -->

    <!-- Asynchronous Google Analytics snippet. Change UA-XXXXX-X to be your site's ID.
   mathiasbynens.be/notes/async-analytics-snippet -->
    <script>
        var _gaq = [
            ['_setAccount', 'UA-XXXXX-X'],
            ['_trackPageview']
        ];
        (function (d, t) {
            var g = d.createElement(t), s = d.getElementsByTagName(t)[0];
            g.src = ('https:' == location.protocol ? '//ssl' : '//www') + '.google-analytics.com/ga.js';
            s.parentNode.insertBefore(g, s)
        }(document, 'script'));
    </script>
</body>
</html>