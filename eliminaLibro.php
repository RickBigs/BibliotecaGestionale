<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Reindirizzo alla pagina di login se l'utente non è autenticato
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Includo la connessione al database
require 'connessione.php';
require_once 'header.php';

// Controllo se è stato passato un ID valido tramite GET
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Elimina il libro con Codice Libro specificato
    $stmt = $conn->prepare("DELETE FROM libri WHERE id_libro = ?");
    $stmt->bind_param("i", $id);

    // Eseguo la query e gestisco il reindirizzamento in base al risultato
    if ($stmt->execute()) {
        header("Location: libri.php?success=1");
        exit;
    } else {
        header("Location: libri.php?error=1");
        exit;
    }
} else {
    // Reindirizzo a libri.php in caso di errore
    header("Location: libri.php?error=1");
    exit;
}
?>
