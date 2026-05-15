<script>
    
    function openAll(){ location.href = "{{route('returns.index', ['type' => 0])}}"; }
    function openNew(){ location.href = "{{route('returns.index', ['type' => 1])}}"; }
    function openInProgress(){ location.href = "{{route('returns.index', ['type' => 2])}}"; }
    function openClosed(){ location.href = "{{route('returns.index', ['type' => 3])}}"; }

    $(document).ready(function() {
      $('#ordersTable').DataTable({
        pageLength: 25,
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json'
        },
        order: [[0, 'desc']],
      });
    });
    
    $(document).on("change", "#returnStatusSelect", function(){
    
        let selected = parseInt($(this).val());
        let id_order_return = $(this).attr('data-id');
        
        if(selected == 12){

            $('#ajaxModalSimple').modal('show') 
        
            element = $('#contentToReplace');
            
            let html = '';

            html += '<div id="contentToReplace"></div>';
            html += '<input type="hidden" name="id_order_return" value="'+id_order_return+'" name="id_order_return">';
            html += '<div style="text-align:center;">';
                html += '<div style="width: 100%;margin: 10px 0;">';
                    html += '<label class="form-label fw-bold">Value to pay</label> <input type="number" name="value_to_pay" step="0.1" class="form-control" required></input>';
                html += '</div>';
                html += '<div style="width: 100%;margin: 10px 0;">';
                    html += '<label class="form-label fw-bold">Link for payment</label> <input type="text" name="link_for_payment" class="form-control" required></input>';
                html += '</div>';
                html += '<div style="width: 100%;margin: 10px 0;">';
                    html += '<label class="form-label fw-bold">Link to pictures</label> <input type="text" name="link_to_pictures" class="form-control"></input>';
                html += '</div>';
                html += '<button type="submit" class="btn btn-primary mt-3" style="width: 100%;">Update</button>';
            html += '</div>';
                        
            
            element.html(html);
            
            $('#ajaxModalSimple').show();
        }else{
            $('.formSubmit-' + id_order_return).submit();
        }
        
    });

    $(document).on("submit", "#returnStatusForm", function(e){
        
        e.preventDefault();
    
        let form = this;
    
        Swal.fire({
            title: "Confirmar alteração?",
            text: "Tem a certeza que quer alterar o estado da devolução?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sim, alterar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>