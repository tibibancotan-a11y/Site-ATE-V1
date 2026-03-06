document.addEventListener('DOMContentLoaded', function () {
    const listaAnunturi = document.getElementById('lista-anunturi');
    const loader = document.getElementById('loader');
    const resetBtn = document.getElementById('reset-filtru');

    function showLoader() { loader.classList.remove('hidden'); }
    function hideLoader() { loader.classList.add('hidden'); }

    // Filtrare pe grupe + expand subcategorii
    const butoaneGrupe = document.querySelectorAll('.buton-grupa');
    const dropdownuri = {};

    butoaneGrupe.forEach(buton => {
        const span = buton.querySelector('.expand');
        span.addEventListener('click', function (e) {
            e.stopPropagation();
            const grupa = buton.dataset.grupa;
            const subcategorii = JSON.parse(buton.dataset.subcategorii);

            // elimină dropdownul dacă există
            if (dropdownuri[grupa]) {
                dropdownuri[grupa].remove();
                delete dropdownuri[grupa];
                return;
            }

            const container = document.createElement('div');
            container.classList.add('dropdown-subcategorii');

            subcategorii.forEach(cat => {
                const btn = document.createElement('button');
                btn.textContent = cat;
                btn.className = 'subcategorie';
                btn.addEventListener('click', () => {
                    showLoader();
                    fetch(`filter.php?grupa=${encodeURIComponent(grupa)}&tip=${encodeURIComponent(cat)}`)
                        .then(response => response.text())
                        .then(data => {
                            listaAnunturi.innerHTML = data;
                            hideLoader();
                        })
                        .catch(err => console.error('Eroare AJAX:', err));
                });
                container.appendChild(btn);
            });

            buton.insertAdjacentElement('afterend', container);
            dropdownuri[grupa] = container;
        });

        // Filtrare pe grupă (click pe butonul principal)
        buton.addEventListener('click', function () {
            const grupa = buton.dataset.grupa;
            showLoader();
            fetch(`filter.php?grupa=${encodeURIComponent(grupa)}`)
                .then(response => response.text())
                .then(data => {
                    listaAnunturi.innerHTML = data;
                    hideLoader();
                });
        });
    });

    // Reset filtrare
    resetBtn.addEventListener('click', function () {
        showLoader();
        fetch("filter.php")
            .then(response => response.text())
            .then(data => {
                listaAnunturi.innerHTML = data;
                hideLoader();
            });
    });
});
