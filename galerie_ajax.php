<?php
include_once 'admin/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo "<p>Eroare: ID invalid</p>";
    exit;
}

$sql = "SELECT * FROM anunturi WHERE id = $id AND activ='da'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<p>Anunțul nu a fost găsit.</p>";
    exit;
}

$row = $result->fetch_assoc();
$media = array_filter(explode(";", $row['imagini']));
?>
<div class="popup-inner">
    <div class="popup-left">
        <?php if (!empty($media)): ?>
        <div class="swiper">
            <div class="swiper-wrapper">
                <?php foreach ($media as $file): ?>
                    <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)): ?>
                        <div class="swiper-slide"><img src="uploads/<?= htmlspecialchars(trim($file)) ?>" alt="Imagine utilaj"></div>
                    <?php elseif (preg_match('/\.(mp4|webm)$/i', $file)): ?>
                        <div class="swiper-slide"><video controls><source src="uploads/<?= htmlspecialchars($file) ?>"></video></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <!-- Navigare Swiper -->
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
        <?php else: ?>
            <p class="no-media">Nu există imagini sau video pentru acest anunț.</p>
        <?php endif; ?>
    </div>

    <div class="popup-right">
        <h2><?= htmlspecialchars($row['titlu']) ?></h2>
        <p class="pret"><?= number_format($row['pret'], 2, '.', ',') ?> <?= htmlspecialchars($row['moneda'] ?? '') ?></p>
        <div class="descriere"><?= nl2br(htmlspecialchars($row['descriere'])) ?></div>
    </div>
</div>
