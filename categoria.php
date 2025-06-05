<?php
// categoria.php - Gestione CRUD categorie libri (ENUM)
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
require_once 'connessione.php';

// Recupero le categorie possibili dall'ENUM della tabella libri
$categorie = [];
$res_enum = $conn->query("SHOW COLUMNS FROM libri LIKE 'categoria'");
if ($res_enum) {
    $row_enum = $res_enum->fetch_assoc();
    if (preg_match("/enum\\((.*)\\)/", $row_enum['Type'], $matches)) {
        $vals = explode(",", str_replace("'", "", $matches[1]));
        foreach ($vals as $val) {
            $categorie[] = trim($val);
        }
    }
}
// Gestione aggiunta categoria (ALTER TABLE)
if (isset($_POST['azione']) && $_POST['azione'] === 'inserisci') {
    $nuova = trim($_POST['nome']);
    if ($nuova !== '' && !in_array($nuova, $categorie)) {
        $categorie[] = $nuova;
        $enum = "'" . implode("','", $categorie) . "'";
        $conn->query("ALTER TABLE libri MODIFY categoria ENUM($enum) NOT NULL");
    }
    header('Location: categoria.php');
    exit;
}
// Gestione modifica categoria (rename)
if (isset($_POST['azione']) && $_POST['azione'] === 'modifica' && isset($_POST['old_nome'])) {
    $old = trim($_POST['old_nome']);
    $new = trim($_POST['nome']);
    if ($old !== '' && $new !== '' && $old !== $new && in_array($old, $categorie) && !in_array($new, $categorie)) {
        $nuove_categorie = array_map(function($cat) use ($old, $new) { return $cat === $old ? $new : $cat; }, $categorie);
        $enum = "'" . implode("','", $nuove_categorie) . "'";
        $conn->query("ALTER TABLE libri MODIFY categoria ENUM($enum) NOT NULL");
        $conn->query("UPDATE libri SET categoria = '".$conn->real_escape_string($new)."' WHERE categoria = '".$conn->real_escape_string($old)."'");
    }
    header('Location: categoria.php');
    exit;
}
// Gestione elimina categoria
if (isset($_GET['elimina'])) {
    $del = $_GET['elimina'];
    if (in_array($del, $categorie)) {
        $nuove_categorie = array_filter($categorie, function($cat) use ($del) { return $cat !== $del; });
        if (count($nuove_categorie) > 0) {
            $enum = "'" . implode("','", $nuove_categorie) . "'";
            $conn->query("ALTER TABLE libri MODIFY categoria ENUM($enum) NOT NULL");
            // Aggiorna i libri che avevano questa categoria (opzionale: metti la prima categoria disponibile)
            $prima = reset($nuove_categorie);
            $conn->query("UPDATE libri SET categoria = '".$conn->real_escape_string($prima)."' WHERE categoria = '".$conn->real_escape_string($del)."'");
        }
    }
    header('Location: categoria.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestione Categorie</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-50 min-h-screen">
<?php require_once 'header.php'; ?>
<main class="max-w-2xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-blue-900 mb-6">Gestione Categorie</h1>
    <form method="post" class="flex flex-wrap gap-3 items-center bg-white rounded-lg shadow px-4 py-3 mb-6">
        <input type="text" name="nome" placeholder="Nuova categoria" required class="border border-gray-300 rounded px-3 py-1 focus:ring-blue-500 focus:border-blue-500" />
        <button type="submit" name="azione" value="inserisci" class="bg-green-600 hover:bg-green-800 text-white px-4 py-1 rounded transition">Aggiungi</button>
    </form>
    <div class="overflow-x-auto rounded-lg shadow bg-white">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase">Categoria</th>
                    <th class="px-4 py-2"></th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categorie as $cat): ?>
                <tr class="hover:bg-blue-50">
                    <td class="px-4 py-2"><?php echo htmlspecialchars($cat); ?></td>
                    <td class="px-4 py-2">
                        <form method="post" class="flex gap-2 items-center">
                            <input type="hidden" name="old_nome" value="<?php echo htmlspecialchars($cat); ?>">
                            <input type="text" name="nome" placeholder="Nuovo nome" class="border border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500" />
                            <button type="submit" name="azione" value="modifica" class="bg-blue-600 hover:bg-blue-800 text-white px-3 py-1 rounded transition">Rinomina</button>
                        </form>
                    </td>
                    <td class="px-4 py-2">
                        <a href="categoria.php?elimina=<?php echo urlencode($cat); ?>" class="bg-red-500 hover:bg-red-700 text-white px-3 py-1 rounded transition" onclick="return confirm('Eliminare la categoria?')">Elimina</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
