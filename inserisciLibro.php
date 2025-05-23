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
    <title>Inserisci Libro</title>
</head>
<body>

<?php require_once 'header.php'; ?>

<h1>Inserisci un nuovo libro</h1>

<form id="form-libro"method="post" action="inserisciLibro.php">
    <label for="titolo">Titolo:</label>
    <input type="text" name="titolo" id="titolo" required style="width: 100%; padding: 10px; margin-bottom: 15px;">

    <label for="id_autore">Autore:</label>
    <select name="id_autore" id="id_autore" required style="width: 100%; padding: 10px; margin-bottom: 15px;">
        <option value="">-- Seleziona un autore --</option>
        <?php
        // Popolo il menù a tendina con gli autori disponibili
        if ($result_autori->num_rows > 0) {
            while ($autore = $result_autori->fetch_assoc()) {
                echo "<option value='".$autore["id_autore"]."'>".$autore["nominativo"]."</option>";
            }
        }
        ?>
    </select>

    <label for="anno_stampa">Anno di stampa:</label>
    <input type="number" name="anno_stampa" id="anno_stampa" required style="width: 100%; padding: 10px; margin-bottom: 15px;">

    <label for="prezzo">Prezzo (€):</label>
    <input type="number" step="0.01" name="prezzo" id="prezzo" required style="width: 100%; padding: 10px; margin-bottom: 15px;">

    <label for="trama">Trama (max 255 caratteri):</label>
    <textarea name="trama" id="trama" maxlength="255" rows="3" style="width:100%;margin-bottom:1rem;"></textarea>

    <button type="submit" class="bottone btn-add">Inserisci Libro</button>
</form>

</body>
</html>
