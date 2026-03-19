<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    
    function openOrders(){ location.href = "{{route('erp.index', ['list' => 1])}}"; }
    function closedOrders(){ location.href = "{{route('erp.index', ['list' => 2])}}"; }
    function suppliers(){ location.href = "{{route('erp.index', ['list' => 3])}}"; }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
      $('#ordersTable').DataTable({
        pageLength: 25,
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json'
        },
        order: [[0, 'asc']],
      });
    });
    
    function getSupplierOrders(id_supplier, openOrders){

        $.ajax({
            url: "{{route('erp.ordersOfSupplier')}}",
            type: 'POST',
            data: { id_supplier: id_supplier, openOrders: openOrders },
            beforeSend: function () {
                $('#suppliersOrders').html('<p>A carregar…</p>');
            },
            success: function (response) {
                $('#suppliersOrders').html(response);
            },
            error: function () {
                $('#suppliersOrders').html('<p>Erro ao carregar encomendas.</p>');
            }
        });        
        
    }
    
    function openOrder(po_id){

        location.href= "/customTools/erp/get/order/" + po_id;      
        
    }


</script>