<?php
header('Content-Type: text/html; charset=UTF-8');
include_once 'admin/db.php';

$grupa_selectata = $_GET['grupa'] ?? '';
$tip_selectat    = $_GET['tip']   ?? '';

$conditii = ["activ='da'"];

include_once 'includes/categorii.php';

// Filtrare pe grupă (folosim slug + numele complet, pentru compatibilitate)
if ($grupa_selectata && isset($grupe_utilaje_sluguri[$grupa_selectata])) {
    $slugs = $grupe_utilaje_sluguri[$grupa_selectata];
    $labels = [];
    foreach ($slugs as $slug) {
        if (isset($categorii_utilaje[$slug])) {
            $labels[] = $categorii_utilaje[$slug];
        }
    }
    $valori = array_unique(array_merge($slugs, $labels));
    $esc = array_map([$conn, 'real_escape_string'], $valori);
    if (!empty($esc)) {
        $conditii[] = "categorie IN ('" . implode("','", $esc) . "')";
    }
}

// Filtrare pe tip (subcategorie)
if ($tip_selectat) {
    $valori = [];
    if (isset($categorii_utilaje[$tip_selectat])) {
        $slug  = $tip_selectat;
        $label = $categorii_utilaje[$slug];
        $valori = [$slug, $label];
    } else {
        $valori = [$tip_selectat];
    }
    $valori = array_unique($valori);
    $esc = array_map([$conn, 'real_escape_string'], $valori);
    if (!empty($esc)) {
        $conditii[] = "categorie IN ('" . implode("','", $esc) . "')";
    }
}

$sql = "SELECT * FROM anunturi WHERE " . implode(' AND ', $conditii) . " ORDER BY data DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0):
    while ($row = $result->fetch_assoc()):
        // imagini curate + prima imagine
        $imagini = array_values(array_filter(explode(';', (string)$row['imagini'])));
        $prima_imagine = $imagini[0] ?? 'images/no-image.jpg';

        // normalizăm calea imaginii pentru <img> din listă
        $normalized = (
            strpos($prima_imagine, 'http') === 0 ||
            strpos($prima_imagine, '/') === 0 ||
            strpos($prima_imagine, 'uploads/') === 0
        ) ? $prima_imagine : ('uploads/' . $prima_imagine);

        // JSON pentru data-anunt (folosit de popup)
        $dataAnunt = json_encode([
                "titlu" => $row['titlu'],
                "pret" => $row['pret'],
                "moneda" => $row['moneda'],
                "descriere" => $row['descriere'],
                "imagini" => $imagini
            ], JSON_UNESCAPED_UNICODE);
?>
    <!-- ✅ Card identic cu cel din index.php -->
    <div class="card" data-anunt='<?= htmlspecialchars($dataAnunt, ENT_QUOTES, 'UTF-8'); ?>'>
        <div class="img-container">
            <img src="<?= htmlspecialchars($normalized) ?>" alt="<?= htmlspecialchars($row['titlu']); ?>">
        </div>
        <div class="info-container">
            <h3><?= htmlspecialchars($row['titlu']) ?></h3>
            <div class="pret"><?= htmlspecialchars(number_format((float)$row['pret'], 2, '.', ' ')) ?> <?= htmlspecialchars($row['moneda'] ?? '') ?></div>
            <div class="descriere"><?= htmlspecialchars(mb_substr((string)$row['descriere'], 0, 80)) ?>...</div>
        </div>
    </div>
<?php
    endwhile;
else:
?>
<div class="no-results">
  <p>Nu există anunțuri disponibile pentru filtrul curent.</p>
  <button id="nr-reset" class="btn btn-ghost">Resetează filtrele</button>
</div>
<?php
endif;
