document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('bmsSupplierProductSearch');
    const searchBtn = document.getElementById('bmsSupplierProductSearchBtn');
    const target = document.getElementById('bmsAddProductsResults');
    if (!searchInput || !searchBtn || !target) return;

    const loadProducts = function () {
        const q = encodeURIComponent(searchInput.value || '');
        fetch(window.bmsPoConfig.supplierProductsUrl + '?q=' + q, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => r.text())
            .then(html => { target.innerHTML = html; })
            .catch(() => { target.innerHTML = '<div class="alert alert-danger">Erro ao carregar produtos.</div>'; });
    };

    searchBtn.addEventListener('click', loadProducts);
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            loadProducts();
        }
    });
});
