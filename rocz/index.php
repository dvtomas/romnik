<?php
    include "../lib/header.php";
    common_header("Multidialektní slovník romštiny");

    include("lib/common.php");
    if (isset($_GET["q"]))
        $query = trim($_GET["q"]);
    else
        $query = "";

    global $allVariantGroups;
    global $allVariants;

    $selectedVariants = array();
    foreach($allVariants as $variant) {
        if (isset($_GET[$variant]))
            $selectedVariants[] = $variant;
    }
    if (empty($selectedVariants)) // If no variant is selected, treat the query as if all variants were selected.
        $selectedVariants = $allVariants;

    function generateCheckVariantsButton($name, $id, $variantsToCheck) {
        global $allVariants;
        $function_name = 'button_check_' . $id;
        echo '<input type="button" value="' . $name . '" onclick="'.$function_name.'()" />';
        echo '
        <script>
        function ' . $function_name . '() {
        ';
        foreach ($allVariants as $variant) {
            if (in_array($variant, $variantsToCheck)) {
                echo '$("input[name^=' . $variant . ']").prop("checked", true);';
            } else {
                echo '$("input[name^=' . $variant . ']").prop("checked", false);';
            }
        }
        echo '
        }
        </script>
        ';
    }

    echo '
    <FORM METHOD=GET ACTION="index.php">
    <em>Vyhledat ve slovníku: </em><INPUT TYPE="text" NAME="q" SIZE="30" AUTOFOCUS="1" VALUE="' . $query . '">
    <INPUT TYPE="submit" VALUE="Hledat">
    <p />
    <em>Vybrané variety</em>: ';
    generateCheckVariantsButton("všechny", "all", $allVariants);
    echo '<p />';

    foreach($allVariantGroups as $variantGroup) {
        echo "<fieldset>\n";
        generateCheckVariantsButton($variantGroup[0][0], $variantGroup[0][0], $variantGroup);
        echo " ";
        foreach ($variantGroup as $variant) {
            if (in_array($variant, $selectedVariants))
                $checked = 'checked="checked"';
            else
                $checked = "";
            echo '<label>
                <input name="' . $variant . '" value="' . $variant . '" type="checkbox"' . $checked . '>' . $variant .
            '</label>';
        }
        echo "</fieldset>\n";
    }

    echo '
    </FORM>
    ';

    if (isset($_GET["q"])) {
        if ($query == "")
            warn("Je třeba vyplnit hledané slovo.");
        else {
            getMySQLConnection();
            $sql = "SELECT DISTINCT PARAGRAPH FROM PARAGRAPHS, WORDS WHERE PARAGRAPH_ID = PARAGRAPHS.ID AND ASCII LIKE '" .
                mysql_real_escape_string(utf8ToAscii($query)) . "%' AND (";
            $selectedVariantsExpressions = array();
            foreach ($selectedVariants as $variant)
                $selectedVariantsExpressions[] = $variant . "=1";
            $sql .= implode(" OR ", $selectedVariantsExpressions);
            $sql .= ")";
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