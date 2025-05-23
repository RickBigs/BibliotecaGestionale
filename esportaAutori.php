<?php
// Avvio la sessione per gestire l'autenticazione
session_start();

// Includo la connessione al database
require_once 'connessione.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=autori.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID Autore', 'Nominativo']);

$sql = "SELECT id_autore, nominativo FROM autori ORDER BY nominativo ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['id_autore'], $row['nominativo']]);
}

fclose($output);
$conn->close();
?>
