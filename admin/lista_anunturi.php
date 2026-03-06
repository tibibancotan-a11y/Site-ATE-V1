<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}
include_once 'db.php';
include_once '../includes/functii.php';

$result = $conn->query("SELECT * FROM anunturi ORDER BY data DESC");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Lista Anunțuri</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <h2 style="text-align:center;">Administrare Anunțuri</h2>

  <table border="1" cellpadding="6" cellspacing="0" style="width:90%; margin:auto; background:white;">
    <tr style="background:#eee;">
      <th>ID</th>
      <th>Titlu</th>
      <th>Categorie</th>
      <th>Preț</th>
      <th>Status</th>
      <th>Acțiuni</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $row['id'] ?></td>
      <td><?= htmlspecialchars($row['titlu']) ?></td>
      <td><?= htmlspecialchars($row['categorie']) ?></td>
      <td><?= htmlspecialchars($row['pret']) ?> <?= $row['moneda'] ?? 'EUR' ?></td>
      <td><?= $row['activ'] ? 'Activ' : 'Inactiv' ?></td>
      <td>
        <a href="edit.php?id=<?= $row['id'] ?>">✏️ Editare</a> |
        <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Sigur vrei să ștergi?')">🗑️ Ștergere</a> |
        <a href="toggle.php?id=<?= $row['id'] ?>">
          <?= $row['activ'] ? '🔒 Dezactivează' : '✅ Activează' ?>
        </a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>

  <div style="text-align:center; margin-top:20px;">
    <a href="panel.php">⬅ Înapoi la Panou</a>
  </div>
</body>
</html>
