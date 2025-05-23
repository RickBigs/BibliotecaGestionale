<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once 'connessione.php';
// Libri più scaricati (top 5)
$sql = "SELECT l.titolo, IFNULL(SUM(CASE WHEN m.tipo_movimento='scarico' THEN m.quantita ELSE 0 END),0) AS scaricati
        FROM libri l
        LEFT JOIN movimenti_magazzino m ON l.id_libro = m.id_libro
        GROUP BY l.id_libro
        ORDER BY scaricati DESC, l.titolo ASC
        LIMIT 5";
$result = $conn->query($sql);
$libri = [];
$quantita = [];
while ($row = $result->fetch_assoc()) {
    $libri[] = $row['titolo'];
    $quantita[] = (int)$row['scaricati'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Statistiche Biblioteca</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    .stat-container { max-width: 700px; margin: 2rem auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?>
<div class="stat-container">
    <h1>Top picks</h1>
    <h2>Libri più venduti (TOP 5)</h2>
    <canvas id="topLibriChart" height="120"></canvas>
</div>
<script>
const ctx = document.getElementById('topLibriChart').getContext('2d');
const topLibriChart = new Chart(ctx, {
    type: 'pie', // Cambiato da 'bar' a 'pie'
    data: {
        labels: <?php echo json_encode($libri); ?>,
        datasets: [{
            label: 'Scaricati',
            data: <?php echo json_encode($quantita); ?>,
            backgroundColor: [
                'rgba(44, 62, 80, 0.7)',
                'rgba(52, 152, 219, 0.7)',
                'rgba(46, 204, 113, 0.7)',
                'rgba(241, 196, 15, 0.7)',
                'rgba(231, 76, 60, 0.7)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'bottom' },
            title: { display: false }
        }
    }
});
</script>
</body>
</html>
