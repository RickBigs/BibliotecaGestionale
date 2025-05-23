<?php
require_once 'connessione.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=libri.csv');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID Libro', 'Titolo', 'Autore', 'Anno di stampa', 'Prezzo', 'Quantità', 'Trama']);
$sql = "SELECT l.id_libro, l.titolo, a.nominativo AS autore, l.anno_stampa, l.prezzo, d.quantita, l.trama FROM libri l LEFT JOIN disponibilita d ON l.id_libro = d.id_libro INNER JOIN autori a ON l.id_autore = a.id_autore ORDER BY l.titolo ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id_libro'],
            $row['titolo'],
            $row['autore'],
            $row['anno_stampa'],
            $row['prezzo'],
            $row['quantita'],
            $row['trama']
        ]);
    }
}
fclose($output);
$conn->close();
?>
