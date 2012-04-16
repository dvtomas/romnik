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

    <!-- Blueprint CSS -->
    <link rel="stylesheet" href="/css/blueprint/screen.css" type="text/css" media="screen, projection">
    <link rel="stylesheet" href="/css/blueprint/print.css" type="text/css" media="print">
    <!--[if lt IE 8]>
    <link rel="stylesheet" href="/css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->

    <!-- Import fancy-type plugin for the sample page. -->
    <link rel="stylesheet" href="/css/blueprint/plugins/fancy-type/screen.css" type="text/css"
          media="screen, projection">
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
    <h1>Nahrání nového slovníku</h1>

    Vyberte soubor se slovníkem. Tento soubor vytvoříte v programu TshwaneLex pomocí exportu do HTML. Nejprve si pokusně
    zkontrolujte, jak proběhne indexace souboru, ujistěte se tak, že soubor je v pořádku a ve správném formátu. Normálně
    by po pokusné indexaci na konci této stránky měl být obrovský seznam se všemi slovy ve slovníku, jejich varietami a
    seznam překladů. Pokud by byl nahrán špatný soubor, nebo pokud se změnil formát exportu, bude vidět něco jiného -
    záleží na situaci. Teprve poté stiskněte tlačítko <code>"Import slovníku do databáze!"</code> a proveďte tak samotný
    import nových dat do databáze on-line slovníku.

    <hr/>
    <h2>Nahrání nového souboru s exportem slovníku na server</h2>

    <form action="load-new-dictionary.php" method="post" enctype="multipart/form-data">
        <label for="file">Soubor s exportovaným slovníkem (*.html):</label>
        <input type="file" name="file" id="file"/>
        <br/>
        <br/>
        Pokusná indexace nového souboru se slovníkem. Bez rizika si zkontrolujte, jak bude soubor importován on-line
        slovníkem.
        <input type="submit" name="submitFile" value="Pokusná indexace souboru se slovníkem"/>
        <br/>
        <br/>
        Po stisku tohoto tlačítka se soubor s exportem nahraje do databáze a slovník podle něj začne překládat. Než
        provedete tuto akci, zkontrolujte si na konci stránky, jak dopadla pokusná indexace!
        <br/>
        <input type="submit" name="importFile" value="Import slovníku do databáze!"/>
    </form>

    <?php
    include_once("lib/common.php");
    include_once("lib/dictionary-parser.php");
    $shouldPopulateDatabase = isset($_POST["importFile"]);

    if (isset($_FILES["file"])) {
        echo "<hr>";
        echo "<h2>Byl nahrán nový soubor</h2>";
        if ($_FILES["file"]["error"] > 0) {
            echo "Chyba při nahrávání souboru: " . $_FILES["file"]["error"] . "<br />";
        }
        else {
            echo "Soubor se slovníkem: '" . $_FILES["file"]["name"] . "' o velikosti " . number_format($_FILES["file"]["size"] / 1024, 1) . " kB.<br />";
            if ($shouldPopulateDatabase) {
                echo '<div class="notice">Importuji soubor se slovníkem do databáze!</div>';
            }
            parseDictionaryFile($_FILES["file"]["tmp_name"]);
            if ($shouldPopulateDatabase) {
                populateDatabase();
            }

            dumpParseResult();
        }
    }

    include "../lib/footer.php"
?>
