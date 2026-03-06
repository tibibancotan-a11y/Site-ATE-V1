<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

include_once 'db.php';

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    die("ID invalid");
}

// ✅ Preluăm datele din formular
$titlu     = trim($_POST['titlu'] ?? '');
$descriere = trim($_POST['descriere'] ?? '');
$categorie = trim($_POST['categorie'] ?? '');
$pret      = floatval($_POST['pret'] ?? 0);
$valuta    = $_POST['moneda'] ?? 'EUR';
$tva_inclus = isset($_POST['tva_inclus']) ? 1 : 0;

// ✅ Obținem imaginile existente din DB
$sql = "SELECT imagini FROM anunturi WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$anunt = $result->fetch_assoc();
$stmt->close();

$imagini_existente = array_filter(explode(";", $anunt['imagini'] ?? ''));

// ✅ Ștergere imagini selectate (dacă există checkbox în formular)
$imagini_de_sters = $_POST['sterge_imagini'] ?? [];
if (!empty($imagini_de_sters)) {
    foreach ($imagini_de_sters as $img) {
        $img = basename($img);
        $cale = '../uploads/' . $img;
        if (file_exists($cale)) {
            unlink($cale);
        }
        // Eliminăm din lista existentă
        $key = array_search($img, $imagini_existente);
        if ($key !== false) {
            unset($imagini_existente[$key]);
        }
    }
}

// ✅ Upload imagini noi (maxim 10 în total)
$imagini_noi = [];
if (!empty($_FILES['imagini']['name'][0])) {
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $total_imagini_actuale = count($imagini_existente);
    $sloturi_ramase = max(0, 10 - $total_imagini_actuale);
    $numar_upload = min(count($_FILES['imagini']['name']), $sloturi_ramase);

    for ($i = 0; $i < $numar_upload; $i++) {
        if ($_FILES['imagini']['error'][$i] === 0) {
            $ext = strtolower(pathinfo($_FILES['imagini']['name'][$i], PATHINFO_EXTENSION));
            $ext_permise = ['jpg','jpeg','png','gif','webp','mp4','webm'];
            if (!in_array($ext, $ext_permise)) {
                continue;
            }

            $nume_fisier = uniqid('file_') . '.' . $ext;
            $destinatie = $upload_dir . $nume_fisier;

            if (move_uploaded_file($_FILES['imagini']['tmp_name'][$i], $destinatie)) {
                $imagini_noi[] = $nume_fisier;
            }
        }
    }
}

// 🔄 Ștergere imagini selectate (bife din formular)
$delete_list = isset($_POST['delete_images']) ? (array)$_POST['delete_images'] : [];
$delete_list = array_values(array_filter(array_map('trim', $delete_list)));

// Obținem lista existentă de imagini (dacă nu e deja definită)
if (!isset($imagini_existente)) {
    $res = $conn->query("SELECT imagini FROM anunturi WHERE id = " . intval($id) . " LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    $imagini_existente = $row && !empty($row['imagini']) ? array_values(array_filter(array_map('trim', explode(';', $row['imagini'])))) : [];
}

// Aplicăm ștergerile
if (!empty($delete_list)) {
    $ramase = array_values(array_diff($imagini_existente, $delete_list));

    // Ștergem fișierele fizice din /uploads
    foreach ($delete_list as $fname) {
        $p = __DIR__ . '/../uploads/' . $fname;
        if (is_file($p)) { @unlink($p); }
    }

    $imagini_existente = $ramase;
}

// ✅ Refacem lista finală de imagini
$imagini_final = array_merge($imagini_existente, $imagini_noi);
$imagini_final_str = implode(';', $imagini_final);


// Asigurăm că avem categoria din formular
if (!isset($categorie)) { $categorie = trim($_POST['categorie'] ?? ''); }
// ✅ Actualizăm datele în DB cu TVA inclus
$stmt = $conn->prepare("UPDATE anunturi SET titlu=?, descriere=?, categorie=?, pret=?, moneda=?, imagini=?, tva_inclus=? WHERE id=?");
if (!$stmt) { die("Eroare SQL la prepare: " . $conn->error); }
$stmt->bind_param("ssssssii", $titlu, $descriere, $categorie, $pret, $valuta, $imagini_final_str, $tva_inclus, $id);
$stmt->execute();
$stmt->close();

header("Location: lista_anunturi.php");
exit;
?>
