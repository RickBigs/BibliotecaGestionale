<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Reindirizzo alla pagina di login se l'utente non è autenticato
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Includo la connessione al database
require_once 'connessione.php';

// Se il form è stato inviato
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nominativo = trim($_POST["nominativo"]);
    $descrizione = isset($_POST["descrizione"]) ? trim($_POST["descrizione"]) : null;
    if ($nominativo === "") {
        header('Location: autori.php?error=1');
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO autori (nominativo, descrizione) VALUES (?, ?)");
    $stmt->bind_param("ss", $nominativo, $descrizione);
    if ($stmt->execute()) {
        header('Location: autori.php?success=1');
    } else {
        header('Location: autori.php?error=1');
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Inserisci Autore</title>
</head>
<body>

<?php require_once 'header.php'; ?>

<h1>Inserisci un nuovo autore</h1>

<form id="form-autore" method="post" action="inserisciAutore.php">
    <label for="nominativo">Nominativo Autore:</label>
    <input type="text" name="nominativo" id="nominativo" required>
    <label for="descrizione">Storia/Breve descrizione:</label>
    <textarea name="descrizione" id="descrizione" maxlength="255" rows="3" style="width:100%;margin-bottom:1rem;"></textarea>
    <button type="submit" class="bottone btn-add">Inserisci Autore</button>
</form>

</body>
</html>
