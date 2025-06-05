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
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Inserisci un nuovo autore</h1>
    <form id="form-autore" method="post" action="inserisciAutore.php" class="bg-white rounded-lg shadow p-6 flex flex-col gap-4">
        <label for="nominativo" class="font-semibold text-blue-900">Nominativo Autore:</label>
        <input type="text" name="nominativo" id="nominativo" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="descrizione" class="font-semibold text-blue-900">Storia/Breve descrizione:</label>
        <textarea name="descrizione" id="descrizione" maxlength="255" rows="3" class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
        <button type="submit" class="bg-green-700 hover:bg-green-900 text-white px-4 py-2 rounded transition">Inserisci Autore</button>
    </form>
</main>
</body>
</html>
