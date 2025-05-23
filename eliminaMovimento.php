<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Controllo autenticazione utente
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Controllo permessi admin
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'Admin') {
    header('Location: index.php?error=permesso');
    exit;
}

// Includo la connessione al database
require_once 'connessione.php';

// Verifica dell'ID movimento
if (!isset($_GET['id'])) {
    header('Location: magazzino.php?error=1');
    exit;
}

$id_movimento = intval($_GET['id']);

// Controllo che il movimento esista
$stmt_check = $conn->prepare("SELECT id_movimento FROM movimenti_magazzino WHERE id_movimento = ?");
$stmt_check->bind_param("i", $id_movimento);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows === 0) {
    $stmt_check->close();
    header('Location: magazzino.php?error=1');
    exit;
}

$stmt_check->close();

// Cancellazione del movimento di magazzino
$stmt = $conn->prepare("DELETE FROM movimenti_magazzino WHERE id_movimento = ?");
$stmt->bind_param("i", $id_movimento);
if ($stmt->execute()) {
    // Dopo l'eliminazione aggiorna la disponibilita del libro
    // Prima recupera l'id_libro del movimento eliminato
    $stmt_libro = $conn->prepare("SELECT id_libro FROM movimenti_magazzino WHERE id_movimento = ?");
    $stmt_libro->bind_param("i", $id_movimento);
    $stmt_libro->execute();
    $stmt_libro->bind_result($id_libro);
    $stmt_libro->fetch();
    $stmt_libro->close();
    if (isset($id_libro)) {
        $q = $conn->prepare("SELECT IFNULL(SUM(CASE WHEN tipo_movimento='carico' THEN quantita WHEN tipo_movimento='scarico' THEN -quantita ELSE 0 END),0) FROM movimenti_magazzino WHERE id_libro = ?");
        $q->bind_param("i", $id_libro);
        $q->execute();
        $q->bind_result($nuova_quantita);
        $q->fetch();
        $q->close();
        $stmt_disp = $conn->prepare("INSERT INTO disponibilita (id_libro, quantita) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantita = ?");
        $stmt_disp->bind_param("iii", $id_libro, $nuova_quantita, $nuova_quantita);
        $stmt_disp->execute();
        $stmt_disp->close();
        // Aggiorna anche la colonna quantita nella tabella libri
        $stmt_libro = $conn->prepare("UPDATE libri SET quantita = ? WHERE id_libro = ?");
        $stmt_libro->bind_param("ii", $nuova_quantita, $id_libro);
        $stmt_libro->execute();
        $stmt_libro->close();
    }
    header('Location: magazzino.php?success=1');
} else {
    header('Location: magazzino.php?error=1');
}
$stmt->close();
$conn->close();
exit;
?>
