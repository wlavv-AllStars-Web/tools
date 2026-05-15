<div id="housingInfo">
    @if($mode === 'empty')
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fa-solid fa-circle-xmark fa-2x text-danger"></i></div>
                <div class="fw-semibold">No product found</div>
                <div class="text-muted small">No match for <strong>{{ $search }}</strong>.</div>
            </div>
        </div>
    @elseif($mode === 'multiple' || $mode === 'housing_list')
        <div class="card shadow-sm border-0" style="background-color: #FFF;margin-top: 10px;">
            <div class="card-body" style="padding: 0;">
                @if($products->count())
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="text-align: center;background-color: #FFF;">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>EAN13</th>
                                    <th>Location</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $row)
                                    <tr>
                                        <td onclick='pickProduct({{ $row['id_product'] }}, {{ $row['id_product_attribute'] }}, @json($search))'>{{ $row['reference'] ?: '—' }}</td>
                                        <td onclick='pickProduct({{ $row['id_product'] }}, {{ $row['id_product_attribute'] }}, @json($search))'>{{ $row['ean13'] ?: '—' }}</td>
                                        <td onclick='pickProduct({{ $row['id_product'] }}, {{ $row['id_product_attribute'] }}, @json($search))'>{{ $row['operational_location'] ?: '—' }}</td>
                                        <td onclick='pickProduct({{ $row['id_product'] }}, {{ $row['id_product_attribute'] }}, @json($search))'>{{ $row['stock'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-muted small py-4">
                        No products found in this housing.
                    </div>
                @endif
            </div>
        </div>

        @if($mode === 'housing_list' && $canEditLogistics)
            <div id="bulkHousingPanel" data-housing="{{ $housing ?? $search }}" class="card shadow-sm border-0" style="background-color: #FFF;margin-top: 10px;">
                <div class="card-body" style="text-align: center;margin: 0;padding: 1rem 2px;">
                    <input type="hidden" id="bulk_housing_target" value="{{ $housing ?? $search }}">
                    <div class="fw-semibold mb-1">Bulk housing</div>
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label">Scan EAN13 / Reference</label>
                            <input type="text" class="form-control" id="bulk_product_scan" value="" autocomplete="off" style="text-align: center;">
                        </div>
                        <div class="col-12 col-md-4 d-grid">
                            <button type="button" class="btn btn-primary" onclick="bulkAddProductFromScan()">
                                <i class="fa-solid fa-plus"></i> Add product
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0" style="text-align: center;background-color: #FFF;">
                            <thead>
                                <tr>
                                    <th>Scan</th>
                                    <th>Current</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody id="bulkHousingRows">
                                <tr id="bulkHousingEmptyRow">
                                    <td colspan="3" class="text-muted small py-3">No products scanned yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-grid mt-3">
                        <button type="button" class="btn btn-success" onclick="bulkSaveHousing()" id="bulkSaveHousingBtn" disabled>
                            <i class="fa-solid fa-floppy-disk"></i> Save bulk housing
                        </button>
                    </div>
                </div>
            </div>

        @endif
    @elseif($mode === 'single' && $product)
        <input type="hidden" id="selected_id_product" value="{{ $product->id_product }}">
        <input type="hidden" id="selected_id_product_attribute" value="{{ $product->id_product_attribute }}">

        <style>
            .housing-edit-panel {
                border: 1px solid rgba(0, 0, 0, .10);
                border-radius: 8px;
                background: #fff;
                overflow: hidden;
                margin-bottom: 10px;
            }

            .housing-edit-panel__header {
                width: 100%;
                border: 0;
                background: rgba(248, 249, 250, .95);
                padding: 12px 14px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                text-align: left;
            }

            .housing-edit-panel__title {
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 600;
            }

            .housing-edit-panel__meta {
                color: #6c757d;
                font-size: .82rem;
                white-space: nowrap;
            }

            .housing-edit-panel__body {
                padding: 14px;
                border-top: 1px solid rgba(0, 0, 0, .08);
            }

            .housing-edit-panel__chevron {
                transition: transform .15s ease;
            }

            .housing-edit-panel.is-open .housing-edit-panel__chevron {
                transform: rotate(180deg);
            }
        </style>

        <div class="row g-3">
            <div class="col-12 col-xl-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body" style="text-align: center;padding: 2px;">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Reference</div>
                                <div class="fw-semibold">{{ $product->reference ?: '—' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">EAN13</div>
                                <div class="fw-semibold">{{ $product->ean13 ?: '—' }}</div>
                            </div>

                            @if($product->attribute_housing)
                                <div class="col-4">
                                    <div class="text-muted small">Housing</div>
                                    <div class="fw-semibold">{{ $product->attribute_housing ?: '—' }}</div>
                                </div>
                            @else
                                <div class="col-4">
                                    <div class="text-muted small">Location</div>
                                    <div class="fw-semibold">{{ $product->product_location ?: '—' }}</div>
                                </div>
                            @endif

                            <div class="col-4">
                                <div class="text-muted small">Stock</div>
                                <div class="fw-semibold">{{ $product->stock }}</div>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Stock arrive</div>
                                <div class="fw-semibold">{{ $product->quantity_arrive }}</div>
                            </div>

                            <div class="col-6">
                                <div class="text-muted small">ID Product</div>
                                <div class="fw-semibold">{{ $product->id_product }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">ID Attribute</div>
                                <div class="fw-semibold">{{ $product->id_product_attribute }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        @if($canEditLogistics)
                            <div class="housing-edit-panel" id="panel-location">
                                <button type="button" class="housing-edit-panel__header" onclick="toggleHousingPanel('panel-location')">
                                    <span class="housing-edit-panel__title">
                                        <i class="fa-solid fa-location-dot"></i>
                                        Location / Housing
                                    </span>
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="housing-edit-panel__meta">{{ $product->operational_location ?: '—' }}</span>
                                        <i class="fa-solid fa-chevron-down housing-edit-panel__chevron"></i>
                                    </span>
                                </button>
                                <div class="housing-edit-panel__body d-none">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-8">
                                            <label class="form-label">Location / Housing</label>
                                            <input type="text" class="form-control" id="edit_location" value="{{ $product->operational_location }}" style="text-align: center;">
                                        </div>
                                        <div class="col-12 col-md-4 d-grid align-items-end">
                                            <button class="btn btn-primary mt-md-4" type="button" onclick="updateLocation()">
                                                <i class="fa-solid fa-location-dot"></i> Update location
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="housing-edit-panel" id="panel-ean13">
                                <button type="button" class="housing-edit-panel__header" onclick="toggleHousingPanel('panel-ean13')">
                                    <span class="housing-edit-panel__title">
                                        <i class="fa-solid fa-barcode"></i>
                                        EAN13
                                    </span>
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="housing-edit-panel__meta">{{ $product->ean13 ?: '—' }}</span>
                                        <i class="fa-solid fa-chevron-down housing-edit-panel__chevron"></i>
                                    </span>
                                </button>
                                <div class="housing-edit-panel__body d-none">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-8">
                                            <label class="form-label">EAN13</label>
                                            <input type="text" class="form-control" id="edit_ean13" value="{{ $product->ean13 }}" style="text-align: center;">
                                        </div>
                                        <div class="col-12 col-md-4 d-grid align-items-end">
                                            <button class="btn btn-outline-primary mt-md-4" type="button" onclick="updateEan13()">
                                                <i class="fa-solid fa-barcode"></i> Update EAN13
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="housing-edit-panel" id="panel-measures">
                                <button type="button" class="housing-edit-panel__header" onclick="toggleHousingPanel('panel-measures')">
                                    <span class="housing-edit-panel__title">
                                        <i class="fa-solid fa-ruler-combined"></i>
                                        Measures
                                    </span>
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="housing-edit-panel__meta">{{ number_format((float) $product->weight, 2) }}( kg ) | {{ number_format((float) $product->width, 0, '.', '') }} * {{ number_format((float) $product->height, 0, '.', '') }} * {{ number_format((float) $product->depth, 0, '.', '') }} ( cm )</span>
                                        <i class="fa-solid fa-chevron-down housing-edit-panel__chevron"></i>
                                    </span>
                                </button>
                                <div class="housing-edit-panel__body d-none">
                                    <div class="row g-3">
                                        <div class="col-6 col-md-3">
                                            <label class="form-label">Weight</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="edit_weight" value="{{ number_format((float) $product->weight, 2, '.', '') }}" style="text-align: center;">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label">Width</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="edit_width" value="{{ number_format((float) $product->width, 2, '.', '') }}" style="text-align: center;">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label">Height</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="edit_height" value="{{ number_format((float) $product->height, 2, '.', '') }}" style="text-align: center;">
                                        </div>
                                        <div class="col-6 col-md-3">
                                            <label class="form-label">Depth</label>
                                            <input type="number" step="0.01" min="0" class="form-control" id="edit_depth" value="{{ number_format((float) $product->depth, 2, '.', '') }}" style="text-align: center;">
                                        </div>
                                        <div class="col-12 d-grid mt-2">
                                            <button class="btn btn-outline-dark" type="button" onclick="updateMeasures()">
                                                <i class="fa-solid fa-ruler-combined"></i> Update measures
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($canEditAdminFields)
                            <div class="housing-edit-panel" id="panel-reference">
                                <button type="button" class="housing-edit-panel__header" onclick="toggleHousingPanel('panel-reference')">
                                    <span class="housing-edit-panel__title">
                                        <i class="fa-solid fa-hashtag"></i>
                                        Reference
                                    </span>
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="housing-edit-panel__meta">{{ $product->reference ?: '—' }}</span>
                                        <i class="fa-solid fa-chevron-down housing-edit-panel__chevron"></i>
                                    </span>
                                </button>
                                <div class="housing-edit-panel__body d-none">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-8">
                                            <label class="form-label">Reference</label>
                                            <input type="text" class="form-control" id="edit_reference" value="{{ $product->reference }}" style="text-align: center;">
                                        </div>
                                        <div class="col-12 col-md-4 d-grid align-items-end">
                                            <button class="btn btn-outline-primary mt-md-4" type="button" onclick="updateReference()">
                                                <i class="fa-solid fa-hashtag"></i> Update reference
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="housing-edit-panel" id="panel-stock">
                                <button type="button" class="housing-edit-panel__header" onclick="toggleHousingPanel('panel-stock')">
                                    <span class="housing-edit-panel__title">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                        Stock
                                    </span>
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="housing-edit-panel__meta">{{ $product->stock }}</span>
                                        <i class="fa-solid fa-chevron-down housing-edit-panel__chevron"></i>
                                    </span>
                                </button>
                                <div class="housing-edit-panel__body d-none">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-8">
                                            <label class="form-label">Stock</label>
                                            <input type="number" step="1" class="form-control" id="edit_stock" value="{{ $product->stock }}" style="text-align: center;">
                                        </div>
                                        <div class="col-12 col-md-4 d-grid align-items-end">
                                            <button class="btn btn-outline-danger mt-md-4" type="button" onclick="updateStock()">
                                                <i class="fa-solid fa-boxes-stacked"></i> Update stock
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="housing-edit-panel" id="panel-stock-arrive">
                                <button type="button" class="housing-edit-panel__header" onclick="toggleHousingPanel('panel-stock-arrive')">
                                    <span class="housing-edit-panel__title">
                                        <i class="fa-solid fa-truck-ramp-box"></i>
                                        Stock arrive
                                    </span>
                                    <span class="d-flex align-items-center gap-2">
                                        <span class="housing-edit-panel__meta">{{ $product->quantity_arrive }}</span>
                                        <i class="fa-solid fa-chevron-down housing-edit-panel__chevron"></i>
                                    </span>
                                </button>
                                <div class="housing-edit-panel__body d-none">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-8">
                                            <label class="form-label">Stock arrive</label>
                                            <input type="number" step="1" class="form-control" id="edit_stock_arrive" value="{{ $product->quantity_arrive }}" style="text-align: center;">
                                        </div>
                                        <div class="col-12 col-md-4 d-grid align-items-end">
                                            <button class="btn btn-outline-danger mt-md-4" type="button" onclick="updateStockArrive()">
                                                <i class="fa-solid fa-truck-ramp-box"></i> Update stock arrive
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!$canEditLogistics && !$canEditAdminFields)
                            <div class="text-muted small text-center">You do not have permission to edit this product.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border-0" style="text-align: center;">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Orders with this product</div>
                        @if($orderRows->count())
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th>Product</th>
                                            <th>Reference</th>
                                            <th>EAN13</th>
                                            <th class="text-end">Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($orderRows as $row)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">#{{ $row->id_order }}</div>
                                                    <div class="text-muted small">{{ $row->order_reference ?: '—' }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge" style="background: {{ $row->state_color }}; color: #fff;">{{ $row->state_label }}</span>
                                                </td>
                                                <td>{{ $row->product_name ?: '—' }}</td>
                                                <td>{{ $row->product_reference ?: '—' }}</td>
                                                <td>{{ $row->product_ean13 ?: '—' }}</td>
                                                <td class="text-end">
                                                    <div>{{ (int) $row->product_quantity }}</div>
                                                    <div class="text-muted small">In stock: {{ (int) $row->product_quantity_in_stock }}</div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-muted small">No orders found in the tracked states for this product.</div>
                        @endif
                    </div>
                </div>
            </div>

            @if($canEditAdminFields)
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="text-align: center;">
                        <div class="card-body" style="margin: 0; padding: 1rem 2px;">
                            <div class="justify-content-between align-items-center gap-2 mb-3">
                                <div>
                                    <div class="fw-semibold">Latest history</div>
                                    <div class="text-muted small">Click a row to view audit details.</div>
                                </div>
                            </div>

                            @if($history->count())
                                @php
                                    $formatHistoryValue = function ($value) {
                                        if ($value === null || $value === '') {
                                            return '—';
                                        }

                                        $normalized = str_replace(',', '.', trim((string) $value));

                                        return is_numeric($normalized)
                                            ? number_format((float) $normalized, 2, '.', '')
                                            : $value;
                                    };
                                @endphp

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0 table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 22%;">Operation</th>
                                                <th style="width: 22%;">Field</th>
                                                <th style="width: 28%;">Old</th>
                                                <th style="width: 28%;">New</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($history as $row)
                                                @php
                                                    $historyRowId = 'history-details-' . $loop->index;
                                                @endphp
                                                <tr role="button" onclick="toggleHistoryDetails('{{ $historyRowId }}')">
                                                    <td>
                                                        <div class="fw-semibold">{{ $row->operation }}</div>
                                                        <div class="text-muted small d-md-none">{{ $row->created_at }}</div>
                                                    </td>
                                                    <td>{{ $row->field_name }}</td>
                                                    <td class="text-break">{{ $formatHistoryValue($row->old_value) }}</td>
                                                    <td class="text-break">{{ $formatHistoryValue($row->new_value) }}</td>
                                                </tr>
                                                <tr id="{{ $historyRowId }}" class="d-none bg-light">
                                                    <td colspan="4">
                                                        <div class="row g-2 small">
                                                            <div class="col-12 col-md-3">
                                                                <div class="text-muted">Date</div>
                                                                <div class="fw-semibold">{{ $row->created_at }}</div>
                                                            </div>
                                                            <div class="col-12 col-md-3">
                                                                <div class="text-muted">User</div>
                                                                <div class="fw-semibold">{{ $row->user_name ?? ('#' . ($row->user_id ?? '—')) }}</div>
                                                            </div>
                                                            <div class="col-6 col-md-2">
                                                                <div class="text-muted">Target</div>
                                                                <div class="fw-semibold">{{ $row->target_type ?? '—' }}</div>
                                                            </div>
                                                            <div class="col-6 col-md-2">
                                                                <div class="text-muted">Product ID</div>
                                                                <div class="fw-semibold">{{ $row->id_product ?? '—' }}</div>
                                                            </div>
                                                            <div class="col-6 col-md-2">
                                                                <div class="text-muted">Attribute ID</div>
                                                                <div class="fw-semibold">{{ $row->id_product_attribute ?? '—' }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-muted small">No history recorded yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <script>
            function toggleHousingPanel(panelId) {
                const panel = document.getElementById(panelId);
                if (!panel) return;

                const body = panel.querySelector('.housing-edit-panel__body');
                if (!body) return;

                body.classList.toggle('d-none');
                panel.classList.toggle('is-open', !body.classList.contains('d-none'));
            }

            function toggleHistoryDetails(rowId) {
                const row = document.getElementById(rowId);
                if (!row) return;
                row.classList.toggle('d-none');
            }
        </script>
    @endif
</div>
