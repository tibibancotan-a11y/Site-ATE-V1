<?php
session_start();
if (isset($_SESSION['admin'])) {
  header('Location: panel.php');
  exit;
}

$eroare = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $parola_corecta = 'Admin123';
  $parola_introdusa = $_POST['parola'] ?? '';
  if ($parola_introdusa === $parola_corecta) {
    $_SESSION['admin'] = true;
    header('Location: panel.php');
    exit;
  } else {
    $eroare = 'Parolă incorectă';
  }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Autentificare Admin</title>
</head>
<body>
  <h2>Login Administrator</h2>
  <form method="POST">
    <input type="password" name="parola" placeholder="Parolă" required>
    <button type="submit">Autentificare</button>
  </form>
  <?php if ($eroare): ?>
    <p style="color:red;"><?= htmlspecialchars($eroare) ?></p>
  <?php endif; ?>
</body>
</html>