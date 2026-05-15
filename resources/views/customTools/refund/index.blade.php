@extends('layouts.app')

@section('content')


<div class="navbar navbar-light" id="editFormContainer" style="display: none;margin: 10px 15px;"> </div>

<div class="navbar navbar-light customPanel" id="filterHeader">
    <button class="btn" onclick="openNewRefund()">ADD NEW REFUND</button>
    <div style="float: right;">
        <form method="GET" action="{{ route($routes['index'] ?? 'refund.index') }}" class="row g-3" style="margin-bottom: 0;">
            <div class="col-md-3"> <input type="number" name="year" id="year" class="form-control" value="{{ request('year') ?? date('Y') }}"> </div>
            <div class="col-md-3">
                <select name="month" id="month" class="form-select">
                    <option value="">-- All --</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}> {{ DateTime::createFromFormat('!m', $m)->format('F') }} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="method" id="method" class="form-select">
                    <option value="">-- All --</option>
                    @foreach(['Ingenico', 'Paypal', 'Alma', 'Bank Transfer', 'Other'] as $method)
                        <option value="{{ $method }}" {{ request('method') == $method ? 'selected' : '' }}> {{ $method }} </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary w-100"> <i class="fas fa-filter"></i> Filter </button>
            </div>
        </form>
    </div>
</div>

@include("customTools.refund.includes.add")

<div class="navbar navbar-light customPanel categorList listSelector" style="display: inline-table;">
    <div style="display: flex;">
        <div id="buttonRefundActive"    style="width: 50%; float: left;text-align: center;background-color: dodgerblue;  color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_active_refund()">OPEN REFUNDS</div>
        <div id="buttonRefundArchived"  style="width: 50%; float: left;text-align: center;background-color: transparent; color: #333; padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="select_archived_refund()">ARCHIVED</div>
    </div>
    <div style="width: 100%; height: 10px;"></div>
    <div>
        @include("customTools.refund.includes.list", ['refunds' => $refunds,  'listType' => 'refundActive'   ])
        @include("customTools.refund.includes.list", ['refunds' => $archived, 'listType' => 'refundArchived' ])            
    </div>
</div>

<script>

    function select_active_refund(){
        
        $('.refundActive').css('display', 'block'); 
        $('.refundArchived').css('display', 'none'); 
        
        $('#buttonRefundActive').css('background-color', 'dodgerblue'); 
        $('#buttonRefundArchived').css('background-color', 'transparent');     
    }
    
    function select_archived_refund(){
        
        $('.refundActive').css('display', 'none'); 
        $('.refundArchived').css('display', 'block'); 

        $('#buttonRefundActive').css('background-color', 'transparent'); 
        $('#buttonRefundArchived').css('background-color', 'dodgerblue');          
    }
    
    function openNewRefund(){
        $('#newRefundContainer').toggle();
        $('.refundListContainer').css('display', 'none');
        $('.listSelector').css('display', 'none');
        $('#filterHeader').css('display', 'none');
    }

    function SaveNewRefund(){
        alert('SAVE');
    }
    
    
    function editRefund(id){
        $('#refundListContainer').css('display', 'none');
        $('#filterHeader').css('display', 'none');
        $('#editFormContainer').css('display', 'block');
        
        $('.listSelector').css('display', 'none');


        $.ajax({
            url: "{{ route($routes['edit'] ?? 'refund.editRefund') }}",
            type: 'POST',
            data: { id: id },
            success: function(response) {
                $('#editFormContainer').html(response).show();
            },
            error: function(xhr) {
                alert('Erro ao carregar o formulário.');
                console.error(xhr.responseText);
            }
        });
        
    }
    
    $(document).ready(function() {
        
        $('#order_id').on('blur', function() {
            const orderId = $(this).val().trim();
    
            if (orderId === '') return;
    
            $.ajax({
                url: '{{ route($routes["get_info"] ?? "refund.getInfo") }}',
                method: 'POST',
                data: { id_order: orderId },
                success: function(response) {
                    $('input[name="purchase_date"]').val(response.purchase_date);
                    $('input[name="country"]').val(response.country);
                    $('input[name="product_reference"]').val(response.references);
                    $('#order_total').val(response.total);
                    $('#client_name').val(response.name);
                    $('#client_email').val(response.email);
                    $('#lang').val(response.lang);

                    $('input[name="purchase_date"]').val(response.purchase_date);
                    
                    let payment = response.payment;
                    if( payment == 'Virement bancaire'){ payment = 'Bank Transfer' }
                    if( payment == 'Transferencia bancaria'){ payment = 'Bank Transfer' }
                    
                    if( payment == 'PayPal'){ payment = 'Paypal' }

                    if (payment.includes('alma')) payment = 'Alma';   
                    
                    $('select[name="refund_payment_method"]').val(payment);

                },
                error: function(xhr) {
                    console.error('Error loading order info:', xhr.responseText);
                    alert('Unable to fetch order information.');
                }
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    
        $('#refundForm').on('submit', function(e) {
            e.preventDefault();
    
            const form = $(this);
            const data = form.serialize();
    
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: data,
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('There was an error submitting the refund.');
                    console.error(xhr.responseText);
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.refund-row').forEach(row => {
            row.addEventListener('click', function () {
                const targetId = this.dataset.toggle;
                const allDetails = document.querySelectorAll('.refund-details-row');
    
                // Esconde todas as linhas de detalhe
                allDetails.forEach(detail => {
                    detail.classList.add('d-none');
                });
    
                const detailsRow = document.getElementById(targetId);
    
                // Se a linha de detalhe estava escondida, mostrar
                if (detailsRow.classList.contains('d-none')) {
                    detailsRow.classList.remove('d-none');
                } else {
                    // Caso já estivesse visível, fecha de novo (toggle)
                    detailsRow.classList.add('d-none');
                }
            });
        });
    });

    function copyEmail() {
        const emailSpan = document.getElementById("emailText");
        const email = emailSpan.innerText.trim();
    
        navigator.clipboard.writeText(email).then(() => {
            emailSpan.style.color = "green";
    
            setTimeout(() => {
                emailSpan.style.color = "blue";
            }, 2000);
        });
    }

</script>
<style>
    .form-label{ margin-bottom: 0; }
    .card-body > .col-md-4{ margin-top: 5px; }
    .card-body{ padding: 0.5rem; }
    
    .refund-details-row td > *{ text-align: center; }
    
    .refundArchived{ display: none; }
</style>
@endsection
