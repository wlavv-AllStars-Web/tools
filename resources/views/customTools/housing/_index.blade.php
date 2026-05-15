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

        $.ajax({
            type: 'POST',
            url: "{{ route('housing.requestData') }}",
            data: {
                _token: "{{ csrf_token() }}",
                barcode: value,
                id_product: selectedProductId,
                id_product_attribute: selectedAttributeId
            },
            success: function (response) {
                $('#housingInfo').html(response);
                hideFeedback();
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
</script>
@endsection
