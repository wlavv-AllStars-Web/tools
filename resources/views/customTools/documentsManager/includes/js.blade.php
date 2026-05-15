<script>

    //document.getElementById('category').value = '';
    
    function loadDocument(id_document){
        $.ajax({
            type: 'POST',
            url: "{{ route('documentsManager.loadFile') }}",
            data: {
                _token: "{{ csrf_token() }}",
                id_document: id_document     
            },
            success: function(response) {
                $('#loadFile').replaceWith(response);
            }       
        });
    } 

    function listSearch(id_document){
        
        $('.searchRows').css('display', 'none');
        $('.waitWhileSearch').css('display', 'contents').css('background-color', 'coral');
       
        $.ajax({
            type: 'POST',
            url: "{{ route('documentsManager.listSearch') }}",
            data: {
                _token: "{{ csrf_token() }}",
                name: $('#searchName').val(),     
                category: $('#searchCategory').val(),     
                date: $('#searchDate').val(),  
                number: $('#searchNumber').val(),  
                
            },
            success: function(response) {
                $('#searchListContainer').replaceWith(response);
            }       
        });
    } 


    function deleteDocument(id_document){
        
        Swal.fire({
            title: "Please confirm document removal!",
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: `No`,
        }).then((result) => {

            if (result.isConfirmed) {
       
                $.ajax({
                    type: 'POST',
                    url: "{{ route('documentsManager.destroy') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_document: id_document  
                    },
                    success: function(response) {
                        
                        $('#row_' + id_document).remove();
                        
                        $('#loadFile').replaceWith( '<div id="loadFile"> <div class="navbar navbar-light customPanel" style="text-align: left;"> <div class="alert alert-warning" role="alert"> PLEASE SELECT A DOCUMENT</div> </div> </div>' );
                        
                    }       
                });


            } else if (result.isDenied) {
                Swal.fire("File not removed!", '', 'info')
            }

        });
        
    }       

    function calculateValue(){
        
        let totalExVat = $('#totalExVat').val();
        let vatValue =   $('#vatValue').val();
        
        let total = parseFloat(totalExVat) + parseFloat(vatValue);
        
        $('#total').attr('value', total);
    }
    
    function checkExtraFields(){

        let category = $('#category').val();

        if( category == 'carriers'){
            $('#financeBlock').css('display', 'none');
        }else{
            $('#financeBlock').css('display', 'block');
            $('#invoiceBlock').css('display', 'block');
        }
        
        $('.extra_fields').css('display', 'none');
        $('.extra_fields_carriers').css('display', 'none');
        $('#extraField_' + category).css('display', 'flex');
    }
    
    function checkExtraFieldsCarriers(){
        $('#financeBlock').css('display', 'none');
        $('#extraField_carriers_movement').css('display', 'flex');
        $('#invoiceBlock').css('display', 'block');
        
    }
    
    function checkExtraFieldsCarriersMovement(){
        $('#financeBlock').css('display', 'block');
        $('#extraFieldCarriersDocument').css('display', 'flex');
    }
    
    function executeFunction(){
        setTimeout(function () {
            $('#loadFile').replaceWith('<div id="loadFile"><div class="navbar navbar-light customPanel"><embed src="/uploads/documents/upload.pdf?t={{rand()}}" width="850px" height="650px" type="application/pdf" ></div></div>');
            $('#idSaveButton').css('display', 'grid');
        }, 1000)   
    }

    function saveDocument(){
        
        let name = $('#name').val();
        let year = $('#document_year').val();
        let month = $('#document_month').val();
        let day = $('#document_day').val();
        let category = $('#category').val();
        let document_type = document.querySelector('input[name="document_type"]:checked').value;
        let send = 1;

        if( name.length == 0){
            Swal.fire({ title: "Missing document name!", text: "The document name is missing, please check!", icon: "error" }); 
            return;
        }
        
        if( ( year.length == 0 ) || ( month.length  == 0 ) || ( day.length == 0 ) ){
            Swal.fire({ title: "Date missing!", text: "The date is incorrect. Please check!", icon: "error" }); 
            return;
        }
        
        if(document_type == 'manifest'){
            category = 'manifest';
        }else{
            if( category.length == 0 ){
                Swal.fire({ title: "Document category is missing!", text: "The document category is missing, please check!", icon: "error" }); 
                return;
            }
        }
        
        let upload_filename = $("input[name=upload_filename]").val();
        
        if( upload_filename.length == 0 ){
            Swal.fire({ title: "Document upload!", text: "No document uploaded, please verify!", icon: "error" }); 
            return;
        }
    
        $.ajax({
            type: 'POST',
            url: "{{route('documentsManager.store')}}",
            data: {
                _token: "{{ csrf_token() }}",
                name: $('#name').val(),
                number: $('#number').val(),
                totalExVAT: $('#totalExVat').val(),
                vat: $('#vatValue').val(),
                total: $('#total').val(),
                year: $('#document_year').val(),
                month: $('#document_month').val(),
                day: $('#document_day').val(),
                category: category,
                services: $('#services').val(),/** pending column **/
                carriers: $('#carriers').val(),
                supplier: $('#supplier').val(),/** pending column **/
                notes: $('#notes').val(),
                dpd: $('#dpd').val(),
                ups: $('#ups').val(),
                nacex: $('#nacex').val(),
                gls: $('#gls').val(),
                tnt: $('#tnt').val(),
                document_type: document_type,
                upload_filename: upload_filename,
                upload_path: $("input[name=upload_path]").val()
            },
            success: function(response) {
                location.reload();
            }       
        });

    }

    $('.selectedElement').change(function(){ 
        
        var option = $(this).val();
        
        if(option.length > 0){
            window.location.href = option;
        }else{
            Swal.fire({
                title: "OPTION NOT SELECTED!",
                text: "Please select an option!",
                icon: "error"
            }); 
        }
        
    });

    
    function handle(e){
        if(e.keyCode === 13){
            e.preventDefault();
            searchDocuments();
        }
    }
    
    function searchDocuments(){
        
        $.ajax({
            type: 'POST',
            url: "{{route('documentsManager.search')}}",
            data: {
                _token: "{{ csrf_token() }}",
                tag: $('#search_document').val()
            },
            success: function(response) {
                
                $('.categorList').css('display', 'none');
                $('.searchList').replaceWith(response);
                
            }       
        });

    }
</script>