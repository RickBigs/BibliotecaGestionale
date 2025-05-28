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
    <title>Gestione Categorie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <style>
        .categorie-container {
            max-width: 500px;
            margin: 2rem auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            padding: 2rem 1.5rem 1.5rem 1.5rem;
        }
        .categorie-container h1 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .categorie-form {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .categorie-form input[type="text"] {
            flex: 1 1 180px;
        }
        .categorie-table {
            width: 100%;
            border-collapse: collapse;
        }
        .categorie-table th, .categorie-table td {
            padding: 0.7rem 0.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .categorie-table th {
            background: #f1f5f9;
            color: #2563eb;
            font-weight: 600;
        }
        .categorie-table tr:last-child td {
            border-bottom: none;
        }
        .categorie-table input[type="text"] {
            width: 100%;
            min-width: 80px;
        }
        @media (max-width: 600px) {
            .categorie-container {
                padding: 1rem 0.3rem;
            }
            .categorie-form {
                flex-direction: column;
                gap: 0.7rem;
            }
        }
    </style>
</head>
<body>
<?php require_once 'header.php'; ?>
<div class="categorie-container">
    <h1>Gestione Categorie</h1>
    <form method="post" class="categorie-form" autocomplete="off" aria-label="Aggiungi categoria">
        <input type="hidden" name="azione" value="inserisci">
        <input type="text" name="nome" placeholder="Nuova categoria" required aria-label="Nome nuova categoria">
        <button type="submit" class="bottone">Aggiungi</button>
    </form>
    <div class="table-wrapper">
        <table class="categorie-table" aria-label="Elenco categorie">
            <thead>
                <tr><th>Categoria</th><th>Azioni</th></tr>
            </thead>
            <tbody>
            <?php foreach ($categorie as $cat): ?>
                <tr>
                    <td style="width:80%">
                
                    <form method="post" style="display:flex;gap:0.3rem;align-items:center;">
                    <input type="hidden" name="azione" value="modifica">
                    <input type="hidden" name="old_nome" value="<?php echo htmlspecialchars($cat); ?>">
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($cat); ?>" required aria-label="Modifica categoria">
                    </form>
                    <button type="submit" class="bottone" title="Rinomina categoria">Modifica</button>
                    </td>
                    <td style="width:40%">
                    <a href="categoria.php?elimina=<?php echo urlencode($cat); ?>"
                    class="bottone-elimina"
                    onclick="return confirm('Eliminare la categoria? Tutti i libri con questa categoria verranno aggiornati!')"
                    title="Elimina categoria"
                    aria-label="Elimina categoria">Elimina</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="margin-top:1.5rem;color:#888;font-size:0.97em;text-align:center;">Le categorie sono gestite come valori ENUM nel database. Eliminando una categoria, i libri associati verranno aggiornati alla prima categoria disponibile.</p>
</div>
</body>
</html>
