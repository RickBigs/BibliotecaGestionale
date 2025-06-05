<?php
// Avvio la sessione per gestire l'autenticazione
session_start();
// Includo la connessione al database
require_once 'connessione.php';

// Verifico se l'ID dell'autore è stato passato tramite GET
if (!isset($_GET['id'])) {
    echo "<p>Errore: ID autore non specificato.</p>";
    exit;
}

// Recupero l'ID dell'autore dalla query string
$id_autore = (int)$_GET['id'];

// Preparo la query per selezionare i dettagli dell'autore
$sql = "SELECT * FROM autori WHERE id_autore = $id_autore";
$result = $conn->query($sql);

// Verifico se è stato trovato un autore con quell'ID
if ($result->num_rows != 1) {
    echo "<p>Autore non trovato.</p>";
    exit;
}

// Recupero i dati dell'autore
$autore = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dettagli Autore</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Dettagli Autore</h1>
    <div class="bg-white rounded-lg shadow p-6">
    <table class="min-w-full divide-y divide-gray-200">
        <tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Codice</th><td class="px-4 py-2"><?php echo $autore['id_autore']; ?></td></tr>
        <tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Nominativo</th><td class="px-4 py-2"><?php echo htmlspecialchars($autore['nominativo']); ?></td></tr>
        <?php
        // Mostro la descrizione dell'autore se disponibile
        if (isset($autore['descrizione'])) {
            echo '<tr><th class="text-left px-4 py-2 text-gray-700 font-semibold">Storia</th><td class="px-4 py-2">' . htmlspecialchars($autore['descrizione']) . '</td></tr>';
        }
        ?>
    </table>
    <a href="autori.php" class="mt-6 inline-block bg-blue-600 hover:bg-blue-800 text-white px-4 py-2 rounded transition">Torna all'elenco autori</a>
    </div>
</main>
</body>
</html>
