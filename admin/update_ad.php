<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}

include_once 'db.php';

$id = $_POST['id'] ?? null;
if (!$id || !is_numeric($id)) {
  die('ID invalid.');
}

$titlu = $conn->real_escape_string($_POST['titlu']);
$descriere = $conn->real_escape_string($_POST['descriere']);
$categorie = $conn->real_escape_string($_POST['categorie']);
$pret = $conn->real_escape_string($_POST['pret']);
$moneda = $conn->real_escape_string($_POST['moneda']);

// 1. Preluăm imaginile vechi din DB
$result = $conn->query("SELECT imagini FROM anunturi WHERE id = $id");
$row = $result->fetch_assoc();
$imagini_vechi = explode(';', $row['imagini'] ?? '');
$imagini_noi = [];

// 2. Ștergem imaginile bifate pentru ștergere
$de_sters = $_POST['sterge_imagini'] ?? [];
foreach ($imagini_vechi as $img) {
  if (in_array($img, $de_sters)) {
    $cale = "../uploads/$img";
    if (file_exists($cale)) unlink($cale);
  } else {
    $imagini_noi[] = $img;
  }
}

// 3. Procesăm noile fișiere încărcate
if (!empty($_FILES['imagini']['name'][0])) {
  foreach ($_FILES['imagini']['tmp_name'] as $index => $tmp) {
    if (is_uploaded_file($tmp)) {
      $ext = pathinfo($_FILES['imagini']['name'][$index], PATHINFO_EXTENSION);
      $nume = time() . '_' . rand(1000, 9999) . '.' . $ext;
      move_uploaded_file($tmp, "../uploads/$nume");
      $imagini_noi[] = $nume;
    }
  }
}

// 4. Salvăm înapoi în baza de date
$lista_finala = implode(';', $imagini_noi);

$stmt = $conn->prepare("UPDATE anunturi SET titlu=?, descriere=?, categorie=?, pret=?, moneda=?, imagini=? WHERE id=?");
$stmt->bind_param("ssssssi", $titlu, $descriere, $categorie, $pret, $moneda, $lista_finala, $id);
$stmt->execute();

header("Location: panel.php");
exit;
