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

    <title>Hello, world!</title>
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
    ini_set('display_errors', 'On');
    include("dd.php");

    function dom_to_array($root)
    {
        $result = array();

        if ($root->hasAttributes()) {
            $attrs = $root->attributes;
            foreach ($attrs as $i => $attr)
                $result[$attr->name] = $attr->value;
        }

        $children = $root->childNodes;
        if (is_null($children))
            return NULL;

        if ($children->length == 1) {
            $child = $children->item(0);

            if ($child->nodeType == XML_TEXT_NODE) {
                $result['_value'] = $child->nodeValue;
                if (count($result) == 1)
                    return $result['_value'];
                else
                    return $result;
            }
        }

        $group = array();
        for($i = 0; $i < $children->length; $i++) {
            $child = $children->item($i);
            $recursive_result = dom_to_array($child);

            if (!isset($result[$child->nodeName])) {
                if (!is_null($recursive_result))
                    $result[$child->nodeName] = $recursive_result;
            } else {
                if (!isset($group[$child->nodeName])) {
                    $tmp = $result[$child->nodeName];
                    $result[$child->nodeName] = array($tmp);
                    $group[$child->nodeName] = 1;
                }

                if (!is_null($recursive_result))
                    $result[$child->nodeName][] = $recursive_result;
            }
        }
        if (isset($result))
            return $result;
        else
            return NULL;
    }

    function getArray($node)
    {
        $array = false;

        if ($node->hasAttributes())
        {
            foreach ($node->attributes as $attr)
            {
                $array[$attr->nodeName] = $attr->nodeValue;
            }
        }

        if ($node->hasChildNodes())
        {
            if ($node->childNodes->length == 1)
            {
                $array[$node->firstChild->nodeName] = $node->firstChild->nodeValue;
            }
            else
            {
                foreach ($node->childNodes as $childNode)
                {
                    if ($childNode->nodeType != XML_TEXT_NODE)
                    {
                        $array[$childNode->nodeName][] = getArray($childNode);
                    }
                }
            }
        }

        return $array;
    }


    class MyDOMDocument extends DOMDocument
    {
        public function toArray(DOMNode $oDomNode = null)
        {
            // return empty array if dom is blank
            if (is_null($oDomNode) && !$this->hasChildNodes()) {
                return array();
            }
            $oDomNode = (is_null($oDomNode)) ? $this->documentElement : $oDomNode;
            if (!$oDomNode->hasChildNodes()) {
                $mResult = $oDomNode->nodeValue;
            } else {
                $mResult = array();
                foreach ($oDomNode->childNodes as $oChildNode) {
                    // how many of these child nodes do we have?
                    // this will give us a clue as to what the result structure should be
                    $oChildNodeList = $oDomNode->getElementsByTagName($oChildNode->nodeName);
                    $iChildCount = 0;
                    // there are x number of childs in this node that have the same tag name
                    // however, we are only interested in the # of siblings with the same tag name
                    foreach ($oChildNodeList as $oNode) {
                        if ($oNode->parentNode->isSameNode($oChildNode->parentNode)) {
                            $iChildCount++;
                        }
                    }
                    $mValue = $this->toArray($oChildNode);
                    $sKey   = ($oChildNode->nodeName{0} == '#') ? 0 : $oChildNode->nodeName;
                    $mValue = is_array($mValue) ? $mValue[$oChildNode->nodeName] : $mValue;
                    // how many of thse child nodes do we have?
                    if ($iChildCount > 1) {  // more than 1 child - make numeric array
                        $mResult[$sKey][] = $mValue;
                    } else {
                        $mResult[$sKey] = $mValue;
                    }
                }
                // if the child is <foo>bar</foo>, the result will be array(bar)
                // make the result just 'bar'
                if (count($mResult) == 1 && isset($mResult[0]) && !is_array($mResult[0])) {
                    $mResult = $mResult[0];
                }
            }
            // get our attributes if we have any
            $arAttributes = array();
            if ($oDomNode->hasAttributes()) {
                foreach ($oDomNode->attributes as $sAttrName=>$oAttrNode) {
                    // retain namespace prefixes
                    $arAttributes["@{$oAttrNode->nodeName}"] = $oAttrNode->nodeValue;
                }
            }
            // check for namespace attribute - Namespaces will not show up in the attributes list
            if ($oDomNode instanceof DOMElement && $oDomNode->getAttribute('xmlns')) {
                $arAttributes["@xmlns"] = $oDomNode->getAttribute('xmlns');
            }
            if (count($arAttributes)) {
                if (!is_array($mResult)) {
                    $mResult = (trim($mResult)) ? array($mResult) : array();
                }
                $mResult = array_merge($mResult, $arAttributes);
            }
            $arResult = array($oDomNode->nodeName=>$mResult);
            return $arResult;
        }
    }

    function getTranslationParagraphs($document) {
        $result = array();
        $body = $document->getElementsByTagName("body")->item(0);
        foreach ($body->childNodes as $node ) {
            if ($node instanceof DOMElement && 0 == strcasecmp($node->tagName, "p") && $node->hasAttribute("dir")) {
                $result[] = $node;
            }
        }
        return $result;
    }

    function getInnerHtml($node) {
        $innerHTML= '';
        $children = $node->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML( $child );
        }

        return $innerHTML;
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        $start  = $length * -1; //negative
        return (substr($haystack, $start) === $needle);
    }

    function traverse_node($node)
    {
        if ($node instanceof DOMText) {
        }
        else {
            if ($node->getAttribute("style") == "color:#000070;")
                echo("<br>Word " . $node->nodeValue);
            elseif (endsWith($node->getAttribute("style"), "font-size:8pt;"))
                echo("<br>Variants " . $node->nodeValue);
        }

        $children = $node->childNodes;
        if (is_null($children))
            return NULL;

        for($i = 0; $i < $children->length; $i++) {
            $recursive_result = traverseTranslationParagraph($children->item($i));
        }
    }

    $doc = new MyDOMDocument();
    $doc->loadHTMLFile("dictionaries/multilex-rocz.html");
    foreach(getTranslationParagraphs($doc) as $para) {
        //d(get_inner_html($para));
        traverseTranslationParagraph($para);
    }

    echo "Finished"
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