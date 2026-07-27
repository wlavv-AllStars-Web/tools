@if($currentVisibility !== null)
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
@else
    <span class="visibility-unavailable">Not assigned to {{ $store }}</span>
@endif
