<?php
// Avvio la sessione per gestire l'autenticazione
session_start();
// Includo la connessione al database
require_once 'connessione.php';

// Controllo permessi: solo gli Admin possono accedere a questa pagina
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'Admin') {
    header('Location: index.php?error=permesso');
    exit;
}

// Recupera elenco libri per la select
$libri = $conn->query("SELECT id_libro, titolo FROM libri ORDER BY titolo ASC");

// Gestione del form di inserimento movimento
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_libro = intval($_POST['id_libro']);
    $tipo_movimento = $_POST['tipo_movimento'];
    $quantita = intval($_POST['quantita']);
    $data_movimento = $_POST['data_movimento'];
    $descrizione = trim($_POST['descrizione']);
    // Validazione dei dati in input
    if ($id_libro && in_array($tipo_movimento, ['carico','scarico']) && $quantita > 0 && $data_movimento) {
        // Controllo disponibilità per scarico
        if ($tipo_movimento === 'scarico') {
            $q = $conn->prepare("SELECT IFNULL(SUM(CASE WHEN tipo_movimento='carico' THEN quantita ELSE -quantita END),0) as disp FROM movimenti_magazzino WHERE id_libro = ?");
            $q->bind_param("i", $id_libro);
            $q->execute();
            $q->bind_result($disponibilita);
            $q->fetch();
            $q->close();
            // Se la quantità da scaricare è superiore alla disponibilità, mostro un errore
            if ($quantita > $disponibilita) {
                echo "<div class='alert-error'>Impossibile scaricare: la quantità richiesta supera la disponibilità attuale ($disponibilita).</div>";
                $conn->close();
                exit;
            }
        }
        // Inserimento del movimento di magazzino nel database
        $stmt = $conn->prepare("INSERT INTO movimenti_magazzino (id_libro, tipo_movimento, quantita, data_movimento, descrizione) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isiss", $id_libro, $tipo_movimento, $quantita, $data_movimento, $descrizione);
        if ($stmt->execute()) {

            header('Location: magazzino.php?success=1');
        } else {
            header('Location: magazzino.php?error=1');
        }
        $stmt->close();
        $conn->close();
        exit;
    } else {
        echo "<div class='alert-error'>Dati non validi.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Nuovo Movimento Magazzino</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php require_once 'header.php'; ?>
<h1>Inserisci Movimento di Magazzino</h1>
<form id="form-magazzino"method="post" action="inserisciMovimento.php">
    <label for="id_libro">Libro:</label>
    <select name="id_libro" id="id_libro" required>
        <option value="">-- Seleziona libro --</option>
        <?php while ($row = $libri->fetch_assoc()) {
            echo "<option value='{$row['id_libro']}'>{$row['titolo']}</option>";
        } ?>
    </select>
    <label for="tipo_movimento">Tipo movimento:</label>
    <select name="tipo_movimento" id="tipo_movimento" required>
        <option value="carico">Carico</option>
        <option value="scarico">Scarico</option>
    </select>
    <label for="quantita">Quantità:</label>
    <input type="number" name="quantita" id="quantita" min="1" required>
    <label for="data_movimento">Data movimento:</label>
    <input type="date" name="data_movimento" id="data_movimento" required>
    <label for="descrizione">Descrizione (opzionale):</label>
    <textarea name="descrizione" id="descrizione" maxlength="255" rows="2" style="width:100%;margin-bottom:1rem;"></textarea>
    <button type="submit" class="bottone btn-add">Salva Movimento</button>
</form>
<a href="magazzino.php" class="bottone">Torna al Magazzino</a>
</body>
</html>
