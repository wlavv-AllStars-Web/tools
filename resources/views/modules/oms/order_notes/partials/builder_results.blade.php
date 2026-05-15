@php
    $products = $products ?? collect();
    $search = trim((string) ($search ?? ''));
@endphp

<style>
.oms-builder-table{table-layout:fixed;width:100%;}
.oms-builder-table thead th{font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap;background:#f8fafc;}
.oms-builder-table tbody td{vertical-align:middle;white-space:nowrap;}
.oms-builder-table .sku-cell{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;}
.oms-builder-table .sku-code{display:block;width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;padding:.18rem .42rem;border-radius:5px;background:#f8fafc;border:1px solid rgba(20,33,61,.08);}
.oms-builder-table .qty-input{max-width:76px;margin:0 auto;text-align:center;}
.oms-builder-table .new-flag{display:inline-flex;width:20px;height:20px;border-radius:5px;font-size:.72rem;align-items:center;justify-content:center;background:#dcfce7;color:#15803d;border:1px solid #86efac;}
.oms-builder-table .action-btn{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:5px;border:1px solid rgba(37,99,235,.18);background:#eff6ff;color:#1d4ed8;}
.oms-builder-table .action-btn[disabled]{cursor:not-allowed;pointer-events:all;opacity:.55;background:#f8fafc;color:#94a3b8;border-color:rgba(148,163,184,.16);}
.oms-builder-table .name-code{display:block;width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.oms-builder-table .metric-cell{display:inline-flex;align-items:center;justify-content:center;min-width:48px;}
</style>

<div class="table-responsive">
    <table class="table table-hover align-middle mb-0 oms-builder-table" id="omsSupplierProductsTable">
        <colgroup>
            <col style="width:10%">
            <col style="width:20%">
            <col style="width:35%">
            <col style="width:10%">
            <col style="width:15%">
            <col style="width:10%">
        </colgroup>
        <thead>
            <tr>
                <th class="text-center"></th>
                <th>SKU</th>
                <th>Name</th>
                <th class="text-center">Stock</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Add</th>
            </tr>
        </thead>
        <tbody>
            @if($search === '')
                <tr>
                    <td colspan="6" class="text-muted text-center py-4">Type at least 2 characters to search the supplier catalogue.</td>
                </tr>
            @elseif($products->isEmpty())
                <tr>
                    <td colspan="6" class="text-muted text-center py-4">No supplier products found for the current filters.</td>
                </tr>
            @else
                @foreach($products as $product)
                    @php
                        $sku = trim((string) ($product->sku ?? '—'));
                        $name = trim((string) ($product->display_name ?? $sku));
                        $alreadyAdded = (int) ($product->already_added ?? 0) === 1;
                        $isNew = (int) ($product->is_new_candidate ?? 0) === 1;
                        $qtyDefault = max(1, (int) ($product->to_buy ?? 0));
                        $productAttributeId = (int) ($product->product_attribute_id ?? 0);
                        $formId = 'oms-add-product-form-' . (int) $product->product_id . '-' . $productAttributeId;
                    @endphp
                    <tr>
                        <td class="text-center">
                            @if($isNew && !$alreadyAdded)
                                <span class="new-flag"><i class="fa-solid fa-check"></i></span>
                            @endif
                        </td>
                        <td class="sku-cell" title="{{ $name }}">
                            <span class="sku-code">{{ $sku !== '' ? $sku : '—' }}</span>
                        </td>
                        <td title="{{ $name }}">
                            <span class="name-code">{{ $name }}</span>
                        </td>
                        <td class="text-center"><span class="metric-cell">{{ (int) ($product->stock_qty ?? 0) }}</span></td>
                        <td class="text-center">
                            @if(!$alreadyAdded)
                                <input form="{{ $formId }}" class="form-control form-control-sm qty-input" type="number" min="1" name="qty_ordered" value="{{ $qtyDefault }}">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <form id="{{ $formId }}" method="post" action="{{ route('erp.oms.order_notes.lines.store', $orderNote) }}" class="js-oms-add-line-form d-inline-block">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ (int) $product->product_id }}">
                                <input type="hidden" name="product_attribute_id" value="{{ $productAttributeId }}">
                                <button class="action-btn" type="submit" title="{{ $alreadyAdded ? 'Product already added to order note' : 'Add to order note' }}" aria-label="Add to order note" {{ $alreadyAdded ? 'disabled' : '' }}>
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
