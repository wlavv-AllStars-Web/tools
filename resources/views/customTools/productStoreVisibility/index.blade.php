@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel product-visibility-panel">
        <div class="visibility-header">
            <div>
                <h5>Product visibility by store</h5>
                <div class="text-muted">Select a brand and change each product directly in ASM or ASD.</div>
            </div>
            <form method="GET" action="{{ route('sales.tools.product_visibility.index') }}" class="brand-selector">
                <label for="manufacturer_id">Brand</label>
                <select id="manufacturer_id" name="manufacturer_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Select a brand</option>
                    @foreach($manufacturers as $manufacturer)
                        <option value="{{ $manufacturer->id_manufacturer }}" @selected((int) $manufacturerId === (int) $manufacturer->id_manufacturer)>
                            {{ $manufacturer->name }} ({{ $manufacturer->product_count }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        @if($selectedManufacturer && $products)
            <div class="visibility-summary">
                <strong>{{ $selectedManufacturer->name }}</strong>
                <span>{{ number_format($products->total(), 0, ',', ' ') }} products</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered customTable align-middle product-visibility-table">
                    <thead>
                        <tr>
                            <th class="text-center">Cover</th>
                            <th class="text-center">ID</th>
                            <th>Reference</th>
                            <th class="text-center">Images</th>
                            <th class="text-center store-heading asm-heading">ASM visibility</th>
                            <th class="text-center store-heading asd-heading">ASD visibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr
                                data-product-id="{{ $product->id_product }}"
                                @class(['alert alert-danger product-image-alert' => (int) $product->image_count < 5])
                            >
                                <td class="cover-cell">
                                    @if($product->cover_url)
                                        <img src="{{ $product->cover_url }}" alt="{{ $product->reference ?: 'Product ' . $product->id_product }}" loading="lazy"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                    @endif
                                    <span class="cover-placeholder" @if($product->cover_url) style="display:none;" @endif title="No cover image">
                                        <i class="fa-regular fa-image"></i>
                                    </span>
                                </td>
                                <td class="text-center product-id">{{ $product->id_product }}</td>
                                <td class="product-reference">{{ $product->reference ?: '—' }}</td>
                                <td class="text-center image-count">{{ (int) $product->image_count }}</td>
                                <td>
                                    @include('customTools.productStoreVisibility.visibility-control', [
                                        'product' => $product, 'store' => 'ASM', 'currentVisibility' => $product->asm_visibility,
                                    ])
                                </td>
                                <td>
                                    @include('customTools.productStoreVisibility.visibility-control', [
                                        'product' => $product, 'store' => 'ASD', 'currentVisibility' => $product->asd_visibility,
                                    ])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No products found for this brand.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="visibility-pagination">{{ $products->links() }}</div>
        @else
            <div class="empty-brand-state">
                <i class="fa-solid fa-tags"></i>
                <strong>Select the brand you want to work on.</strong>
            </div>
        @endif
    </div>

    <style>
        .product-visibility-panel{display:block;padding:18px;width:100%}
        .visibility-header{align-items:end;display:flex;gap:24px;justify-content:space-between;width:100%}
        .visibility-header h5{font-size:22px;margin:0 0 4px}.brand-selector{min-width:340px}
        .brand-selector label{display:block;font-weight:700;margin-bottom:5px}
        .visibility-summary{align-items:center;border-top:1px solid #ddd;display:flex;gap:10px;margin-top:18px;padding:14px 0 10px}
        .visibility-summary span{background:#e9ecef;border-radius:999px;padding:4px 10px}
        .product-visibility-table{margin-bottom:0;min-width:1050px;width:100%}
        .product-visibility-table th{vertical-align:middle}.cover-cell{text-align:center;width:92px}
        .product-visibility-table tr.product-image-alert > td{background-color:#f8d7da !important;border-color:#f1aeb5}
        .cover-cell img{background:#fff;border:1px solid #ddd;border-radius:5px;height:64px;object-fit:contain;width:64px}
        .cover-placeholder{align-items:center;background:#f2f2f2;border:1px solid #ddd;border-radius:5px;color:#999;height:64px;justify-content:center;width:64px}
        .product-id{font-weight:700;width:90px}.product-reference{font-weight:700;min-width:180px}
        .image-count{font-size:16px;font-weight:700;width:90px}.store-heading{min-width:315px}
        .asm-heading{color:#d32f2f}.asd-heading{color:dodgerblue}
        .visibility-control{display:flex;gap:4px;justify-content:center}
        .visibility-option{background:#fff;border:1px solid #adb5bd;border-radius:4px;color:#495057;cursor:pointer;font-size:12px;font-weight:700;padding:7px 9px;text-transform:uppercase;transition:.15s}
        .visibility-control[data-store="ASM"] .visibility-option:hover{border-color:#d32f2f;color:#d32f2f}
        .visibility-control[data-store="ASM"] .visibility-option.is-selected{background:#d32f2f;border-color:#d32f2f;color:#fff}
        .visibility-control[data-store="ASD"] .visibility-option:hover{border-color:dodgerblue;color:dodgerblue}
        .visibility-control[data-store="ASD"] .visibility-option.is-selected{background:dodgerblue;border-color:dodgerblue;color:#fff}
        .visibility-option:disabled{cursor:wait;opacity:.6}.visibility-unavailable{color:#999;display:block;font-size:12px;text-align:center}
        .visibility-pagination{display:flex;justify-content:center;padding-top:16px}
        .empty-brand-state{align-items:center;color:#6c757d;display:flex;flex-direction:column;font-size:17px;gap:10px;padding:70px 20px;width:100%}
        .empty-brand-state i{font-size:40px}
        @media(max-width:900px){.visibility-header{align-items:stretch;flex-direction:column}.brand-selector{min-width:0;width:100%}}
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.visibility-option').forEach(function (button) {
                button.addEventListener('click', async function () {
                    const control = button.closest('.visibility-control');
                    const previous = control.querySelector('.visibility-option.is-selected');
                    const buttons = Array.from(control.querySelectorAll('.visibility-option'));
                    if (button === previous) return;

                    buttons.forEach(item => item.disabled = true);
                    button.classList.add('is-selected');
                    if (previous) previous.classList.remove('is-selected');

                    try {
                        const response = await fetch(button.dataset.url, {
                            method: 'PATCH',
                            headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':@json(csrf_token())},
                            body: JSON.stringify({visibility: button.dataset.visibility}),
                        });
                        const payload = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(payload.message || 'The visibility could not be updated.');
                        showVisibilityMessage(payload.message || 'Visibility updated.', 'success');
                    } catch (error) {
                        button.classList.remove('is-selected');
                        if (previous) previous.classList.add('is-selected');
                        showVisibilityMessage(error.message, 'error');
                    } finally {
                        buttons.forEach(item => item.disabled = false);
                    }
                });
            });
        });

        function showVisibilityMessage(message, type) {
            if (window.Swal) {
                Swal.fire({icon:type,text:message,timer:type === 'success' ? 1300 : undefined,showConfirmButton:type !== 'success'});
            } else if (type === 'error') {
                window.alert(message);
            }
        }
    </script>
@endsection
