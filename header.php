<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'connessione.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestione Biblioteca</title>
</head>
<body class="bg-gray-50">
<?php if (isset($_SESSION['username'])): ?>
    <div class="absolute top-4 right-8 flex items-center gap-2">
        <span class="user-badge flex items-center gap-1 bg-blue-100 text-blue-900 px-3 py-1 rounded font-medium">
            <span class="user-icon">👤</span><?php echo htmlspecialchars($_SESSION['username']); ?>
            <span class="opacity-70 text-sm">(<?php echo $_SESSION['ruolo']; ?>)</span>
        </span>
        <a href="logout.php" class="bg-red-600 hover:bg-red-800 text-white px-3 py-1 rounded transition">Logout</a>
        <?php if ($_SESSION['ruolo'] === 'Admin'): ?>
            <a href="registrazioneUtente.php" class="bg-green-700 hover:bg-green-900 text-white px-3 py-1 rounded transition">Registra Utente</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
<header class="bg-primary-700 text-white shadow mb-8">
    <div class="container mx-auto px-4 py-4 flex flex-col md:flex-row md:items-center md:justify-between">
        <h1 id="title-header" class="text-3xl font-bold tracking-tight">Gestione Biblioteca</h1>
        <nav id="navbar" class="mt-4 md:mt-0">
            <ul class="flex flex-wrap gap-4 md:gap-6 items-center">
                <li><a href="index.php" class="hover:bg-primary-800 px-3 py-1 rounded transition">Homepage</a></li>
                <li><a href="categoria.php" class="hover:bg-primary-800 px-3 py-1 rounded transition">Categorie</a></li>
                <li><a href="autori.php" class="hover:bg-primary-800 px-3 py-1 rounded transition">Gestione Autori</a></li>
                <li><a href="libri.php" class="hover:bg-primary-800 px-3 py-1 rounded transition">Gestione Libri</a></li>
                <li><a href="magazzino.php" class="hover:bg-primary-800 px-3 py-1 rounded transition">Gestione Magazzino</a></li>
                <li><a href="statistiche.php" class="hover:bg-primary-800 px-3 py-1 rounded transition">Statistiche & Contabilità</a></li>
            </ul>
        </nav>
    </div>
</header>
</body>
</html>
