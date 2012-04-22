<?php
include "../lib/header.php";
common_header("Slovník romských neologismů - nahrát nový slovník");
?>
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
