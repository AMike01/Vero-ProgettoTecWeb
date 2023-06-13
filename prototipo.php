<?php

session_start(); // ATTENZIONE ALLA BREADCRUMB

$title = "Prototipo";
$description = "Pagina di prova";
$keywords = "Prova";

if (isset($_SESSION["username"])) {
    if (isset($_SESSION["ruolo"]) == "utente") {
        $pagina = file_get_contents("HTML/headerUtente.html");
    } elseif (isset($_SESSION["ruolo"]) == "amministratore") {
        $pagina = file_get_contents("HTML/headerAmministratore.html");
    }
} else {
    $pagina = file_get_contents("HTML/headerSemplice.html");
}
require_once "PHP/header.php"; // inserisce info nell'header
// carica contenuto
$pagina .= file_get_contents("HTML/contenuto.html");
// carica footer

$titolo = "TITOLO";

//$pagina = file_get_contents("HTML/prototipo.html");

echo $pagina;
?>