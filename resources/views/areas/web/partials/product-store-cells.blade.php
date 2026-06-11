@foreach($columns as $column)
    @if(!$store)
        <td>-</td>
    @elseif($column === 'pack')
        <td>
            <span class="store-compare-badge {{ $store['is_pack'] ? 'store-compare-yes' : 'store-compare-no' }}">
                {{ $store['is_pack'] ? 'yes' : 'no' }}
            </span>
        </td>
    @elseif($column === 'purchase_price')
        <td>{{ number_format($store['purchase_price'] ?? 0, 2, ',', '.') }}</td>
    @elseif($column === 'supplier_purchase')
        <td>{{ $supplierPurchase !== null ? number_format($supplierPurchase, 2, ',', '.') : '-' }}</td>
    @elseif($column === 'supplier_discount')
        <td>{{ $supplierDiscount !== null ? number_format($supplierDiscount, 2, ',', '.') . '%' : '-' }}</td>
    @elseif($column === 'wm_deprecated')
        <td>
            <span class="store-compare-badge {{ $store['wm_deprecated'] ? 'store-compare-warning' : 'store-compare-no' }}">
                {{ $store['wm_deprecated'] ? 'yes' : 'no' }}
            </span>
        </td>
    @elseif($column === 'discount')
        @php
            $discountClass = $store['discount_type'] === 'individual'
                ? 'alert-danger store-compare-discount-individual'
                : ($store['discount_type'] === 'global' ? 'alert-info store-compare-discount-global' : '');
        @endphp
        <td class="{{ $discountClass }}">{{ $store['discount'] !== '' ? $store['discount'] : '-' }}</td>
    @elseif($column === 'is_active')
        <td>
            <span class="store-compare-badge {{ $store['is_active'] ? 'store-compare-yes' : 'store-compare-no' }}">
                {{ $store['is_active'] ? 'yes' : 'no' }}
            </span>
        </td>
    @elseif($column === 'exclusive')
        <td>
            <span class="store-compare-badge {{ $exclusive ? 'store-compare-warning' : 'store-compare-neutral' }}">
                {{ $exclusive ? 'yes' : 'no' }}
            </span>
        </td>
    @elseif($column === 'visibility')
        <td>{{ $store['visibility'] !== '' ? $store['visibility'] : '-' }}</td>
    @elseif($column === 'category')
        <td>{{ $store['category'] !== '' ? $store['category'] : '-' }}</td>
    @endif
@endforeach
