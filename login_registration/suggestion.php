<?php
$names = array("Jack", "Jones", "Martha", "Jony", "Glen");
if (isset($_POST['suggestion'])) {
    $suggestion = strtoupper($_POST['suggestion']);
    if (!empty($suggestion)) {
        foreach ($names as $n) {
            if (strpos(strtoupper($n), $suggestion) !== false) {
                echo $n;
                echo "<br />";
            }
        }
    }
}
?> 