<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}

include_once 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
  die("ID invalid.");
}

// Obține imaginile pentru ștergere
$stmt = $conn->prepare("SELECT imagini FROM anunturi WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$anunt = $res->fetch_assoc();
$stmt->close();

// Șterge fișierele media de pe server
if ($anunt && $anunt['imagini']) {
  $lista = explode(';', $anunt['imagini']);
  foreach ($lista as $fisier) {
    $cale = '../' . $fisier;
    if (file_exists($cale)) {
      unlink($cale);
    }
  }
}

// Șterge din DB
$stmt = $conn->prepare("DELETE FROM anunturi WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header('Location: lista_anunturi.php');
exit;
?>
