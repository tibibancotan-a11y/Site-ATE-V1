<?php
// db.php - main database connection file

// If db.local.php exists, include it (contains real credentials)
if (file_exists(__DIR__ . '/db.local.php')) {
    include __DIR__ . '/db.local.php';
} else {
    // Placeholder values - safe to commit
    $servername = "localhost";
    $username   = "r70111agro_adminate";
    $password   = "7]y0y7=XMHS2,Z7~";
    $dbname     = "r70111agro_agroteamexpert";
}

$conn = new mysqli($servername, $username, $password, $dbname);

if (method_exists($conn, 'set_charset')) { $conn->set_charset('utf8mb4'); }
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}
$conn->set_charset("utf8"); // ← Pentru caractere românești fără probleme
?>
