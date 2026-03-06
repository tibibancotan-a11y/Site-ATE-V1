<?php
// 🔐 Pornim sesiunea pentru a avea acces la datele existente
session_start();

// ❌ Distrugem sesiunea curentă (ștergem toate datele de autentificare)
session_destroy();

// 🔁 Redirecționăm utilizatorul înapoi către pagina de login
header('Location: login.php');
exit;
