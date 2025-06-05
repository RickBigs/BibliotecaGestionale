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
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-2">Benvenuto nella Biblioteca Digitale</h1>
    <p class="mb-6 text-gray-700">Gestisci libri, autori e magazzino in modo semplice e veloce.</p>
    <div class="flex flex-wrap gap-3 mb-8">
        <a href="libri.php" class="bg-blue-600 hover:bg-blue-800 text-white px-4 py-2 rounded transition">Vai ai Libri</a>
        <a href="autori.php" class="bg-green-600 hover:bg-green-800 text-white px-4 py-2 rounded transition">Vai agli Autori</a>
        <a href="magazzino.php" class="bg-yellow-600 hover:bg-yellow-800 text-white px-4 py-2 rounded transition">Vai al Magazzino</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <h2 class="text-3xl font-bold text-blue-900 mb-2"><?php echo $totLibri; ?></h2>
            <p class="text-gray-600">Libri in archivio</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <h2 class="text-3xl font-bold text-blue-900 mb-2"><?php echo $totAutori; ?></h2>
            <p class="text-gray-600">Autori registrati</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
            <h2 class="text-3xl font-bold text-blue-900 mb-2"><?php echo $totDisponibili; ?></h2>
            <p class="text-gray-600">Libri disponibili</p>
        </div>
    </div>
</main>
</body>
</html>
