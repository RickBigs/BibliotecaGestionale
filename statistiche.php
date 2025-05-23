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

// Libri più caricati (top 5)
$sql_carichi = "SELECT l.titolo, IFNULL(SUM(CASE WHEN m.tipo_movimento='carico' THEN m.quantita ELSE 0 END),0) AS caricati
        FROM libri l
        LEFT JOIN movimenti_magazzino m ON l.id_libro = m.id_libro
        GROUP BY l.id_libro
        ORDER BY caricati DESC, l.titolo ASC
        LIMIT 5";
$result_carichi = $conn->query($sql_carichi);
$libri_carichi = [];
$quantita_carichi = [];
while ($row = $result_carichi->fetch_assoc()) {
    $libri_carichi[] = $row['titolo'];
    $quantita_carichi[] = (int)$row['caricati'];
}

// Confronto carichi e scarichi per ogni libro (top 10 per attività)
$sql_confronto = "SELECT l.titolo,
    IFNULL(SUM(CASE WHEN m.tipo_movimento='carico' THEN m.quantita ELSE 0 END),0) AS caricati,
    IFNULL(SUM(CASE WHEN m.tipo_movimento='scarico' THEN m.quantita ELSE 0 END),0) AS scaricati
FROM libri l
LEFT JOIN movimenti_magazzino m ON l.id_libro = m.id_libro
GROUP BY l.id_libro
ORDER BY (caricati + scaricati) DESC, l.titolo ASC
LIMIT 10";
$result_confronto = $conn->query($sql_confronto);
$libri_confronto = [];
$carichi_confronto = [];
$scarichi_confronto = [];
while ($row = $result_confronto->fetch_assoc()) {
    $libri_confronto[] = $row['titolo'];
    $carichi_confronto[] = (int)$row['caricati'];
    $scarichi_confronto[] = (int)$row['scaricati'];
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
    <hr style="margin:2rem 0;">
    <h2>Libri più caricati (TOP 5)</h2>
    <canvas id="topCarichiChart" height="120"></canvas>
    <hr style="margin:2rem 0;">
    <h2>Confronto carichi e scarichi (TOP 10)</h2>
    <canvas id="confrontoCarichiScarichiChart" height="180"></canvas>
</div>
<script>
const ctx = document.getElementById('topLibriChart').getContext('2d');
const topLibriChart = new Chart(ctx, {
    type: 'pie',
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

// Grafico per i carichi
const ctxCarichi = document.getElementById('topCarichiChart').getContext('2d');
const topCarichiChart = new Chart(ctxCarichi, {
    type: 'pie',
    data: {
        labels: <?php echo json_encode($libri_carichi); ?>,
        datasets: [{
            label: 'Caricati',
            data: <?php echo json_encode($quantita_carichi); ?>,
            backgroundColor: [
                'rgba(39, 174, 96, 0.7)',
                'rgba(41, 128, 185, 0.7)',
                'rgba(142, 68, 173, 0.7)',
                'rgba(243, 156, 18, 0.7)',
                'rgba(192, 57, 43, 0.7)'
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

// Grafico confronto carichi e scarichi
const ctxConfronto = document.getElementById('confrontoCarichiScarichiChart').getContext('2d');
const confrontoChart = new Chart(ctxConfronto, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($libri_confronto); ?>,
        datasets: [
            {
                label: 'Caricati',
                data: <?php echo json_encode($carichi_confronto); ?>,
                backgroundColor: 'rgba(39, 174, 96, 0.7)'
            },
            {
                label: 'Scaricati',
                data: <?php echo json_encode($scarichi_confronto); ?>,
                backgroundColor: 'rgba(231, 76, 60, 0.7)'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true, position: 'bottom' },
            title: { display: false }
        },
        scales: {
            x: { stacked: false },
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
