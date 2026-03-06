<?php
include_once 'admin/db.php';
include_once 'includes/categorii.php';

$grupa_selectata = $_GET['grupa'] ?? '';
$categorie_selectata = $_GET['categorie'] ?? '';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroTeam Expert - Utilaje Second Hand</title>
    <link rel="stylesheet" href="style.css">
    <!-- ✅ Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css"/>
</head>
<body>

<header>
        <img src="images/LOGO_ATE_optimized.webp" alt="AgroTeam Expert">
    </div>
    <div class="descriere">Utilaje Second Hand</div></div>
                <div class="buton-actiuni">
        <a href="tel:+40755105766">Suna</a>
        <a href="https://wa.me/40755105766" target="_blank" rel="noopener">WhatsApp</a>
    </div>
    <a href="admin/login.php" class="admin-link">Admin</a>
<style>
  /* Aceasta este cea mai rapidă metodă */
  h1, h2, .Utilaje Second Hand { 
    font-family: sans-serif; /* Folosește fontul standard al sistemului, curat și modern */
    font-weight: bold;
    letter-spacing: -0.5px; /* Îl face să arate mai "pro", mai compact */
  }
</style>
</header>

<!-- =================== FILTRU PRINCIPAL =================== -->
<div class="filter-bar">
    <button id="reset-filtru" class="btn-filtru reset">Toate</button>
    <?php foreach ($grupe_utilaje as $grupa => $categorii): ?>
        <button class="btn-filtru"
                data-grupa="<?php echo htmlspecialchars($grupa); ?>"
                data-subcategorii='<?php echo json_encode($grupe_utilaje_struct[$grupa] ?? []); ?>'>
            <?php echo ucwords(str_replace('_',' ', $grupa)); ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- Submeniu sub categorii (chips) -->
<div id="subcat-menu" class="subcat-menu hidden"></div>

<!-- Container pentru subcategorii (vechi) – fallback -->
<div id="subcategorii-container"></div>

<!-- Loader -->
<div id="loader" class="hidden">Se încarcă...</div>

<!-- =================== LISTA ANUNTURI =================== -->
<div class="ads-row">
  <aside class="ad-rail ad-rail-left" aria-hidden="true"></aside>
  <div class="ads-content">
<div id="lista-anunturi" class="lista-anunturi">
    <?php
    $conditii = [];
    if ($grupa_selectata && isset($grupe_utilaje_sluguri[$grupa_selectata])) {
        $slugs = $grupe_utilaje_sluguri[$grupa_selectata];
        $labels = [];
        foreach ($slugs as $slug) {
            if (isset($categorii_utilaje[$slug])) {
                $labels[] = $categorii_utilaje[$slug];
            }
        }
        $valori = array_unique(array_merge($slugs, $labels));
        $escaped = array_map([$conn, 'real_escape_string'], $valori);
        if (!empty($escaped)) {
            $conditii[] = "categorie IN ('" . implode("','", $escaped) . "')";
        }
    }
    if ($categorie_selectata) {
        $valori = [];
        if (isset($categorii_utilaje[$categorie_selectata])) {
            $slug = $categorie_selectata;
            $label = $categorii_utilaje[$slug];
            $valori = [$slug, $label];
        } else {
            $valori = [$categorie_selectata];
        }
        $valori = array_unique($valori);
        $escaped = array_map([$conn, 'real_escape_string'], $valori);
        if (!empty($escaped)) {
            $conditii[] = "categorie IN ('" . implode("','", $escaped) . "')";
        }
    }
    $sql = "SELECT * FROM anunturi WHERE activ='da'";
    if (!empty($conditii)) {
        $sql .= " AND " . implode(" AND ", $conditii);
    }
    $sql .= " ORDER BY data DESC";

    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0):
        while ($row = $result->fetch_assoc()):
            $imagini = array_values(array_filter(explode(';', $row['imagini'])));
            $prima_imagine = $imagini[0] ?? '';
            $dataAnunt = json_encode([
                "titlu" => $row['titlu'],
                "pret" => $row['pret'],
                "moneda" => $row['moneda'],
                "descriere" => $row['descriere'],
                "imagini" => $imagini
            ], JSON_UNESCAPED_UNICODE);
    ?>
        <!-- ✅ Card cu atribut data-anunt -->
        <div class="card" data-anunt='<?= htmlspecialchars($dataAnunt, ENT_QUOTES, 'UTF-8'); ?>'>
            <div class="img-container">
                <?php if ($prima_imagine): ?>
                    <img src="uploads/<?= htmlspecialchars($prima_imagine) ?>" alt="<?= htmlspecialchars($row['titlu']); ?>">
                <?php else: ?>
                    <img src="images/no-image.jpg" alt="fără imagine">
                <?php endif; ?>
            </div>
            <div class="info-container">
                <h3><?= htmlspecialchars($row['titlu']) ?></h3>
                <?php $pret_fmt = number_format($row['pret'], 0, '.', ' '); $txt_tva = ''; ?><div class="pret"><?= $pret_fmt ?> <?= htmlspecialchars($row['moneda'] ?? '') ?></div>
                <div class="descriere"><?= htmlspecialchars(mb_substr($row['descriere'], 0, 80)) ?>...</div>
            </div>
        </div>
    <?php endwhile; else: ?>
        <p>Nu există anunțuri disponibile.</p>
    <?php endif; ?>
</div>
  </div>
  <aside class="ad-rail ad-rail-right" aria-hidden="true"></aside>
</div>

<!-- =================== SCRIPT AJAX FILTRARE =================== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const listaAnunturi  = document.getElementById('lista-anunturi');
  const loader         = document.getElementById('loader');
  const resetBtn       = document.getElementById('reset-filtru');
  const subcatMenu     = document.getElementById('subcat-menu');
  const butoaneGrupe   = document.querySelectorAll('.btn-filtru[data-grupa]');

  function showLoader(){ loader && loader.classList.remove('hidden'); }
  function hideLoader(){ loader && loader.classList.add('hidden'); }

  function debounce(fn, delay=300){ let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), delay); }; }
  async function fetchHTML(url){
    try{
      const ctrl = new AbortController();
      const id = setTimeout(()=>ctrl.abort(), 8000);
      const res = await fetch(url, {signal: ctrl.signal, headers: {'X-Requested-With':'fetch'}});
      clearTimeout(id);
      if(!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.text();
    }catch(err){
      console.error('Eroare la filtrare:', err);
      return `<div class="no-results">Nu am putut încărca rezultatele.
        <button id="nr-reset" class="btn btn-ghost">Resetează filtrele</button></div>`;
    }
  }
  const debouncedLoad = debounce(async (url) => {
    showLoader();
    const html = await fetchHTML(url);
    if (listaAnunturi) listaAnunturi.innerHTML = html;
    hideLoader();
  }, 300);

  // Submeniu chips pentru subcategorii
  function renderSubmenu(subs = []){
    if (!subcatMenu) return;
    if (!subs.length){ subcatMenu.classList.add('hidden'); subcatMenu.innerHTML = ''; return; }
    const items = subs.map(s => {
      const slug = (typeof s === 'string') ? s : (s.slug || s.id || '');
      const label = (typeof s === 'string')
        ? String(s).replace(/_/g,' ')
        : (s.nume || s.label || s.name || slug);
      if (!slug) return '';
      return `<button class="chip" data-tip="${slug}">${label}</button>`;
    }).join('');
    subcatMenu.innerHTML = `<button class="chip all" data-tip="__ALL__">Toate categoriile</button>${items}`;
    subcatMenu.classList.remove('hidden');
  }

  // === Chip-uri subcategorii (activ vizual + filtrare) ===
  document.addEventListener('click', (e) => {
    const chip = e.target.closest('#subcat-menu .chip');
    if (!chip) return;

    const menu = document.getElementById('subcat-menu');
    if (menu) menu.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');

    const tip = chip.dataset.tip;

    if (tip === '__ALL__') {
      const activeGroup = document.querySelector('.btn-filtru.activ[data-grupa]');
      if (activeGroup) {
        debouncedLoad(`filter.php?grupa=${encodeURIComponent(activeGroup.dataset.grupa)}`);
      } else {
        debouncedLoad('filter.php');
      }
      return;
    }

    debouncedLoad(`filter.php?tip=${encodeURIComponent(tip)}`);
  });

  // Click pe grupe
  butoaneGrupe.forEach(buton => {
    const grupa = buton.dataset.grupa;
    const subcategorii = (()=>{ try { return JSON.parse(buton.dataset.subcategorii || '[]'); } catch(_){ return []; } })();
    buton.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelectorAll('.btn-filtru').forEach(b => b.classList.remove('activ'));
      buton.classList.add('activ');
      renderSubmenu(subcategorii);
      debouncedLoad(`filter.php?grupa=${encodeURIComponent(grupa)}`);
    });
  });

  // Reset / Toate
  if (resetBtn){
    resetBtn.addEventListener('click', (e) => {
      e.preventDefault();
      document.querySelectorAll('.btn-filtru').forEach(b => b.classList.remove('activ'));
      resetBtn.classList.add('activ');
      renderSubmenu([]);
      debouncedLoad('filter.php');
    });
  }

  // „Resetează filtrele” din mesajul fără rezultate
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#nr-reset')) return;
    e.preventDefault();
    resetBtn?.click();
  });
});
</script>

<!-- ✅ Popup container -->
<div id="popup-anunt" class="popup-overlay hidden">
  <div class="popup-content">
    <span class="popup-close">&times;</span>
    <div class="popup-body">
      <!-- Galeria media -->
      <div class="popup-gallery swiper">
        <div class="swiper-wrapper" id="swiper-images"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
      </div>

      <div class="popup-details">
        <h2 id="popup-titlu"></h2>
        <p class="popup-price" id="popup-pret"></p>
        <p class="popup-desc" id="popup-descriere"></p>
      </div>
    </div>
  </div>
</div>

<!-- ✅ FULLSCREEN GALLERY CONTAINER -->
<div id="fullscreen-gallery" class="hidden"></div>

<!-- ✅ Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>

<!<!-- ✅ Script stabil pentru popup + fullscreen (Swiper) -->
<script>
let popupSwiper = null;
let fullscreenSwiper = null;

document.addEventListener('click', function (e) {
  const popup      = document.getElementById('popup-anunt');
  const fullscreen = document.getElementById('fullscreen-gallery');

  const titluEl      = document.getElementById('popup-titlu');
  const pretEl       = document.getElementById('popup-pret');
  const descriereEl  = document.getElementById('popup-descriere');
  const swiperWrapper= document.getElementById('swiper-images');

  /* ===== 1. DESCHIDERE POPUP LA CLICK PE CARD ===== */
  const card = e.target.closest('.card');
  if (card && card.dataset.anunt) {
    e.preventDefault();

    let anunt = {};
    try {
      anunt = JSON.parse(card.dataset.anunt || '{}');
    } catch (err) {
      console.error('JSON anunt invalid:', err);
    }

    titluEl.textContent     = anunt.titlu || '';
    pretEl.textContent      = (anunt.pret ? anunt.pret + ' ' : '') + (anunt.moneda || '');
    descriereEl.textContent = anunt.descriere || '';

    // Curățăm slide-urile vechi
    swiperWrapper.innerHTML = '';

    (anunt.imagini || []).forEach((name) => {
      if (!name) return;
      const imgName = String(name).trim();
      const src = (
        imgName.startsWith('http') ||
        imgName.startsWith('/') ||
        imgName.startsWith('uploads/')
      )
        ? imgName
        : 'uploads/' + imgName;

      const slide = document.createElement('div');
      slide.className = 'swiper-slide';
      slide.innerHTML = `
        <div class="swiper-zoom-container">
          <img src="${src}" class="thumb-img" alt="">
        </div>`;
      swiperWrapper.appendChild(slide);
    });

    // Arătăm popup-ul
    popup.classList.remove('hidden');

    // Distrugem vechiul Swiper (dacă există)
    if (popupSwiper) {
      popupSwiper.destroy(true, true);
      popupSwiper = null;
    }

    // Inițializăm Swiper DOAR pe galeria din popup
    popupSwiper = new Swiper('#popup-anunt .popup-gallery', {
      slidesPerView: 1,
      spaceBetween: 10,
      centeredSlides: true,
      navigation: {
        nextEl: '#popup-anunt .swiper-button-next',
        prevEl: '#popup-anunt .swiper-button-prev',
      },
      zoom: { maxRatio: 3 },
      observer: true,
      observeParents: true,
    });

    return;
  }

  /* ===== 2. ÎNCHIDERE POPUP (X sau click pe fundal) ===== */
  if (e.target.closest('.popup-close')) {
    popup.classList.add('hidden');
    return;
  }
  if (e.target === popup) {
    popup.classList.add('hidden');
    return;
  }

  /* ===== 3. DESCHIDERE FULLSCREEN LA CLICK PE POZA DIN POPUP ===== */
  if (e.target.classList.contains('thumb-img') &&
      e.target.closest('#popup-anunt')) {

    const popupImages = Array.from(
      document.querySelectorAll('#popup-anunt .thumb-img')
    );
    if (!popupImages.length) return;

    const slidesHtml = popupImages.map(img => `
      <div class="swiper-slide">
        <div class="swiper-zoom-container">
          <img src="${img.src}" alt="">
        </div>
      </div>
    `).join('');

    fullscreen.innerHTML = `
      <div class="swiper fullscreen-swiper" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
        <div class="swiper-wrapper">
          ${slidesHtml}
        </div>
        <div class="swiper-button-next" style="color:#fff;"></div>
        <div class="swiper-button-prev" style="color:#fff;"></div>
        <div class="close-btn" style="position:absolute;top:10px;right:20px;font-size:30px;color:#fff;cursor:pointer;">&times;</div>
      </div>
    `;
    fullscreen.classList.remove('hidden');

    // Distrugem vechiul Swiper fullscreen (dacă există)
    if (fullscreenSwiper) {
      fullscreenSwiper.destroy(true, true);
      fullscreenSwiper = null;
    }

    fullscreenSwiper = new Swiper('.fullscreen-swiper', {
      loop: true,
      zoom: { maxRatio: 3 },
      navigation: {
        nextEl: '.fullscreen-swiper .swiper-button-next',
        prevEl: '.fullscreen-swiper .swiper-button-prev',
      },
    });

    return;
  }

    /* ===== 4. ÎNCHIDERE FULLSCREEN (X sau click oriunde în afara imaginii) ===== */
  if (!fullscreen.classList.contains('hidden')) {
    const onImage    = e.target.closest('.swiper-zoom-container img');
    const onControls = e.target.closest('.swiper-button-next, .swiper-button-prev');
    const onClose    = e.target.closest('.close-btn');
    const inOverlay  = e.target.closest('#fullscreen-gallery');

    // dacă ai apăsat pe X sau pe fundal / margini (nu pe imagine, nu pe săgeți)
    if (onClose || (inOverlay && !onImage && !onControls)) {
      fullscreen.classList.add('hidden');
      fullscreen.innerHTML = '';
      if (fullscreenSwiper) {
        fullscreenSwiper.destroy(true, true);
        fullscreenSwiper = null;
      }
    }
  }

});
</script>


<style>
/* ✅ Stil pentru fullscreen + zoom */
#fullscreen-gallery {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.95);
  display: flex; align-items: center; justify-content: center;
  z-index: 9999;
}
#fullscreen-gallery img { max-width: 95%; max-height: 90%; }
.hidden { display: none; }

/* Zoom & layout in popup + fullscreen */
.popup-gallery .swiper-zoom-container img,
.fullscreen-swiper .swiper-zoom-container img{
  max-width: 100%;
  max-height: 80vh;
  object-fit: contain;
  -webkit-user-drag: none;
  user-select: none;
}
.popup-gallery .swiper-wrapper,
.fullscreen-swiper .swiper-wrapper { align-items: center; }
</style>

</body>
</html>
