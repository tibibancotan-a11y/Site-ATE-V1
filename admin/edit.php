<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
include_once 'db.php';
include_once __DIR__ . '/../includes/categorii.php';

$id = $_GET['id'] ?? null;
if (!$id) { die("ID invalid."); }

$stmt = $conn->prepare("SELECT * FROM anunturi WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$rezultat = $stmt->get_result();
$anunt = $rezultat->fetch_assoc();
if (!$anunt) { die("Anuntul nu a fost gasit."); }
$imagini = explode(";", $anunt['imagini']);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <title>Editare Anunt</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container">
  <h2>Editare Anunt</h2>

  <div class="toolbar" style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin:10px 0 18px;">
    <a href="lista_anunturi.php" class="btn-back" style="text-decoration:none;display:inline-block;padding:10px 14px;border:1px solid #d1d5db;border-radius:10px;background:#f9fafb;">⬅ Înapoi la anunțuri</a>
  </div>

  <form action="save_edit.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $anunt['id'] ?>">
    <label>Titlu:</label>
    <input type="text" name="titlu" value="<?= htmlspecialchars($anunt['titlu']) ?>" required>
    <label>Descriere:</label>
    <textarea name="descriere"><?= htmlspecialchars($anunt['descriere']) ?></textarea>
    <label>Categorie:</label>
    <select name="categorie" required>
      <?php foreach ($categorii_utilaje as $slug => $nume): ?>
        <option value="<?= htmlspecialchars($slug) ?>" <?= ($anunt['categorie'] ?? '') === $slug ? 'selected' : '' ?>><?= htmlspecialchars($nume) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Pret (EUR):</label>
    <input type="number" name="pret" value="<?= $anunt['pret'] ?>" required>
    <label>Moneda:</label>
    <select name="moneda">
      <option value="RON" <?= $anunt['moneda']=='RON'?'selected':'' ?>>RON</option>
      <option value="EUR" <?= $anunt['moneda']=='EUR'?'selected':'' ?>>EUR</option>
    </select>
    <label>TVA inclus:</label>
    <input type="checkbox" name="tva_inclus" value="1" <?= $anunt['tva_inclus']?'checked':'' ?>>

    <!-- ========== GALERIE IMAGINI EXISTENTE CU BIFE DE ȘTERGERE ========== -->
    <?php
      $imagini_existente = array_values(array_filter(array_map('trim', explode(';', $anunt['imagini'] ?? ''))));
    ?>
    <style>
      /* Stilare doar pentru această pagină */
      .images-wrap{margin:18px 0;}
      .images-grid{
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap:16px;
        align-items:start;
      }
      .thumb{
        position:relative;
        display:block;
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius:12px;
        padding:10px;
        box-shadow:0 1px 2px rgba(0,0,0,.04);
        transition:box-shadow .2s, transform .2s;
      }
      .thumb:hover{ box-shadow:0 6px 20px rgba(0,0,0,.07); transform:translateY(-1px); }
      .thumb img{
        width:100%;
        aspect-ratio: 4 / 3;
        object-fit:cover;
        border-radius:8px;
        display:block;
        user-select:none;
      }
      .thumb .meta{
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-top:8px;
        font-size:12px;
        color:#374151;
      }
      .thumb .meta .name{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:70%; }
      .thumb .meta .chk{ display:flex; gap:6px; align-items:center; }
      .images-empty{
        background:#f9fafb;
        border:1px dashed #d1d5db;
        padding:18px;
        border-radius:12px;
        color:#6b7280;
        font-size:14px;
      }
    </style>

    <div class="images-wrap">
      <h3>Imagini încărcate</h3>
      <?php if (!empty($imagini_existente)): ?>
        <div class="images-grid">
          <?php foreach ($imagini_existente as $img): 
                $safe = htmlspecialchars($img, ENT_QUOTES, 'UTF-8');
                $src  = '../uploads/' . rawurlencode($img);
          ?>
            <label class="thumb">
              <img src="<?= $src ?>" alt="">
              <div class="meta">
                <span class="name" title="<?= $safe ?>"><?= $safe ?></span>
                <span class="chk"><input type="checkbox" name="delete_images[]" value="<?= $safe ?>"> Șterge</span>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="images-empty">Nu există imagini pentru acest anunț.</div>
      <?php endif; ?>
    </div>
    <!-- ========== /GALERIE ========== -->

    <button type="submit">Salvează</button>
  </form>
</div>
</body>
</html>
