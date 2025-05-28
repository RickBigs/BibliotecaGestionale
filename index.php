<?php

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once 'connessione.php';
$totAutori = 0;
$totLibri = 0;
$totDisponibili = 0;
$entrate = 0;
$uscite = 0;
$res1 = $conn->query("SELECT COUNT(*) as tot FROM autori");
if ($res1 && $row1 = $res1->fetch_assoc()) {
    $totAutori = $row1['tot'];
}
$res2 = $conn->query("SELECT COUNT(*) as tot FROM libri");
if ($res2 && $row2 = $res2->fetch_assoc()) {
    $totLibri = $row2['tot'];
}
// Totale entrate
$resEntrate = $conn->query("SELECT SUM(quantita) as tot FROM movimenti_magazzino WHERE tipo_movimento = 'carico'");
if ($resEntrate && $row = $resEntrate->fetch_assoc()) {
    $entrate = $row['tot'] !== null ? $row['tot'] : 0;
}

// Totale uscite
$resUscite = $conn->query("SELECT SUM(quantita) as tot FROM movimenti_magazzino WHERE tipo_movimento = 'scarico'");
if ($resUscite && $row = $resUscite->fetch_assoc()) {
    $uscite = $row['tot'] !== null ? $row['tot'] : 0;
}

// Disponibili = entrate - uscite
$totDisponibili = $entrate - $uscite;

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Biblioteca - Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php require_once 'header.php'; ?>
<main>
    <h1>Benvenuto nella Biblioteca Digitale</h1>
    <p>Gestisci libri, autori e magazzino in modo semplice e veloce.</p>
    <br>
    <div class="dashboard-nav">
        <a href="libri.php">Vai ai Libri</a>
        <a href="autori.php">Vai agli Autori</a>
        <a href="magazzino.php">Vai al Magazzino</a>
    </div>
    <div class="dashboard">
        <div class="card">
            <h2><?php echo $totLibri; ?></h2>
            <p>Libri in archivio</p>
        </div>
        <div class="card">
            <h2><?php echo $totAutori; ?></h2>
            <p>Autori registrati</p>
        </div>
        <div class="card">
            <h2><?php echo $totDisponibili; ?></h2>
            <p>Libri disponibili</p>
        </div>
    </div>
</main>
</body>
</html>
