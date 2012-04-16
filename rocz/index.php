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

    <title>Romsko-český slovník</title>
    <meta name="description" content="">

    <!-- Mobile viewport optimized: h5bp.com/viewport -->
    <meta name="viewport" content="width=device-width">

    <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

    <!-- Blueprint CSS -->
    <link rel="stylesheet" href="/css/blueprint/screen.css" type="text/css" media="screen, projection">
    <link rel="stylesheet" href="/css/blueprint/print.css" type="text/css" media="print">
    <!--[if lt IE 8]>
    <link rel="stylesheet" href="/css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->

    <!-- Import fancy-type plugin for the sample page. -->
    <link rel="stylesheet" href="/css/blueprint/plugins/fancy-type/screen.css" type="text/css" media="screen, projection">
    <link rel="stylesheet" href="../css/style.css">

    <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

    <!-- All JavaScript at the bottom, except this Modernizr build.
Modernizr enables HTML5 elements & feature detects for optimal performance.
Create your own custom Modernizr build: www.modernizr.com/download/ -->
    <script src="../js/libs/modernizr-2.5.3.min.js"></script>
</head>
<body>
<!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6.
chromium.org/developers/how-tos/chrome-frame-getting-started -->
<!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a
    different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a>
    to experience this site.</p><![endif]-->
<header>

</header>
<div class="container" role="main">
    <h1>Romsko-český slovník</h1>
    <?php
    include("lib/common.php");
    getMySQLConnection();
    if (isset($_GET["q"]))
        $query = trim($_GET["q"]);
    else
        $query = "";

    echo '
    <FORM METHOD=GET ACTION="index.php">
    <em>Vyhledat ve slovníku: </em><INPUT TYPE="text" NAME="q" SIZE="30" VALUE="' . $query . '">
    <INPUT TYPE="submit" VALUE="Hledat">
    </FORM>
    ';

    if (isset($_GET["q"])) {
        if ($query == "")
            warn("Je třeba vyplnit hledané slovo.");
        else {
            $sql = "SELECT PARAGRAPH FROM PARAGRAPHS, WORDS WHERE PARAGRAPH_ID = PARAGRAPHS.ID AND ASCII LIKE '" .
                mysql_real_escape_string(utf8ToAscii($query)) . "%'";
            $result = mysql_query_or_die($sql);
            echo "<p /> <h2>Výsledky vyhledávání</h2><p />";
            $result_index = 0;
            while ($row = mysql_fetch_assoc($result)) {
                $result_index++;
                echo "<hr />";
                echo $result_index . ".";
                echo $row["PARAGRAPH"];
                echo "<p />\n";
            }
            mysql_free_result($result);
            if ($result_index == 0) {
                warn("Pro zadaný výraz nebyly nalezeny žádné výsledky.");
            }
        }
    }
include "../lib/footer.php"
?>