<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Includo la connessione al database
require_once 'connessione.php';

// 1. Controllo se è stato passato l'id del libro da modificare
if (!isset($_GET['id_upd'])) {
    echo "<p>Errore: ID libro non specificato.</p>";
    exit;
}

$id_libro = (int)$_GET['id_upd'];

// 2. Recupero i dati del libro da modificare
$sql_libro = "SELECT * FROM libri WHERE id_libro = $id_libro";
$result_libro = $conn->query($sql_libro);

if ($result_libro->num_rows != 1) {
    echo "<p>Errore: libro non trovato.</p>";
    exit;
}

$libro = $result_libro->fetch_assoc();

// 3. Recupero autori per il menù a tendina
$sql_autori = "SELECT id_autore, nominativo FROM autori ORDER BY nominativo ASC";
$result_autori = $conn->query($sql_autori);

// 4. Se è stato inviato il form di modifica
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titolo = trim($_POST["titolo"]);
    $id_autore = intval($_POST["id_autore"]);
    $anno_stampa = intval($_POST["anno_stampa"]);
    $prezzo = floatval($_POST["prezzo"]);
    $trama = isset($_POST["trama"]) ? trim($_POST["trama"]) : null;
    if ($titolo === "" || !$id_autore) {
        header('Location: libri.php?error=1');
        exit;
    }
    $stmt_upd = $conn->prepare("UPDATE libri SET titolo = ?, id_autore = ?, anno_stampa = ?, prezzo = ?, trama = ? WHERE id_libro = ?");
    $stmt_upd->bind_param("siidsi", $titolo, $id_autore, $anno_stampa, $prezzo, $trama, $id_libro);
    if ($stmt_upd->execute()) {
        header('Location: libri.php');
    } else {
        header('Location: libri.php?error=1');
    }
    $stmt_upd->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Modifica Libro</title>
</head>
<body>

<?php require_once 'header.php'; ?>

<h1>Modifica Libro</h1>

<form method="post" action="modificaLibro.php?id_upd=<?php echo $id_libro; ?>">
    <label for="titolo">Titolo:</label>
    <input type="text" name="titolo" id="titolo" value="<?php echo htmlspecialchars($libro['titolo']); ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px;">

    <label for="id_autore">Autore:</label>
    <select name="id_autore" id="id_autore" required style="width: 100%; padding: 10px; margin-bottom: 15px;">
        <option value="">-- Seleziona un autore --</option>
        <?php
        if ($result_autori->num_rows > 0) {
            while ($autore = $result_autori->fetch_assoc()) {
                $selected = ($autore["id_autore"] == $libro["id_autore"]) ? "selected" : "";
                echo "<option value='".$autore["id_autore"]."' $selected>".$autore["nominativo"]."</option>";
            }
        }
        ?>
    </select>

    <label for="anno_stampa">Anno di stampa:</label>
    <input type="number" name="anno_stampa" id="anno_stampa" value="<?php echo $libro['anno_stampa']; ?>" min="1000" max="2100" required style="width: 100%; padding: 10px; margin-bottom: 15px;">

    <label for="prezzo">Prezzo (€):</label>
    <input type="number" step="0.01" name="prezzo" id="prezzo" value="<?php echo $libro['prezzo']; ?>" required style="width: 100%; padding: 10px; margin-bottom: 15px;">

    <label for="trama">Trama (max 255 caratteri):</label>
    <textarea name="trama" id="trama" maxlength="255" rows="3" style="width:100%;margin-bottom:1rem;"><?php echo isset($libro['trama']) ? htmlspecialchars($libro['trama']) : ''; ?></textarea>

    <button type="submit" class="bottone btn-add">Salva Modifiche</button>
</form>

</body>
</html>
