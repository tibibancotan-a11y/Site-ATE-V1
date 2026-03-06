<?php
function esteActiv($status) {
    return $status == 1;
}

function scurteazaDescriere($text, $lungime = 100) {
    return strlen($text) > $lungime ? substr($text, 0, $lungime) . '...' : $text;
}
?>
