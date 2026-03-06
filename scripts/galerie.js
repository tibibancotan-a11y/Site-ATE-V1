function deschideGalerie(id) {
    document.body.style.overflow = 'hidden'; // Blochează scroll-ul pe fundal

    const popup = document.getElementById('popup-galerie');
    const content = document.getElementById('popup-content');

    // Afișăm mesaj temporar de încărcare
    content.innerHTML = '<div style="color:#fff; text-align:center; padding:40px;">Se încarcă...</div>';
    popup.classList.remove('hidden');

    fetch('galerie_ajax.php?id=' + id)
        .then(response => {
            if (!response.ok) throw new Error('Eroare la server');
            return response.text();
        })
        .then(html => {
            content.innerHTML = html;

            // Inițializează Swiper după ce conținutul a fost încărcat
            const swiperElement = content.querySelector('.swiper');
            if (swiperElement) {
                new Swiper('.swiper', {
                    navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                    pagination: { el: '.swiper-pagination', clickable: true },
                    loop: true
                });
            }
        })
        .catch(error => {
            console.error('Eroare:', error);
            content.innerHTML = '<p style="color:red; text-align:center;">Eroare la încărcare. Reîncearcă.</p>';
        });
}

function inchideGalerie() {
    const popup = document.getElementById('popup-galerie');
    const content = document.getElementById('popup-content');

    popup.classList.add('hidden');
    content.innerHTML = ''; // Curățăm conținutul
    document.body.style.overflow = 'auto'; // Permitem scroll din nou
}
