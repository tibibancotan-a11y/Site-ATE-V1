<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}

include_once 'db.php';

$id = $_POST['id'] ?? null;
$titlu = trim($_POST['titlu'] ?? '');
$descriere = trim($_POST['descriere'] ?? '');
$categorie = $_POST['categorie'] ?? '';
$pret = $_POST['pret'] ?? '';
$moneda = $_POST['moneda'] ?? 'EUR';

if (!$id || !$titlu || !$descriere || !$categorie) {
  die("Date lipsă sau incorecte.");
}

$stmt = $conn->prepare("UPDATE anunturi SET titlu=?, descriere=?, categorie=?, pret=?, moneda=? WHERE id=?");
$stmt->bind_param("sssssi", $titlu, $descriere, $categorie, $pret, $moneda, $id);
$stmt->execute();
$stmt->close();

header('Location: lista_anunturi.php');
exit;
?>
