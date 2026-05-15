<div id="housingInfo">
    @if($mode === 'empty' && empty($isHousingSearch))
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fa-solid fa-circle-xmark fa-2x text-danger"></i></div>
                <div class="fw-semibold">No product found</div>
                <div class="text-muted small">No match for <strong>{{ $search }}</strong>.</div>
            </div>
        </div>
    @elseif(in_array($mode, ['multiple', 'housing_list'], true))
        @php
            $isHousing = !empty($isHousingSearch);
            $bulkHousing = $housing ?? ($isHousing ? $search : null);
        @endphp

        @if($isHousing)
        @else
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="fw-semibold mb-1">Multiple matches found</div>
                    <div class="text-muted small">Seleciona o registo certo para abrir a ficha operacional.</div>
                </div>
            </div>
        @endif

        <div class="card shadow-sm border-0 mb-3">
            @if($products->count() > 0)
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="text-align: center;">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>EAN13</th>
                                <th>Location</th>
                                <th>Stock</th>
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
                <div class="card-body text-center py-4">
                    <div class="mb-2"><i class="fa-solid fa-location-dot fa-2x text-muted"></i></div>
                    <div class="fw-semibold">No products currently in this housing</div>
                    <div class="text-muted small">Podes usar a zona de bulk abaixo para associar produtos a este housing.</div>
                </div>
            @endif
        </div>

        @if($isHousing && $canEditLogistics)
            <div class="card shadow-sm border-0 mb-3" id="bulkHousingPanel" data-housing="{{ $bulkHousing }}">
                <div class="card-body" style="text-align: center;">
                    <div class="justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <div class="fw-semibold">Bulk housing</div>
                        </div>
                        <span class="badge text-bg-primary">{{ $bulkHousing }}</span>
                    </div>

                    <div class="input-group input-group-lg mb-3">
                        <input type="text" id="bulk_scan_input" class="form-control text-center" autocomplete="off" placeholder="Scan product EAN / Reference">
                        <button class="btn btn-primary" type="button" onclick="bulkAddScanFromInput()">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                    <div id="bulkScanRows" class="d-grid gap-2 mb-3"></div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg" type="button" onclick="bulkSaveHousing()">
                            <i class="fa-solid fa-floppy-disk"></i> Save bulk housing
                        </button>
                        <button class="btn btn-outline-secondary" type="button" onclick="bulkClearRows()">
                            Clear scanned products
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @elseif($mode === 'single' && $product)
        <input type="hidden" id="selected_id_product" value="{{ $product->id_product }}">
        <input type="hidden" id="selected_id_product_attribute" value="{{ $product->id_product_attribute }}">

        <div class="row g-3">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div>
                                <div class="text-muted small mb-1">Selected product</div>
                                <h5 class="mb-1">{{ $product->name ?: 'Unnamed product' }}</h5>
                                <div class="text-muted small">
                                    {{ strtoupper($product->entity_type) }}
                                    @if($product->id_product_attribute > 0)
                                        · Combination #{{ $product->id_product_attribute }}
                                    @endif
                                    · Product #{{ $product->id_product }}
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($orderSummary as $badge)
                                    @if(($badge['count'] ?? 0) > 0)
                                        <span class="badge" style="background: {{ $badge['color'] }}; color: #fff;">{{ $badge['label'] }}: {{ $badge['count'] }}</span>
                                    @endif
                                @endforeach
                                @if(collect($orderSummary)->sum('count') === 0)
                                    <span class="badge text-bg-light">No tracked orders</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Current detail</div>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="text-muted small">Reference</div>
                                <div class="fw-semibold">{{ $product->reference ?: '—' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">EAN13</div>
                                <div class="fw-semibold">{{ $product->ean13 ?: '—' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Product location</div>
                                <div class="fw-semibold">{{ $product->product_location ?: '—' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Attribute housing</div>
                                <div class="fw-semibold">{{ $product->attribute_housing ?: '—' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Operational location</div>
                                <div class="fw-semibold">{{ $product->operational_location ?: '—' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Location source</div>
                                <div class="fw-semibold">{{ $product->location_source === 'attribute_housing' ? 'ps_product_attribute.housing' : 'ps_product.location' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Stock</div>
                                <div class="fw-semibold">{{ number_format((float) $product->stock, 2) }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Stock arrive</div>
                                <div class="fw-semibold">{{ number_format((float) $product->quantity_arrive, 2) }}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Weight</div>
                                <div class="fw-semibold">{{ number_format((float) $product->weight, 2) }}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Width</div>
                                <div class="fw-semibold">{{ number_format((float) $product->width, 2) }}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Height</div>
                                <div class="fw-semibold">{{ number_format((float) $product->height, 2) }}</div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Depth</div>
                                <div class="fw-semibold">{{ number_format((float) $product->depth, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Quick updates</div>

                        @if($canEditLogistics)
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-8">
                                    <label class="form-label">Location / Housing</label>
                                    <input type="text" class="form-control" id="edit_location" value="{{ $product->operational_location }}">
                                    <div class="form-text">
                                        @if($product->location_source === 'attribute_housing')
                                            Updates <code>ps_product_attribute.housing</code>.
                                        @else
                                            Updates <code>ps_product.location</code>.
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 d-grid align-items-end">
                                    <button class="btn btn-primary mt-md-4" type="button" onclick="updateLocation()">
                                        <i class="fa-solid fa-location-dot"></i> Update location
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-8">
                                    <label class="form-label">EAN13</label>
                                    <input type="text" class="form-control" id="edit_ean13" value="{{ $product->ean13 }}">
                                </div>
                                <div class="col-12 col-md-4 d-grid align-items-end">
                                    <button class="btn btn-outline-primary mt-md-4" type="button" onclick="updateEan13()">
                                        <i class="fa-solid fa-barcode"></i> Update EAN13
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Weight</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="edit_weight" value="{{ number_format((float) $product->weight, 2, '.', '') }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Width</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="edit_width" value="{{ number_format((float) $product->width, 2, '.', '') }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Height</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="edit_height" value="{{ number_format((float) $product->height, 2, '.', '') }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label">Depth</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="edit_depth" value="{{ number_format((float) $product->depth, 2, '.', '') }}">
                                </div>
                                <div class="col-12 d-grid mt-2">
                                    <button class="btn btn-outline-dark" type="button" onclick="updateMeasures()">
                                        <i class="fa-solid fa-ruler-combined"></i> Update measures
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($canEditAdminFields)
                            <hr>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-8">
                                    <label class="form-label">Reference (admin only)</label>
                                    <input type="text" class="form-control" id="edit_reference" value="{{ $product->reference }}">
                                </div>
                                <div class="col-12 col-md-4 d-grid align-items-end">
                                    <button class="btn btn-outline-primary mt-md-4" type="button" onclick="updateReference()">
                                        <i class="fa-solid fa-hashtag"></i> Update reference
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Stock (admin only)</label>
                                    <input type="number" step="1" class="form-control" id="edit_stock" value="{{ number_format((float) $product->stock, 2, '.', '') }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Stock arrive (admin only)</label>
                                    <input type="number" step="0.01" class="form-control" id="edit_stock_arrive" value="{{ number_format((float) $product->quantity_arrive, 2, '.', '') }}">
                                </div>
                                <div class="col-12 col-md-6 d-grid">
                                    <button class="btn btn-outline-danger" type="button" onclick="updateStock()">
                                        <i class="fa-solid fa-boxes-stacked"></i> Update stock
                                    </button>
                                </div>
                                <div class="col-12 col-md-6 d-grid">
                                    <button class="btn btn-outline-danger" type="button" onclick="updateStockArrive()">
                                        <i class="fa-solid fa-truck-ramp-box"></i> Update stock arrive
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if(!$canEditLogistics && !$canEditAdminFields)
                            <div class="text-muted small">You do not have permission to edit this product.</div>
                        @endif
            <div class="col-12">
                <div class="card shadow-sm border-0">
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
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="fw-semibold mb-3">Latest history</div>
                        @if($history->count())
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Operation</th>
                                            <th>Field</th>
                                            <th>Old</th>
                                            <th>New</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($history as $row)
                                            <tr>
                                                <td>{{ $row->created_at }}</td>
                                                <td>{{ $row->operation }}</td>
                                                <td>{{ $row->field_name }}</td>
                                                <td>{{ $row->old_value }}</td>
                                                <td>{{ $row->new_value }}</td>
                                                <td>{{ $row->user_name ?: ('#' . $row->user_id) }}</td>
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
    @endif
</div>
