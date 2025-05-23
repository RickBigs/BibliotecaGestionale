<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Redirigo alla pagina di login se l'utente non è autenticato
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Includo la connessione al database
require_once 'connessione.php';
// Includo l'header
require_once 'header.php';

// Controllo se l'ID dell'autore è stato passato tramite GET
if (!isset($_GET['id'])) {
    header('Location: autori.php?error=1');
    exit;
}

// Preparo ed eseguo la query per eliminare l'autore
$id_autore = (int)$_GET['id'];
$stmt = $conn->prepare("DELETE FROM autori WHERE id_autore = ?");
$stmt->bind_param("i", $id_autore);

if ($stmt->execute()) {
    // Reindirizzo con successo se l'autore è stato eliminato
    header('Location: autori.php?success=1');
} else {
    // Reindirizzo con errore in caso di fallimento dell'eliminazione
    header('Location: autori.php?error=1');
}

// Chiudo lo statement e la connessione
$stmt->close();
$conn->close();
exit;
?>
