<script>
document.addEventListener('DOMContentLoaded', function () {
    const galleryType = document.querySelector('[name="gallery_type"]');
    const flickrBox = document.querySelector('[data-flickr-box]');
    const internalBox = document.querySelector('[data-internal-gallery-box]');

    function syncGalleryType() {
        if (!galleryType) {
            return;
        }

        const isFlickr = galleryType.value === 'flickr';

        if (flickrBox) {
            flickrBox.style.display = isFlickr ? '' : 'none';
        }

        if (internalBox) {
            internalBox.style.display = isFlickr ? 'none' : '';
        }
    }

    if (galleryType) {
        galleryType.addEventListener('change', syncGalleryType);
        syncGalleryType();
    }

    document.addEventListener('click', function (event) {
        if (event.target.matches('[data-remove-gallery-item]')) {
            event.target.closest('.asg-gallery-item').remove();
            updateGalleryOrder();
        }
    });

    const gallery = document.querySelector('[data-sortable-gallery]');
    let draggedItem = null;

    if (gallery) {
        gallery.addEventListener('dragstart', function (event) {
            const item = event.target.closest('.asg-gallery-item');

            if (!item) {
                return;
            }

            draggedItem = item;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', item.querySelector('input[name="existing_images[]"]').value);

            setTimeout(function () {
                item.classList.add('is-dragging');
            }, 0);
        });

        gallery.addEventListener('dragover', function (event) {
            event.preventDefault();

            const target = event.target.closest('.asg-gallery-item');

            if (!draggedItem || !target || target === draggedItem) {
                return;
            }

            const items = Array.from(gallery.querySelectorAll('.asg-gallery-item'));
            const draggedIndex = items.indexOf(draggedItem);
            const targetIndex = items.indexOf(target);

            target.classList.add('is-drag-over');

            if (draggedIndex < targetIndex) {
                target.after(draggedItem);
            } else {
                target.before(draggedItem);
            }
        });

        gallery.addEventListener('dragleave', function (event) {
            const item = event.target.closest('.asg-gallery-item');

            if (item) {
                item.classList.remove('is-drag-over');
            }
        });

        gallery.addEventListener('drop', function (event) {
            event.preventDefault();
            clearDragState();
            updateGalleryOrder();
        });

        gallery.addEventListener('dragend', function () {
            clearDragState();
            updateGalleryOrder();
        });

        updateGalleryOrder();
    }

    function clearDragState() {
        document.querySelectorAll('.asg-gallery-item').forEach(function (item) {
            item.classList.remove('is-dragging', 'is-drag-over');
        });

        draggedItem = null;
    }

    function updateGalleryOrder() {
        document.querySelectorAll('[data-sortable-gallery] .asg-gallery-item').forEach(function (item, index) {
            const order = item.querySelector('[data-gallery-order]');

            if (order) {
                order.textContent = index + 1;
            }
        });
    }
});
</script>
