@extends('layouts.app')

@section('content')
<style>
    .studio-asd-photos { margin: 15px 0; }
    .studio-asd-photos-card { position: sticky; top: 20px; width: 400px; background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 22px; }
    .studio-asd-photos-brand { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid #eee; }
    .studio-asd-layout { display: grid; grid-template-columns: minmax(0, 1fr) 400px; gap: 22px; align-items: start; }
    .studio-asd-gallery-title { margin: 0 0 12px; font-size: 1.05rem; font-weight: 700; }
    .studio-asd-gallery-toolbar { display: flex; gap: 8px; margin: 0 0 12px; }
    .studio-asd-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .studio-asd-image-tile { min-width: 0; overflow: hidden; border: 1px solid #ddd; border-radius: 6px; background: #fff; }
    .studio-asd-image { position: relative; display: grid; aspect-ratio: 1; place-items: center; overflow: hidden; background: #fff; }
    .studio-asd-image img { width: 100%; height: 100%; object-fit: contain; }
    .studio-asd-image-cover { position: absolute; top: 5px; left: 5px; padding: 2px 5px; border-radius: 3px; background: #0d6efd; color: #fff; font-size: .65rem; font-weight: 700; text-transform: uppercase; }
    .studio-asd-image-reference { padding: 8px 9px; border-top: 1px solid #eee; font-size: .8rem; font-weight: 700; overflow-wrap: anywhere; }
    .studio-asd-no-images { display: grid; place-items: center; aspect-ratio: 1; padding: 12px; color: #777; text-align: center; background: #f8f9fa; }
    .studio-asd-loader { min-height: 48px; display: grid; place-items: center; color: #777; }
    .studio-asd-sentinel { height: 2px; }
    @media (max-width: 960px) { .studio-asd-layout { grid-template-columns: 1fr; } .studio-asd-photos-card { position: static; width: 100%; } }
</style>

<div class="studio-asd-photos">
    <div class="studio-asd-layout">
        <section>
            <h5 class="studio-asd-gallery-title">ASD product photos</h5>
            <div class="studio-asd-gallery-toolbar" role="group" aria-label="Product image filter">
                <button type="button" class="btn btn-sm btn-outline-primary" data-asd-image-filter="all">All images</button>
                <button type="button" class="btn btn-sm btn-primary" data-asd-image-filter="missing">Missing images</button>
            </div>
            <div id="studioAsdProductGallery" class="studio-asd-gallery" data-products-url="{{ route('web.tools.resources.asd.studio_products', $brand->id_manufacturer) }}"></div>
            <div id="studioAsdProductLoader" class="studio-asd-loader" hidden><i class="fa-solid fa-spinner fa-spin me-2"></i> Loading product photos...</div>
            <div id="studioAsdProductSentinel" class="studio-asd-sentinel"></div>
        </section>

        <div class="studio-asd-photos-card">
            <div class="studio-asd-photos-brand">
                <div>
                    <h4 style="margin: 0;">{{ $brand->name }}</h4>
                    <small class="text-muted">ASD - manufacturer #{{ $brand->id_manufacturer }}</small>
                </div>

            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('marketing.asd_missing_photos.images.upload', $brand->id_manufacturer) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="pictures_selected_count" id="pictures_selected_count" value="0">
                <div class="mb-3">
                    <label for="pictures" class="form-label">Product pictures 600x600</label>
                    <input type="file" name="pictures[]" id="pictures" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required data-max-files="{{ (int) ini_get('max_file_uploads') }}">
                    <div class="form-text">Use the product reference as file name. JPG, PNG and WEBP up to 20 MB each.</div>
                    <div class="text-danger small mt-2 d-none" id="pictures_count_error"></div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i> Upload photos</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('pictures');
    const count = document.getElementById('pictures_selected_count');
    const error = document.getElementById('pictures_count_error');
    if (input && count && error) {
        input.addEventListener('change', function () {
            const selected = input.files ? input.files.length : 0;
            const maximum = parseInt(input.dataset.maxFiles, 10) || 20;
            count.value = selected;
            error.classList.toggle('d-none', selected <= maximum);
            error.textContent = selected > maximum ? 'Select at most ' + maximum + ' images at a time.' : '';
        });
    }

    const gallery = document.getElementById('studioAsdProductGallery');
    const loader = document.getElementById('studioAsdProductLoader');
    const sentinel = document.getElementById('studioAsdProductSentinel');
    if (!gallery || !loader || !sentinel) return;

    let page = 0;
    let loading = false;
    let hasMore = true;
    let filter = 'missing';
    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };
    const renderProduct = (product) => {
        if (!product.images.length) {
            const tile = element('article', 'studio-asd-image-tile');
            tile.append(
                element('div', 'studio-asd-no-images', 'Image missing'),
                element('div', 'studio-asd-image-reference', product.reference)
            );
            gallery.append(tile);
            return;
        }
        product.images.forEach((image) => {
            const tile = element('article', 'studio-asd-image-tile');
            const link = element('a', 'studio-asd-image');
            link.href = image.large_url;
            link.target = '_blank';
            link.rel = 'noopener';
            const imageElement = document.createElement('img');
            imageElement.src = image.thumbnail_url;
            imageElement.alt = `${product.reference} - ${product.name}`;
            imageElement.loading = 'lazy';
            imageElement.addEventListener('error', () => {
                imageElement.remove();
                link.append(element('i', 'fa-solid fa-triangle-exclamation text-warning fa-lg'));
            }, { once: true });
            link.append(imageElement);
            if (image.cover) link.append(element('span', 'studio-asd-image-cover', 'Cover'));
            tile.append(link, element('div', 'studio-asd-image-reference', product.reference));
            gallery.append(tile);
        });
    };
    const loadProducts = async () => {
        if (loading || !hasMore) return;
        loading = true;
        loader.hidden = false;
        try {
            const url = new URL(gallery.dataset.productsUrl, window.location.origin);
            url.searchParams.set('page', String(page + 1));
            url.searchParams.set('filter', filter);
            const response = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error(String(response.status));
            const payload = await response.json();
            payload.data.forEach(renderProduct);
            page = payload.meta.page;
            hasMore = payload.meta.has_more;
            if (!hasMore && !gallery.children.length) gallery.append(element('div', 'alert alert-info', 'No product images found for this ASD brand.'));
        } catch (error) {
            gallery.append(element('div', 'alert alert-danger', 'Could not load ASD product photos.'));
            hasMore = false;
        } finally {
            loading = false;
            loader.hidden = true;
        }
    };
    document.querySelectorAll('[data-asd-image-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextFilter = button.dataset.asdImageFilter;
            if (nextFilter === filter) return;
            filter = nextFilter;
            page = 0;
            hasMore = true;
            gallery.replaceChildren();
            document.querySelectorAll('[data-asd-image-filter]').forEach((item) => {
                const active = item.dataset.asdImageFilter === filter;
                item.classList.toggle('btn-primary', active);
                item.classList.toggle('btn-outline-primary', !active);
            });
            loadProducts();
        });
    });
    new IntersectionObserver((entries) => {
        if (entries.some((entry) => entry.isIntersecting)) loadProducts();
    }, { rootMargin: '600px 0px' }).observe(sentinel);
    loadProducts();
});
</script>
@endpush
