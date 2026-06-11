@extends('layouts.app')

@section('content')

    <style>
        .store-compare-panel { margin: 10px 0; padding: 20px; width: 100%; }
        .store-compare-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 16px; width: 100%; }
        .store-compare-toolbar-left { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .store-compare-toolbar form { margin: 0; }
        .store-compare-counters { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin-bottom: 16px; }
        .store-compare-counter { background: #fff; border: 1px solid #ddd; padding: 12px 14px; }
        .store-compare-counter span { display: block; color: #666; font-size: 11px; text-transform: uppercase; }
        .store-compare-counter strong { display: block; margin-top: 4px; font-size: 22px; }
        .store-compare-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 10px; margin-bottom: 16px; }
        .store-compare-info-item { background: #fff; border: 1px solid #ddd; padding: 12px 14px; }
        .store-compare-info-item span { display: block; color: #666; font-size: 11px; text-transform: uppercase; margin-bottom: 4px; }
        .store-compare-info-item strong { display: block; color: #333; }
        .store-compare-table-wrap { max-height: 70vh; overflow: auto; border: 1px solid #ddd; background: #fff; }
        .store-compare-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .store-compare-table th, .store-compare-table td { border-bottom: 1px solid #eee; border-right: 1px solid #eee; padding: 7px 8px; white-space: nowrap; text-align: center; vertical-align: middle; }
        .store-compare-table th { position: sticky; top: 0; background: #f8f9fa; z-index: 2; }
        .store-compare-table thead tr:nth-child(2) th { top: 34px; z-index: 3; }
        .store-compare-reference,
        .store-compare-reference-header {
            background: #e9ecef !important;
            color: #343a40;
            font-weight: 700;
        }
        .store-compare-reference { text-align: center !important; }
        .store-compare-group-asm { background: #dc3545 !important; color: #fff; }
        .store-compare-group-asd { background: dodgerblue !important; color: #fff; }
        .store-compare-badge { display: inline-block; min-width: 38px; padding: 3px 7px; border-radius: 12px; font-weight: 700; font-size: 11px; }
        .store-compare-yes { color: #0f5132; background: #d1e7dd; }
        .store-compare-no { color: #842029; background: #f8d7da; }
        .store-compare-neutral { color: #41464b; background: #e2e3e5; }
        .store-compare-warning { color: #664d03; background: #fff3cd; }
        .store-compare-missing { color: #842029; background: #f8d7da; font-weight: 700; }
        .store-compare-discount-individual { background: #f8d7da !important; color: #842029; font-weight: 700; }
        .store-compare-discount-global { background: #cff4fc !important; color: #055160; font-weight: 700; }
    </style>

    <div class="navbar navbar-light customPanel store-compare-panel">
        <div style="width: 100%;">
            <div class="store-compare-toolbar">
                <div class="store-compare-toolbar-left">
                    <form method="GET" action="{{ route('web.product_store_compare.index') }}" style="display: flex; gap: 10px; align-items: center;">
                        <select name="brand" class="form-control" onchange="this.form.submit()" style="min-width: 280px;">
                            <option value="">Select brand</option>
                            @foreach($brands as $availableBrand)
                                <option value="{{ $availableBrand }}" @selected($availableBrand === $brand)>{{ $availableBrand }}</option>
                            @endforeach
                        </select>
                    </form>

                    @if($brand !== '')
                        <form method="POST" action="{{ route('web.product_store_compare.catalog') }}" enctype="multipart/form-data" style="display: flex; gap: 8px; align-items: center; flex-wrap: nowrap;">
                            @csrf
                            <input type="hidden" name="brand" value="{{ $brand }}">
                            <input type="file" name="catalog" class="form-control" accept=".csv,.txt" title="CSV columns: 1 reference, 2 price, 3 discount" required>
                            <button class="btn btn-primary" type="submit" style="white-space: nowrap;">
                                <i class="fa-solid fa-upload"></i> Supplier
                            </button>
                        </form>

                        <a class="btn btn-success" href="{{ route('web.product_store_compare.csv', ['brand' => $brand]) }}">
                            <i class="fa-solid fa-file-csv"></i> CSV
                        </a>
                        <a class="btn btn-danger" href="{{ route('web.product_store_compare.pdf', ['brand' => $brand]) }}">
                            <i class="fa-solid fa-file-pdf"></i> PDF
                        </a>
                    @endif
                </div>

                <span class="badge bg-secondary">{{ number_format(count($rows), 0, ',', '.') }} products</span>
            </div>

            @if($brand === '')
                <div class="alert alert-info" style="width: 100%;">Select a brand to build the comparison.</div>
            @else
                <div class="store-compare-counters">
                    @foreach([
                        'LIST PACKS' => 'packs',
                        'LIST END OF LIFE' => 'end_of_life',
                        'LIST GLOBAL DISCOUNTS' => 'global_discounts',
                        'LIST INACTIVES PRODUCTS' => 'inactive_products',
                        'LIST EXCLUSIVES PRODUCTS' => 'exclusive_products',
                        'LIST NOT VISIBLE PRODUCTS' => 'not_visible_products',
                        'LIST EXISTING CATEGORIES' => 'existing_categories',
                        'LIST BRANDS' => 'brands',
                        'LIST MANUFACTURERS' => 'manufacturers',
                    ] as $label => $key)
                        <div class="store-compare-counter">
                            <span>{{ $label }}</span>
                            <strong>{{ number_format($counters[$key] ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="store-compare-info">
                    <div class="store-compare-info-item">
                        <span>Manufacturer</span>
                        <strong>
                            @if($supplierManufacturerInfo['same_manufacturer'])
                                Same for all products: {{ $supplierManufacturerInfo['manufacturers'][0] ?? '-' }}
                            @else
                                Multiple: {{ implode(', ', $supplierManufacturerInfo['manufacturers']) }}
                            @endif
                        </strong>
                    </div>
                    <div class="store-compare-info-item">
                        <span>Supplier</span>
                        <strong>
                            @if($supplierManufacturerInfo['same_supplier'])
                                Same for all products: {{ $supplierManufacturerInfo['suppliers'][0] ?? '-' }}
                            @else
                                Multiple: {{ implode(', ', $supplierManufacturerInfo['suppliers']) }}
                            @endif
                        </strong>
                    </div>
                </div>

                @if($supplierCatalog)
                    <div class="alert alert-info" style="width: 100%;">
                        Supplier loaded:
                        <strong>{{ $supplierCatalog['name'] ?? '-' }}</strong>
                        - CSV columns: <code>1 reference</code>, <code>2 price</code>, <code>3 discount</code>
                        - {{ number_format(count($supplierCatalog['pending_imports'] ?? []), 0, ',', '.') }} pending imports
                        · {{ number_format($supplierCatalog['count'] ?? 0, 0, ',', '.') }} references
                    </div>
                @endif

                @php
                    $asmColumns = ['pack', 'category', 'visibility', 'exclusive', 'is_active', 'discount', 'wm_deprecated'];
                    $asdColumns = ['wm_deprecated', 'discount', 'is_active', 'exclusive', 'visibility', 'category', 'pack'];

                    if ($supplierCatalog) {
                        $asmColumns[] = 'supplier_discount';
                        $asmColumns[] = 'supplier_purchase';
                        array_unshift($asdColumns, 'supplier_purchase');
                        array_unshift($asdColumns, 'supplier_discount');
                    }

                    $asmColumns[] = 'purchase_price';
                    array_unshift($asdColumns, 'purchase_price');

                    $columnLabels = [
                        'category' => 'Categories',
                        'pack' => 'Pack',
                        'visibility' => 'Visibility',
                        'exclusive' => 'Exclusive',
                        'is_active' => 'Active',
                        'discount' => 'Discount',
                        'wm_deprecated' => 'End of life',
                        'supplier_purchase' => 'Supplier',
                        'supplier_discount' => 'Supplier %',
                    ];
                @endphp

                <div class="store-compare-table-wrap">
                    <table class="store-compare-table" id="storeCompareTable">
                        <thead>
                            <tr>
                                <th colspan="{{ count($asmColumns) }}" class="store-compare-group-asm">ASM</th>
                                <th rowspan="2" class="store-compare-reference-header">Reference</th>
                                <th colspan="{{ count($asdColumns) }}" class="store-compare-group-asd">ASD</th>
                            </tr>
                            <tr>
                                @foreach($asmColumns as $column)
                                    <th>{{ $column === 'purchase_price' ? 'Purchase' : $columnLabels[$column] }}</th>
                                @endforeach
                                @foreach($asdColumns as $column)
                                    <th>{{ $column === 'purchase_price' ? 'Purchase' : $columnLabels[$column] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    @include('areas.web.partials.product-store-cells', [
                                        'store' => $row['asm'],
                                        'exclusive' => $row['exclusive'] === 'ASM only',
                                        'columns' => $asmColumns,
                                        'supplierPurchase' => $row['supplier_catalog_purchase'] ?? null,
                                        'supplierDiscount' => $row['supplier_catalog_discount'] ?? null,
                                    ])
                                    <td class="store-compare-reference">{{ $row['reference'] }}</td>
                                    @include('areas.web.partials.product-store-cells', [
                                        'store' => $row['asd'],
                                        'exclusive' => $row['exclusive'] === 'ASD only',
                                        'columns' => $asdColumns,
                                        'supplierPurchase' => $row['supplier_catalog_purchase'] ?? null,
                                        'supplierDiscount' => $row['supplier_catalog_discount'] ?? null,
                                    ])
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($supplierCatalog && !empty($supplierCatalog['pending_imports']))
                    <div style="width: 100%; margin-top: 18px;">
                        <h5>Supplier references not found in ASM or ASD</h5>
                        <div class="store-compare-table-wrap">
                            <table class="store-compare-table">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Purchase price</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplierCatalog['pending_imports'] as $pending)
                                        <tr>
                                            <td class="store-compare-reference">{{ $pending['reference'] }}</td>
                                            <td>{{ number_format($pending['price'], 2, ',', '.') }}</td>
                                            <td>importação pendente</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

            @endif
        </div>
    </div>

@endsection
