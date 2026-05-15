@extends('layouts.app')

@section('content')

<style>
    #yieldContent{ width: 100% !important; margin: 0 !important; }
    .table > :not(caption) > * > *{ background-color: #FFF; }
</style>
<div class="container-fluid" style="padding: 0;">
    <div class="housing-tool-shell mx-auto" style="max-width: 860px;">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <div class="align-items-center justify-content-between gap-2 flex-wrap mb-2">
                    <div style="text-align: center;">
                        <h4 class="mb-1">Warehouse Product Lookup</h4>
                    </div>
                </div>

                <div class="input-group input-group-lg">
                    <input
                        type="text"
                        name="scan"
                        id="scan"
                        class="form-control text-center"
                        autocomplete="off"
                        placeholder="Scan / EAN / Reference / Housing"
                    >
                    <button class="btn btn-primary" type="button" onclick="requestData()">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>
        </div>
        <div id="housingToolFeedback" class="mb-3" style="display:none;"></div>
        <div id="housingInfo">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fa-solid fa-barcode fa-2x text-muted"></i>
                    </div>
                    <div class="fw-semibold">Ready to scan</div>
                    <div class="text-muted small">Use the field above to search a product.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const scanInput = document.getElementById('scan');
    let typingTimer = null;
    const doneTypingInterval = 350;

    document.addEventListener('DOMContentLoaded', function () {
        scanInput.focus();
    });

    scanInput.addEventListener('keyup', function (event) {
        clearTimeout(typingTimer);

        if (event.key === 'Enter') {
            requestData();
            return;
        }

        typingTimer = setTimeout(function () {
            if (scanInput.value.trim().length > 0) {
                requestData();
            }
        }, doneTypingInterval);
    });

    scanInput.addEventListener('keydown', function () {
        clearTimeout(typingTimer);
    });

    function clearSearch() {
        scanInput.value = '';
        document.getElementById('housingInfo').innerHTML = `
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-3"><i class="fa-solid fa-barcode fa-2x text-muted"></i></div>
                    <div class="fw-semibold">Ready to scan</div>
                    <div class="text-muted small">Use the field above to search a product.</div>
                </div>
            </div>
        `;
        hideFeedback();
        scanInput.focus();
    }

    function requestData(selectedProductId = null, selectedAttributeId = null, selectedSearchTerm = null) {
        const value = (selectedSearchTerm ?? scanInput.value).trim();
        if (!value.length && !selectedProductId) {
            showFeedback('warning', 'Insert a value before searching.');
            scanInput.focus();
            return;
        }

        const payload = {
            _token: "{{ csrf_token() }}",
            barcode: value
        };

        if (selectedProductId !== null && selectedProductId !== '' && !isNaN(selectedProductId)) {
            payload.id_product = parseInt(selectedProductId, 10);
        }

        if (selectedAttributeId !== null && selectedAttributeId !== '' && !isNaN(selectedAttributeId)) {
            payload.id_product_attribute = parseInt(selectedAttributeId, 10);
        }

        $.ajax({
            type: 'POST',
            url: "{{ route('housing.requestData') }}",
            data: payload,
            success: function (response) {
                $('#housingInfo').html(response);
                hideFeedback();

                if (document.getElementById('bulkHousingPanel')) {
                    initBulkHousingEvents(true);
                } else {
                    bulkHousingProducts = [];
                    scanInput.focus();
                }

                if (selectedSearchTerm) {
                    scanInput.value = selectedSearchTerm;
                }
            },
            error: function (xhr) {
                showFeedback('danger', xhr.responseJSON?.message || 'Unable to load product data.');
            }
        });
    }

    function performHousingAction(routeName, payload, successMessage) {
        $.ajax({
            type: 'POST',
            url: routeName,
            data: Object.assign({ _token: "{{ csrf_token() }}" }, payload),
            success: function (response) {
                showFeedback('success', response.message || successMessage);
                reloadSelectedProduct();
            },
            error: function (xhr) {
                showFeedback('danger', xhr.responseJSON?.message || 'Operation failed.');
            }
        });
    }

    function reloadSelectedProduct() {
        const idProduct = $('#selected_id_product').val();
        const idProductAttribute = $('#selected_id_product_attribute').val();
        requestData(idProduct, idProductAttribute, scanInput.value.trim());
    }

    function updateLocation() {
        performHousingAction("{{ route('housing.editLocation') }}", {
            id_product: $('#selected_id_product').val(),
            id_product_attribute: $('#selected_id_product_attribute').val(),
            stand: $('#edit_location').val(),
            search_term: scanInput.value.trim()
        }, 'Location updated successfully.');
    }

    function updateMeasures() {
        performHousingAction("{{ route('housing.editMeasures') }}", {
            id_product: $('#selected_id_product').val(),
            id_product_attribute: $('#selected_id_product_attribute').val(),
            weight: $('#edit_weight').val(),
            width: $('#edit_width').val(),
            height: $('#edit_height').val(),
            depth: $('#edit_depth').val(),
            search_term: scanInput.value.trim()
        }, 'Measures updated successfully.');
    }

    function updateReference() {
        performHousingAction("{{ route('housing.editReference') }}", {
            id_product: $('#selected_id_product').val(),
            id_product_attribute: $('#selected_id_product_attribute').val(),
            reference: $('#edit_reference').val(),
            search_term: scanInput.value.trim()
        }, 'Reference updated successfully.');
    }

    function updateEan13() {
        performHousingAction("{{ route('housing.editEan13') }}", {
            id_product: $('#selected_id_product').val(),
            id_product_attribute: $('#selected_id_product_attribute').val(),
            ean13: $('#edit_ean13').val(),
            search_term: scanInput.value.trim()
        }, 'EAN13 updated successfully.');
    }

    function updateStock() {
        performHousingAction("{{ route('housing.editStock') }}", {
            id_product: $('#selected_id_product').val(),
            id_product_attribute: $('#selected_id_product_attribute').val(),
            stock: $('#edit_stock').val(),
            search_term: scanInput.value.trim()
        }, 'Stock updated successfully.');
    }

    function updateStockArrive() {
        performHousingAction("{{ route('housing.editStockArrive') }}", {
            id_product: $('#selected_id_product').val(),
            id_product_attribute: $('#selected_id_product_attribute').val(),
            stock_arrive: $('#edit_stock_arrive').val(),
            search_term: scanInput.value.trim()
        }, 'Stock arrive updated successfully.');
    }

    function pickProduct(idProduct, idProductAttribute, searchLabel) {
        requestData(idProduct, idProductAttribute, searchLabel);
    }

    function showFeedback(type, message) {
        const el = document.getElementById('housingToolFeedback');
        el.style.display = 'block';
        el.innerHTML = `<div class="alert alert-${type} mb-0">${message}</div>`;
    }

    function hideFeedback() {
        const el = document.getElementById('housingToolFeedback');
        el.style.display = 'none';
        el.innerHTML = '';
    }

    let bulkHousingProducts = [];

    function initBulkHousingEvents(resetList = true) {
        if (resetList) {
            bulkHousingProducts = [];
        }

        renderBulkRows();

        const bulkInput = document.getElementById('bulk_product_scan');
        if (bulkInput) {
            bulkInput.focus();
            bulkInput.onkeyup = function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    bulkAddProductFromScan();
                }
            };
        } else {
            scanInput.focus();
        }
    }

    function isHousingCode(value) {
        return /^[A-Za-z0-9]{2}-[A-Za-z0-9]{2}-[A-Za-z0-9]{2}$/.test(String(value).trim());
    }

    function getActiveBulkHousing() {
        const input = document.getElementById('bulk_housing_target');
        return input ? input.value.trim() : '';
    }

    function bulkAddProductFromScan() {
        const input = document.getElementById('bulk_product_scan');
        if (!input) {
            return;
        }

        const scan = input.value.trim();

        if (isHousingCode(scan)) {
            showFeedback('warning', 'Scan a product EAN/reference here, not another housing code.');
            input.value = '';
            input.focus();
            return;
        }

        if (!scan.length) {
            showFeedback('warning', 'Scan a product EAN/reference before adding.');
            input.focus();
            return;
        }

        $.ajax({
            type: 'POST',
            url: "{{ route('housing.bulkLookupProduct') }}",
            data: {
                _token: "{{ csrf_token() }}",
                scan: scan
            },
            success: function (response) {
                if (!response.ok || !response.product) {
                    showFeedback('danger', response.message || 'Unable to identify product.');
                    input.select();
                    return;
                }

                bulkAddProductRow(scan, response.product);
                input.value = '';
                input.focus();
            },
            error: function (xhr) {
                showFeedback('danger', xhr.responseJSON?.message || 'Unable to identify product.');
                input.select();
            }
        });
    }

    function bulkAddProductRow(scan, product) {
        const rowKey = String(product.row_key || `${product.id_product}-${product.id_product_attribute || 0}`);

        if (bulkHousingProducts.some(item => String(item.row_key) === rowKey)) {
            showFeedback('warning', 'Product already added.');
            return;
        }

        bulkHousingProducts.push({
            row_key: rowKey,
            scan: scan,
            id_product: parseInt(product.id_product, 10),
            id_product_attribute: parseInt(product.id_product_attribute || 0, 10),
            reference: product.reference || '',
            ean13: product.ean13 || '',
            name: product.name || '',
            current_location: product.operational_location || '',
            entity_type: product.entity_type || 'product'
        });

        hideFeedback();
        renderBulkRows();
    }

    function renderBulkRows() {
        const tbody = document.getElementById('bulkHousingRows');
        const emptyRow = document.getElementById('bulkHousingEmptyRow');
        const btn = document.getElementById('bulkSaveHousingBtn');

        if (!tbody) {
            return;
        }

        tbody.querySelectorAll('tr[data-bulk-row="1"]').forEach(row => row.remove());

        if (emptyRow) {
            emptyRow.classList.toggle('d-none', bulkHousingProducts.length > 0);
        }

        bulkHousingProducts.forEach(function (item) {
            const tr = document.createElement('tr');
            tr.setAttribute('data-bulk-row', '1');
            tr.setAttribute('data-bulk-key', item.row_key);
            tr.innerHTML = `
                <td>
                    <input type="hidden" class="form-control form-control-sm" value="${escapeHtml(item.scan)}" readonly style="text-align: center;">
                    <div class="small text-muted">Ref: ${escapeHtml(item.reference || '—')} · EAN: ${escapeHtml(item.ean13 || '—')}</div>
                </td>
                <td>${escapeHtml(item.current_location || '—')}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkRemoveProduct('${escapeJs(item.row_key)}')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (btn) {
            btn.disabled = bulkHousingProducts.length === 0;
        }
    }

    function bulkRemoveProduct(rowKey) {
        bulkHousingProducts = bulkHousingProducts.filter(item => String(item.row_key) !== String(rowKey));
        renderBulkRows();

        const input = document.getElementById('bulk_product_scan');
        if (input) {
            input.focus();
        }
    }

    function bulkClearRows() {
        bulkHousingProducts = [];
        renderBulkRows();

        const input = document.getElementById('bulk_product_scan');
        if (input) {
            input.focus();
        }
    }

    function bulkSaveHousing() {
        const housing = getActiveBulkHousing();

        if (!housing.length) {
            showFeedback('danger', 'No active housing selected.');
            return;
        }

        if (!bulkHousingProducts.length) {
            showFeedback('warning', 'Scan at least one product before saving.');
            const input = document.getElementById('bulk_product_scan');
            if (input) input.focus();
            return;
        }

        $.ajax({
            type: 'POST',
            url: "{{ route('housing.bulkSaveHousing') }}",
            data: {
                _token: "{{ csrf_token() }}",
                housing: housing,
                products: bulkHousingProducts.map(function (item) {
                    return {
                        id_product: parseInt(item.id_product, 10),
                        id_product_attribute: parseInt(item.id_product_attribute || 0, 10)
                    };
                })
            },
            success: function (response) {
                showFeedback('success', response.message || 'Bulk housing saved successfully.');
                bulkClearRows();
                requestData(null, null, housing);
            },
            error: function (xhr) {
                showFeedback('danger', xhr.responseJSON?.message || 'Unable to save bulk housing.');
            }
        });
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeJs(value) {
        return String(value).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
    }
</script>
@endsection
