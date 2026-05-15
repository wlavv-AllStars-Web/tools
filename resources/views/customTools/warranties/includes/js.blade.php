<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    
    function openNew(){ location.href = "{{route('warranties.index', ['type' => 1])}}"; }
    function openInProgress(){ location.href = "{{route('warranties.index', ['type' => 2])}}"; }
    function openClosed(){ location.href = "{{route('warranties.index', ['type' => 3])}}"; }

    $(document).ready(function() {
      $('#ordersTable').DataTable({
        pageLength: 25,
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json'
        },
        order: [[0, 'desc']],
      });
    });

    $(document).on("click", ".open-ajax-modal", function () {
        
        let route = $(this).data("route");
        
        $("#ajaxModalLabel").text("Carregando...");
        $("#ajaxModalLoader").removeClass("d-none");
        $("#ajaxModalContent").addClass("d-none").html("");
    
        $('#ajaxModal').modal('show')    
        
        $.ajax({
            url: route,
            type: "GET",
            dataType: "json",
            success: function(response) {
                $("#ajaxModalLabel").text(response.title ?? "Detalhes");
                $("#ajaxModalContent").html(response.html);
                $("#ajaxModalLoader").addClass("d-none");
                $("#ajaxModalContent").removeClass("d-none");
            },
            error: function() {
                $("#ajaxModalContent").html("<p class='text-danger'>Erro ao carregar conteúdo.</p>");
                $("#ajaxModalLoader").addClass("d-none");
                $("#ajaxModalContent").removeClass("d-none");
            }
        });
    });
    
    /**
    $(document).on("click", ".preview-item", function () {
        let type = $(this).data("type");
        let url = $(this).data("url");
        $("#imagePreviewContent").html('<img src="'+url+'" class="img-fluid rounded">');
        $('#imagePreviewModal').modal('show')    
    });

    $(document).on("click", "#imagePreviewModal", function () {
        $('#imagePreviewModal').modal('hide');    
    });
    **/
        
    $(document).on("click", ".preview-item", function () {
    
        let url = $(this).data("url");
        let isVideo = url.match(/\.(mp4|mov|webm)$/i);
    
        if (isVideo) {
            $("#imagePreviewContent").html(
                '<video src="' + url + '" controls autoplay class="img-fluid rounded" style="max-height: 800px;"></video>'
            );
        } else {
            $("#imagePreviewContent").html(
                '<img src="' + url + '" class="img-fluid rounded" style="max-height: 800px;">'
            );
        }
    
        $('#imagePreviewModal').modal('show');
    });
    
    $(document).on("click", "#imagePreviewModal", function () {
        $('#imagePreviewModal').modal('hide');
    });
    
    $('#imagePreviewModal').on('hidden.bs.modal', function () {
        $("#imagePreviewContent").html('');
    });

    $(document).on("change", "#warrantyStatusSelect", function(){

        let selected = parseInt($(this).val());
        let id_order_return = $(this).attr('data-id');
        
        let container = $("#contentToReplace");
        let html = '';
        
        container.html(html);
        
        if (selected === 3) {
            
            $('#ajaxModalSimple').modal('show') 
        
            html += '<div>';
                html += '<div style="width: 450px;text-align: center;">';
                    html += '<input type="hidden" name="id_order_return" value="'+id_order_return+'" name="id_order_return">';
                    html += '<input type="hidden" name="warrantyStatusSelect" value="'+selected+'" name="id_order_return">';
                    html += '<label class="form-label fw-bold" style="margin: 0 auto;">Pedido de informação</label> <textarea name="supplier_reply" class="form-control" rows="4" required></textarea>';
                html += '</div>';
                html += '<div style="text-align: center;">';
                    html += '<button class="btn btn-primary mt-3" style="width: 150px;height: 35px;margin: 10px auto !important;">Update</button>';
                html += '</div>';
            html += '</div>';
            
            container.html(html);
            
            $('#ajaxModalSimple').show();
            
        }else if (selected === 4) {


            $('#ajaxModalSimple').modal('show') 

            html += '<div>';
                html += '<div style="text-align: center;margin: 10px;">';
                    html += '<h5 class="fw-bold">Please confirm, warranty will be fulfill from...</h5>';
                html += '</div>';
                html += '<div style="text-align: center;margin: 10px;">';
                    html += '<hr>';
                html += '</div>';
                
                html += '<div style="display: flex;">';
                    html += '<div style="width: 50%;float: right;text-align: center;"> <label class="form-label">Suppliers stock?</label> <br><input type="radio" name="fromOurStock" value="0" checked="checked" style="margin-top: 10px;"> </div>';
                    html += '<div style="width: 50%;float: right;text-align: center;"> <label class="form-label">Our stock?</label> <br><input type="radio" name="fromOurStock" value="1" style="margin-top: 10px;"> </div>';
                html += '</div>';
                
                html += '<div style="text-align: center;margin: 10px;">';
                    html += '<hr>';
                html += '</div>';
                
                html += '<div style="text-align: center;">';
                    html += '<input type="hidden" name="id_order_return" value="'+id_order_return+'" name="id_order_return">';
                    html += '<input type="hidden" name="warrantyStatusSelect" value="'+selected+'" name="id_order_return">';
                    html += '<button class="btn btn-primary mt-3" style="width: 150px;height: 35px;margin: 10px auto !important;">Update</button>';
                html += '</div>';
            html += '</div>';
            
            container.html(html);
            
            $('#ajaxModalSimple').show();
            
        }else if (selected === 5) {
            
            $('#ajaxModalSimple').modal('show') 
        
            html += '<div>';
                html += '<div style="width: 450px;text-align: center;">';
                    html += '<input type="hidden" name="id_order_return" value="'+id_order_return+'" name="id_order_return">';
                    html += '<input type="hidden" name="warrantyStatusSelect" value="'+selected+'" name="id_order_return">';
                    html += '<label class="form-label fw-bold" style="margin: 0 auto;">Resposta do fornecedor</label> <textarea name="response_manufacturer" class="form-control" rows="4" required></textarea>';
                html += '</div>';
                html += '<div style="text-align: center;">';
                    html += '<button class="btn btn-primary mt-3" style="width: 150px;height: 35px;margin: 10px auto !important;">Update</button>';
                html += '</div>';
            html += '</div>';
            
            container.html(html);
            
            $('#ajaxModalSimple').show();
            
        }else{

            Swal.fire({
                title: "Confirmar alteração?",
                text: "Tem a certeza que quer alterar o estado da garantia?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sim, alterar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.formSubmit-' + id_order_return).submit();
                }
            });
        
           
        }

    });

    $(document).on("submit", "#warrantyStatusForm", function(e){
        
        e.preventDefault();
    
        let form = this;
    
        Swal.fire({
            title: "Confirmar alteração?",
            text: "Tem a certeza que quer alterar o estado da garantia?",
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