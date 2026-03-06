<?php
// 🔐 Pornim sesiunea pentru a verifica autentificarea adminului
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

// 🔗 Conectare la baza de date
include_once 'db.php';

// ✅ Inițializăm variabile pentru mesaje
$mesaj = "";
$eroare = "";

// 🧾 Dacă formularul a fost trimis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $parola_veche    = $_POST['parola_veche'] ?? '';
    $parola_noua     = $_POST['parola_noua'] ?? '';
    $confirm_parola  = $_POST['confirm_parola'] ?? '';

    // 🔍 Verificăm dacă toate câmpurile sunt completate
    if (empty($parola_veche) || empty($parola_noua) || empty($confirm_parola)) {
        $eroare = "Completează toate câmpurile!";
    } elseif ($parola_noua !== $confirm_parola) {
        $eroare = "Parola nouă și confirmarea nu coincid!";
    } else {
        // 🧠 Obținem parola actuală din baza de date
        $rezultat = $conn->query("SELECT parola FROM admin WHERE username='admin'");
        $row = $rezultat->fetch_assoc();

        if ($row && password_verify($parola_veche, $row['parola'])) {
            // ✅ Generăm hash pentru parola nouă
            $parola_hash = password_hash($parola_noua, PASSWORD_BCRYPT);

            // 🔄 Actualizăm parola în baza de date
            if ($conn->query("UPDATE admin SET parola='$parola_hash' WHERE username='admin'")) {
                $mesaj = "Parola a fost schimbată cu succes!";
            } else {
                $eroare = "Eroare la actualizarea parolei!";
            }
        } else {
            $eroare = "Parola veche este incorectă!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Schimbare Parola</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f8f8;
            padding: 30px;
        }
        .container {
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        input[type="password"], button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        .mesaj { color: green; text-align: center; margin-bottom: 10px; }
        .eroare { color: red; text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2 style="text-align:center;">Schimbă Parola</h2>

    <?php if ($mesaj): ?>
        <p class="mesaj"><?= htmlspecialchars($mesaj) ?></p>
    <?php endif; ?>

    <?php if ($eroare): ?>
        <p class="eroare"><?= htmlspecialchars($eroare) ?></p>
    <?php endif; ?>

    <!-- Formular schimbare parolă -->
    <form method="post">
        <input type="password" name="parola_veche" placeholder="Parola veche" required>
        <input type="password" name="parola_noua" placeholder="Parola nouă" required>
        <input type="password" name="confirm_parola" placeholder="Confirmă parola nouă" required>
        <button type="submit">Salvează</button>
    </form>

    <div style="text-align:center; margin-top:10px;">
        <a href="panel.php">← Înapoi la Panou</a>
    </div>
</div>
</body>
</html>
