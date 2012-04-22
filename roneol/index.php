<?php
    include "../lib/header.php";
    common_header("Slovník romských neologismů");

    include("lib/common.php");
    if (isset($_GET["q"]))
        $query = trim($_GET["q"]);
    else
        $query = "";

    echo '
    <FORM METHOD=GET ACTION="index.php">
    <em>Vyhledat ve slovníku: </em><INPUT TYPE="text" NAME="q" SIZE="30" AUTOFOCUS="1" VALUE="' . $query . '">
    <INPUT TYPE="submit" VALUE="Hledat">
    <p />
    </FORM>
    ';

    if (isset($_GET["q"])) {
        if ($query == "")
            warn("Je třeba vyplnit hledané slovo.");
        else {
            getMySQLConnection();
            $sql = "SELECT DISTINCT PARAGRAPH FROM PARAGRAPHS, WORDS WHERE PARAGRAPH_ID = PARAGRAPHS.ID AND ASCII LIKE '" .
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
            mysql_close();
        }
    }
include "../lib/footer.php"
?>