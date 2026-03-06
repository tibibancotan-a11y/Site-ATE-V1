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

// Află statusul curent
$stmt = $conn->prepare("SELECT activ FROM anunturi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
  die("Anunț inexistent.");
}

// Inversează statusul
$nou_status = $row['activ'] ? 0 : 1;

$stmt = $conn->prepare("UPDATE anunturi SET activ = ? WHERE id = ?");
$stmt->bind_param("ii", $nou_status, $id);
$stmt->execute();
$stmt->close();

header('Location: lista_anunturi.php');
exit;
?>
