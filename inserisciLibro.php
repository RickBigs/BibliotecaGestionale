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
    $titolo = trim($_POST["titolo"]);
    $id_autore = intval($_POST["id_autore"]);
    $anno_stampa = intval($_POST["anno_stampa"]);
    $prezzo = floatval($_POST["prezzo"]);
    $trama = isset($_POST["trama"]) ? trim($_POST["trama"]) : null;

    // Controllo che i campi obbligatori siano stati compilati
    if ($titolo === "" || !$id_autore) {
        header('Location: libri.php?error=1');
        exit;
    }

    // Prepared statement per l'inserimento libro
    $stmt = $conn->prepare("INSERT INTO libri (titolo, id_autore, anno_stampa, prezzo, trama) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siids", $titolo, $id_autore, $anno_stampa, $prezzo, $trama);

    // Esecuzione dello statement e gestione del risultato
    if ($stmt->execute()) {
        header('Location: libri.php?success=1');
    } else {
        header('Location: libri.php?error=1');
    }

    // Chiusura dello statement e della connessione
    $stmt->close();
    $conn->close();
    exit;
}

// Recupero autori per il menù a tendina
$sql_autori = "SELECT id_autore, nominativo FROM autori ORDER BY nominativo ASC";
$result_autori = $conn->query($sql_autori);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Inserisci Libro</title>
</head>
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Inserisci un nuovo libro</h1>
    <form id="form-libro" method="post" action="inserisciLibro.php" class="bg-white rounded-lg shadow p-6 flex flex-col gap-4">
        <label for="titolo" class="font-semibold text-blue-900">Titolo:</label>
        <input type="text" name="titolo" id="titolo" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="id_autore" class="font-semibold text-blue-900">Autore:</label>
        <select name="id_autore" id="id_autore" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">-- Seleziona un autore --</option>
            <?php
            if ($result_autori->num_rows > 0) {
                while ($autore = $result_autori->fetch_assoc()) {
                    echo "<option value='".$autore["id_autore"]."'>".$autore["nominativo"]."</option>";
                }
            }
            ?>
        </select>
        <label for="anno_stampa" class="font-semibold text-blue-900">Anno di stampa:</label>
        <input type="number" name="anno_stampa" id="anno_stampa" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="prezzo" class="font-semibold text-blue-900">Prezzo (€):</label>
        <input type="number" step="0.01" name="prezzo" id="prezzo" required class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
        <label for="trama" class="font-semibold text-blue-900">Trama (max 255 caratteri):</label>
        <textarea name="trama" id="trama" maxlength="255" rows="3" class="border border-gray-300 rounded px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
        <button type="submit" class="bg-green-700 hover:bg-green-900 text-white px-4 py-2 rounded transition">Inserisci Libro</button>
    </form>
</main>
</body>
</html>
