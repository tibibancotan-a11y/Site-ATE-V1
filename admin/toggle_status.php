<?php
// 🔐 Pornim sesiunea și verificăm autentificarea adminului
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// 🔗 Conectare la baza de date
include_once 'db.php';

// 🧾 Preluăm ID-ul anunțului din URL și îl convertim în integer
$id = (int)($_GET['id'] ?? 0);

// ✅ Verificăm dacă ID-ul este valid (> 0)
if ($id > 0) {
    // 🔍 Obținem statusul curent al anunțului (activ/inactiv)
    $result = $conn->query("SELECT activ FROM anunturi WHERE id = $id");
    if ($row = $result->fetch_assoc()) {
        // 🔄 Determinăm noul status (inversăm între 'da' și 'nu')
        $nou_status = ($row['activ'] === 'da') ? 'nu' : 'da';

        // 📝 Actualizăm statusul în baza de date
        $conn->query("UPDATE anunturi SET activ = '$nou_status' WHERE id = $id");
    }
}

// 🔁 Redirecționăm către panoul admin
header('Location: panel.php');
exit;
