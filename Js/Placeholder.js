document.addEventListener('DOMContentLoaded', function () {

    // Winkelwagen knop feedback
    document.querySelectorAll('.btn-cart').forEach(function (btn) {
        if (btn.disabled) return;
        btn.addEventListener('click', function () {
            const original = btn.textContent;
            btn.textContent = '✓ Toegevoegd';
            btn.style.background = '#27AE60';
            btn.style.color = '#fff';
            setTimeout(function () {
                btn.textContent = original;
                btn.style.background = '';
                btn.style.color = '';
            }, 1800);
        });
    });

    // Zoekformulier: lege zoekopdracht blokkeren
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            const input = searchForm.querySelector('input[name="search"]');
            if (input && input.value.trim() === '') {
                e.preventDefault();
                window.location.href = '../Php/webshop.php';
            }
        });
    }

    // Smooth scroll naar #products
    document.querySelectorAll('a[href="#products"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            const target = document.getElementById('products');
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Alerts automatisch sluiten na 5 seconden
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity .5s';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 500);
        }, 5000);
    });

});