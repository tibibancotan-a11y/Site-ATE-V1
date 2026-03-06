<?php
// 🔐 Verificăm autentificarea admin
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// 🔗 Conectare la baza de date
include_once 'db.php';

// ✅ Preluare date din formular
$titlu     = trim($_POST['titlu'] ?? '');
$descriere = trim($_POST['descriere'] ?? '');
$categorie = trim($_POST['categorie'] ?? '');
$pret      = floatval($_POST['pret'] ?? 0);
$valuta    = $_POST['moneda'] ?? 'EUR';
$tva_inclus = isset($_POST['tva_inclus']) ? 1 : 0;

$imagini_salvate = [];

// ✅ Upload fișiere (imagini și video)
if (!empty($_FILES['imagini']['name'][0])) {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Limităm la maxim 10 fișiere
    $total_files = min(count($_FILES['imagini']['name']), 10);

    for ($i = 0; $i < $total_files; $i++) {
        if ($_FILES['imagini']['error'][$i] === 0) {
            $ext = strtolower(pathinfo($_FILES['imagini']['name'][$i], PATHINFO_EXTENSION));

            // ✅ Acceptăm doar imagini și video (jpg, jpeg, png, gif, webp, mp4, webm)
            $ext_permise = ['jpg','jpeg','png','gif','webp','mp4','webm'];
            if (!in_array($ext, $ext_permise)) {
                continue; // Sare peste extensii nepermise
            }

            // ✅ Nume unic pentru fișier
            $nume_fisier = uniqid('file_') . '.' . $ext;
            $destinatie = $upload_dir . $nume_fisier;

            if (move_uploaded_file($_FILES['imagini']['tmp_name'][$i], $destinatie)) {
                // ❗ Salvăm doar numele fișierului (fără uploads/) în DB
                $imagini_salvate[] = $nume_fisier;
            }
        }
    }
}

// ✅ Creăm lista finală de fișiere separate prin `;`
$imagini_final = implode(';', $imagini_salvate);

// ✅ Inserăm datele în DB cu câmp TVA
$stmt = $conn->prepare("INSERT INTO anunturi (titlu, descriere, categorie, pret, moneda, imagini, tva_inclus, activ, data) VALUES (?, ?, ?, ?, ?, ?, ?, 'da', NOW())");
if (!$stmt) {
    die("Eroare SQL la prepare: " . $conn->error);
}
$stmt->bind_param("ssssssi", $titlu, $descriere, $categorie, $pret, $valuta, $imagini_final, $tva_inclus);
$stmt->execute();
$stmt->close();

header("Location: lista_anunturi.php");
exit;
?>
