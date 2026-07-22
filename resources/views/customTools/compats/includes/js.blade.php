<script>

    function listCompats(){
        $('#compatsList').css('display', '');
        $('.checkProducts').css('display', 'none');
        $('#searchProductsByCompat').css('display', 'none');
    }
    
    function drawMenu(){
        window.location.href = "{{ route('admin.tools.compats.update_menu') }}";
    }

    function clearFrontofficeMenu(){
        const brands = @json($options->pluck('name', 'id_option'));

        Swal.fire({
            title: 'Clear frontoffice menu cache',
            text: 'Select the brand whose frontoffice menu cache should be cleared.',
            input: 'select',
            inputOptions: brands,
            inputPlaceholder: 'Select a brand',
            showCancelButton: true,
            confirmButtonText: 'Continue',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => !value ? 'Please select a brand.' : undefined
        }).then((selection) => {
            if (!selection.isConfirmed) {
                return;
            }

            const brandId = selection.value;
            const brandName = brands[brandId];

            Swal.fire({
                title: 'Confirm cache cleanup?',
                text: 'The frontoffice menu cache for ' + brandName + ' will be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, clear it',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((confirmation) => {
                if (!confirmation.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.tools.compats.clear_frontoffice_menu') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        brand_id: brandId
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Cache cleared',
                            text: response.deleted + ' cache row(s) removed for ' + response.brand + '.',
                            icon: 'success'
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Unable to clear cache',
                            text: (xhr.responseJSON && xhr.responseJSON.message)
                                ? xhr.responseJSON.message
                                : 'An unexpected error occurred.',
                            icon: 'error'
                        });
                    }
                });
            });
        });
    }

    function createOption(){
        
        $('#compatsList').css('display', 'none');
        $('.checkProducts').css('display', 'none');
        $('#searchProductsByCompat').css('display', 'none');
        $('#myModal').modal('toggle');
    }

    function createCompat(){
        
        $('#compatsList').css('display', 'none');
        $('#searchProductsByCompat').css('display', 'block');
        $('.createCompat').css('display', 'none');
        $('.checkProducts').css('display', 'none');
    }
    
    function openModelUpload(element, id){
        
        $('#myModalUpload').modal('toggle');
        $('.dropzoneImage').remove();
        $('.dropzoneImageUpdate').css('display', 'block');
        $('#upload_area').css('display', 'block');
        
        $('#updateImages').attr('element', element);
        $('#updateImages').attr('id_element', id);
        
    }
        
    function removeCompat(id_compat){

        Swal.fire({
            title: 'Are you sure?',
            text: 'This action will permanently delete the item.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: "{{ route('admin.tools.compats.remove_compat') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id_compat,
                    },
                    success: function(response) {
                        location.reload();
                    }       
                });
        
            }
        });
    }

    function uploadUpdatedLogo(){
        
        element = $('#updateImages').attr('element');
        id = $('#updateImages').attr('id_element');
        
        $.ajax({
            type: 'POST',
            url: "{{ route('admin.tools.compats.edit_image') }}",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                element: element
            },
            success: function(response) {
                location.reload();
            }       
        });

    }

    function editCompatItem(id_lang, id_option, name){

        Swal.fire({
            title: "Change '" + name + "' tag",
            text: "Please insert new name!",
            input: 'text',
            showCancelButton: true        
        }).then((result) => {

            if (result.value) {
                $.ajax({
                    type: 'POST',
                    url: '{{route("admin.tools.compats.update_tag")}}',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_lang: id_lang,
                        id_option: id_option,
                        newTag: result.value
                    },
                    success: function(response) {
                        Swal.fire('Tag updated!', '', 'info')
                    }       
                });
            }else{
                Swal.fire("Please insert a new tag!", '', 'warning')
            }
            
        });
    
    }

    function setData(element, type, id_compat){

        $.ajax({
            type: 'POST',
            url: '{{route("admin.tools.compats.set_data")}}',
            data: {
                _token: "{{ csrf_token() }}",
                type: type,
                value: element.val(),
                id_compat: id_compat
            },
            success: function(response) {
                Swal.fire('Value updated!', '', 'info')
            }       
        });
    
    }
    
    function executeFunction(){
        console.log('After upload');
    }

    function saveRelationship(element){
        
        let type = $('#type').val();
        let id_parent = $('#parentID').val();
        let en = $('#name_en').val();
        let es = $('#name_es').val();
        let fr = $('#name_fr').val();


        $.ajax({
            type: 'POST',
            url: "{{ route('admin.tools.compats.save_new_relationship') }}",
            data: {
                _token: "{{ csrf_token() }}",
                type: type,
                id_parent: id_parent,
                en: en, 
                es: es,
                fr: fr
            },
            success: function(response) {
                location.reload();
            }       
        });
        
    }

    function loadParent(element){
        
        $('.dropzoneImageUpdate').remove();
        
        
        let type = element.val();

        if( ( type == 0 ) || ( type == 3) ){
            $('.dropzoneImage').css('display', 'block');
            $('#upload_area').css('display', 'block');
        }else{
            $('.dropzoneImage').css('display', 'none');
            $('#upload_area').css('display', 'none');
        }
        
        if( type == 0){
            $('#parentOptionContainer').replaceWith('<div id="parentOptionContainer"></div>');
            $('.selectParent').css('display', 'none');
        }else{

            $.ajax({
                type: 'POST',
                url: "{{ route('admin.tools.compats.get_options_for_modal') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    type: type
                },
                success: function(response) {
                    $('.selectParent').css('display', 'block');
                    $('#parentOptionContainer').replaceWith(response);
                }       
            });
        }
    }
    
    function callAjaxOptions(type){
        next_type = type + 1;
        $.ajax({
            type: 'POST',
            url: "{{ route('admin.tools.compats.get_options') }}",
            data: {
                _token: "{{ csrf_token() }}",
                type: next_type, 
                id_brand:   $('#option_select_1').val(),
                id_model:   $('#option_select_2').val(),
                id_type:    $('#option_select_3').val(),
                id_version: $('#option_select_4').val(),
                id_option:  $('#option_select_'+type).val(),
                store:      $('#store').val()
            },
            success: function(response) {
                if( next_type > 4){
                    $('.createCompat').css('display', 'block');
                    $('#products_container').replaceWith(response);
                }else{
                    $('#option_' + next_type ).replaceWith(response);
                }
            }       
        });
    }
    
    function create_compatibilities(){
        
        $.ajax({
            type: 'POST',
            url: "{{ route('admin.tools.compats.create_compatibilities') }}",
            data: {
                _token: "{{ csrf_token() }}",
                id_brand:   $('#option_select_1').val(),
                id_model:   $('#option_select_2').val(),
                id_type:    $('#option_select_3').val(),
                id_version: $('#option_select_4').val(),
                id_option:  $('#option_select_4').val(),
                store:      $('#store').val(),
                products:   $('#products').val()
            },
            success: function(response) {
                location.reload();
            }       
        });
    }
</script>
