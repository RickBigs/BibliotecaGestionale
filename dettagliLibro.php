<?php
// Avvio la sessione per gestire l'autenticazione
session_start();
// Includo la connessione al database
require_once 'connessione.php';

// Controllo se l'utente è autenticato, altrimenti reindirizzo alla pagina di login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Verifico se l'ID del libro è stato passato tramite GET, altrimenti mostro un messaggio di errore
if (!isset($_GET['id'])) {
    echo "<p>Errore: ID libro non specificato.</p>";
    exit;
}

// Recupero l'ID del libro dalla query string e preparo la query per ottenere i dettagli del libro
$id_libro = (int)$_GET['id'];
$sql = "SELECT l.*, a.nominativo AS autore FROM libri l INNER JOIN autori a ON l.id_autore = a.id_autore WHERE l.id_libro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_libro);
$stmt->execute();
$result = $stmt->get_result();

// Se non viene trovato nessun libro con l'ID specificato, mostro un messaggio di errore
if ($result->num_rows != 1) {
    echo "<p>Libro non trovato.</p>";
    exit;
}

// Recupero i dettagli del libro trovato
$libro = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettagli Libro</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<h1 class="text-2xl font-bold text-blue-900 mb-6">Dettagli Libro</h1>
<div class="max-w-xl mx-auto bg-white rounded-lg shadow p-6">
<table class="min-w-full divide-y divide-gray-200">
    <tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Codice</th><td class="px-4 py-2"><?php echo $libro['id_libro']; ?></td></tr>
    <tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Titolo</th><td class="px-4 py-2"><?php echo htmlspecialchars($libro['titolo']); ?></td></tr>
    <tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Autore</th><td class="px-4 py-2"><?php echo htmlspecialchars($libro['autore']); ?></td></tr>
    <tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Anno di stampa</th><td class="px-4 py-2"><?php echo $libro['anno_stampa']; ?></td></tr>
    <tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Prezzo</th><td class="px-4 py-2"><?php echo $libro['prezzo']; ?></td></tr>
    <?php if (isset($libro['trama'])) { echo '<tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Trama</th><td class="px-4 py-2">' . htmlspecialchars($libro['trama']) . '</td></tr>'; } ?>
</table>
<a href="libri.php" class="mt-6 inline-block bg-blue-600 hover:bg-blue-800 text-white px-4 py-2 rounded transition">Torna all'elenco libri</a>
</div>
</body>
</html>
