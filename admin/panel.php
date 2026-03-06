<?php
// 🔐 Pornire sesiune și verificare autentificare admin
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// 🔗 Conectare la baza de date și includere categorii
include_once '../admin/db.php';
include_once '../includes/categorii.php';

// 🧮 Contor anunțuri active / inactive pentru afișare sumară
$stats = ['active' => 0, 'inactive' => 0];
$result = $conn->query("SELECT activ, COUNT(*) as total FROM anunturi GROUP BY activ");
while ($row = $result->fetch_assoc()) {
    if ($row['activ'] === 'da') {
        $stats['active'] = $row['total'];
    } else {
        $stats['inactive'] = $row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panou Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>

    <!-- 🔰 Titlu principal + statistici generale -->
    <h2 style="text-align:center;">Panou Administrator</h2>
    <div class="statistici" style="text-align:center; margin-bottom: 10px;">
        <p>Anunțuri active: <?= $stats['active'] ?> / inactive: <?= $stats['inactive'] ?></p>
    </div>

    <!-- 📋 Formular creare anunț nou -->
    <form action="save_ad.php" method="POST" enctype="multipart/form-data" style="max-width: 600px; margin: auto;">
        <label>Titlu:</label>
        <input type="text" name="titlu" required maxlength="100"><br>

        <label>Descriere:</label>
        <textarea name="descriere" maxlength="500" required></textarea><br>

        <label>Poze (maxim 10 imagini/video):</label>
        <input type="file" name="imagini[]" accept="image/*,video/*" multiple required><br>

        <label>Categorie:</label>
        <select name="categorie" required>
            <?php foreach ($categorii_utilaje as $slug => $nume): ?>
                <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($nume) ?></option>
            <?php endforeach; ?>
        </select>
        <br>

        <label>Pret:</label>
        <input type="text" name="pret"><br>

        <label>Moneda:</label>
        <select name="moneda">
            <option value="EUR">EUR</option>
            <option value="RON">RON</option>
        </select><br><br>

        <button type="submit">Salvează anunț</button>
    </form>

    <hr>

    <!-- 📄 Listare anunțuri existente + filtre -->
    <h3 style="text-align:center;">Lista anunțuri existente</h3>

    <!-- 🔍 Filtre după stare și categorie -->
    <form method="get" style="text-align:center; margin-bottom:10px;">
        <label>Stare:
            <select name="filtru_status">
                <option value="">Toate</option>
                <option value="da" <?= ($_GET['filtru_status'] ?? '') === 'da' ? 'selected' : '' ?>>Active</option>
                <option value="nu" <?= ($_GET['filtru_status'] ?? '') === 'nu' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </label>

        <label style="margin-left:20px;">Categorie:
            <select name="filtru_categorie">
                <option value="">Toate</option>
                <?php foreach ($categorii_utilaje as $slug => $nume): ?>
                    <option value="<?= htmlspecialchars($slug) ?>" <?= ($_GET['filtru_categorie'] ?? '') === $slug ? 'selected' : '' ?>><?= htmlspecialchars($nume) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <button type="submit" style="margin-left: 10px;">Filtrează</button>
    </form>

<?php
// 🧩 Preluare filtre din formular
$filtru_status = $_GET['filtru_status'] ?? '';
$filtru_categorie = $_GET['filtru_categorie'] ?? '';

// 🔍 Construire condiții SQL pe baza filtrelor
$conditii = [];
if ($filtru_status !== '') {
    $conditii[] = "activ = '" . $conn->real_escape_string($filtru_status) . "'";
}
if ($filtru_categorie !== '') {
    $conditii[] = "categorie = '" . $conn->real_escape_string($filtru_categorie) . "'";
}

$sql = "SELECT * FROM anunturi";
if (!empty($conditii)) {
    $sql .= " WHERE " . implode(" AND ", $conditii);
}
$sql .= " ORDER BY data DESC";

// ▶️ Rulăm interogarea
$result = $conn->query($sql);
?>

    <!-- 📊 Afișare tabel cu anunțuri și acțiuni -->
    <table border="1" cellpadding="6" cellspacing="0" style="margin:auto;">
        <tr>
            <th>ID</th>
            <th>Titlu</th>
            <th>Categorie</th>
            <th>Preț</th>
            <th>Moneda</th>
            <th>Dată</th>
            <th>Activ</th>
            <th>Acțiuni</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['titlu']) ?></td>
            <td><?= htmlspecialchars($categorii_utilaje[$row['categorie']] ?? $row['categorie']) ?></td>
            <td><?= htmlspecialchars($row['pret']) ?></td>
            <td><?= htmlspecialchars($row['moneda']) ?></td>
            <td><?= htmlspecialchars($row['data']) ?></td>
            <td><?= $row['activ'] === 'da' ? '✔' : '✖' ?></td>
            <td>
                <!-- 🔧 Acțiuni: Editare, Ștergere, Activare/Inactivare -->
                <a href="edit.php?id=<?= $row['id'] ?>">✎ Editare</a> |
                <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Ștergi anunțul?')">🗑 Șterge</a> |
                <a href="toggle_status.php?id=<?= $row['id'] ?>">
                    <?= $row['activ'] === 'da' ? '🔒 Dezactivează' : '🔓 Activează' ?>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <!-- 🔚 Linkuri pentru schimbare parolă și logout -->
    <hr style="margin-top:30px;">
    <div style="text-align:center; margin-top:10px;">
        <a href="schimba_parola.php">Schimbă parola</a> |
        <a href="logout.php">Ieșire admin</a>
    </div>

</body>
</html>
