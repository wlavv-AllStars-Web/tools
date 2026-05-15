<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-add-product]').forEach(function (button) {
        button.addEventListener('click', function () {
            const category = button.getAttribute('data-add-product');
            const container = document.getElementById('products-' + category);
            const template = document.getElementById('product-row-template').innerHTML;
            const index = container.querySelectorAll('.product-row').length;

            const html = template
                .replaceAll('__CATEGORY__', category)
                .replaceAll('__INDEX__', index);

            container.insertAdjacentHTML('beforeend', html);
            renumberPositions(container);
            updateCategoryCount(category);
        });
    });

    document.addEventListener('click', function (event) {
        if (event.target.matches('[data-remove-product-row]')) {
            const row = event.target.closest('.product-row');
            const container = row.closest('.product-rows');
            const category = container.id.replace('products-', '');

            row.remove();
            renumberPositions(container);
            updateCategoryCount(category);
        }

        if (event.target.matches('[data-remove-gallery-item]')) {
            event.target.closest('.asg-gallery-item').remove();
        }

        if (event.target.matches('[data-move-product-up]')) {
            const row = event.target.closest('.product-row');
            const previous = row.previousElementSibling;
            const container = row.closest('.product-rows');

            if (previous) {
                container.insertBefore(row, previous);
                renumberPositions(container);
            }
        }

        if (event.target.matches('[data-move-product-down]')) {
            const row = event.target.closest('.product-row');
            const next = row.nextElementSibling;
            const container = row.closest('.product-rows');

            if (next) {
                container.insertBefore(next, row);
                renumberPositions(container);
            }
        }
    });

    document.querySelectorAll('.product-rows').forEach(function (container) {
        renumberPositions(container);
        updateCategoryCount(container.id.replace('products-', ''));
    });

    function renumberPositions(container) {
        container.querySelectorAll('.product-row').forEach(function (row, index) {
            const positionInput = row.querySelector('[data-product-position]');

            if (positionInput) {
                positionInput.value = index + 1;
            }

            row.querySelectorAll('input').forEach(function (input) {
                input.name = input.name.replace(/\]\[\d+\]\[/, '][' + index + '][');
            });
        });
    }

    function updateCategoryCount(category) {
        const container = document.getElementById('products-' + category);
        const badge = document.querySelector('[data-category-count="' + category + '"]');

        if (!container || !badge) {
            return;
        }

        let count = 0;

        container.querySelectorAll('.product-row').forEach(function (row) {
            const idInput = row.querySelector('input[name*="[id_product]"]');
            const nameInput = row.querySelector('input[name*="[name]"]');

            if ((idInput && idInput.value.trim() !== '') || (nameInput && nameInput.value.trim() !== '')) {
                count++;
            }
        });

        badge.textContent = count;
    }
});
</script>
