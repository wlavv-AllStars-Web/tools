@if($currentVisibility !== null)
    <div class="store-visibility-tools">
        <div class="visibility-control" data-store="{{ $store }}">
            @foreach($visibilities as $visibility)
                <button
                    type="button"
                    class="visibility-option @if($currentVisibility === $visibility) is-selected @endif"
                    data-visibility="{{ $visibility }}"
                    data-url="{{ route('sales.tools.product_visibility.update', ['productId' => $product->id_product, 'store' => strtolower($store)]) }}"
                    aria-pressed="{{ $currentVisibility === $visibility ? 'true' : 'false' }}"
                    title="Set {{ $store }} visibility to {{ $visibility }}"
                >
                    {{ $visibility }}
                </button>
            @endforeach
        </div>
        <a
            class="frontoffice-link"
            data-store="{{ $store }}"
            href="{{ rtrim((string) config('allstars.stores.' . $store . '.base_url'), '/') }}/index.php?id_product={{ $product->id_product }}&controller=product"
            target="_blank"
            rel="noopener noreferrer"
            title="Open product in {{ $store }} frontoffice"
        >
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            Frontoffice
        </a>
    </div>
@else
    <span class="visibility-unavailable">Not assigned to {{ $store }}</span>
@endif
