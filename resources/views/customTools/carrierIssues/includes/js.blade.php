<script>
    
    function showContent(id_issue){
        
        $('.carrierIssueRow').css('display', 'none');
        
        let openInfo = $('#carrierIssue_' + id_issue).prop('open');
        
        console.log(openInfo);
        
        if(openInfo == 'open'){
            $('#carrierIssue_' + id_issue).css('display', 'none');
            $('#carrierIssue_' + id_issue).attr('open', 'close');
            $('#carrierIssue_' + id_issue).prop('open', 'close');
        }else{
            $('#carrierIssue_' + id_issue).css('display', 'contents');
            $('#carrierIssue_' + id_issue).attr('open', 'open');
            $('#carrierIssue_' + id_issue).prop('open', 'open');
        }
        
    }
    
    function select_active_issues(){
        $('.carrierIssuesActive').css('display', 'block'); 
        $('.carrierIssuesArchived').css('display', 'none'); 
        $('.carrierIssuesRetention').css('display', 'none'); 
        $('#buttonIssuesActive').css('background-color', 'dodgerblue'); 
        $('#buttonIssuesArchived').css('background-color', 'transparent');     
        $('#buttonIssuesRetention').css('background-color', 'transparent'); 
    }
    
    function select_archived_issues(){
        $('.carrierIssuesActive').css('display', 'none'); 
        $('.carrierIssuesArchived').css('display', 'block'); 
        $('.carrierIssuesRetention').css('display', 'none'); 
        $('#buttonIssuesActive').css('background-color', 'transparent'); 
        $('#buttonIssuesArchived').css('background-color', 'dodgerblue');          
        $('#buttonIssuesRetention').css('background-color', 'transparent');            
    }
    
    function select_retention_issues(){
        $('.carrierIssuesActive').css('display', 'none'); 
        $('.carrierIssuesArchived').css('display', 'none'); 
        $('.carrierIssuesRetention').css('display', 'block'); 
        $('#buttonIssuesActive').css('background-color', 'transparent'); 
        $('#buttonIssuesArchived').css('background-color', 'transparent');            
        $('#buttonIssuesRetention').css('background-color', 'dodgerblue');            
    }
    
    function editIssue(id_issue){
        
        Dropzone.autoDiscover = false;
        
        $.ajax({
            type: 'POST',
            url: "{{ route('carrierIssues.edit') }}",
            data: {
                _token: "{{ csrf_token() }}",
                id_issue: id_issue  
            },
            success: function(response) {
                var data = JSON.parse(JSON.stringify(response));
                $('#carrierIssue_' + id_issue).replaceWith(data.html);
                
                initializeDropzone();
            }       
        });
        
    }

    function initializeDropzone() {
        
        const dropzoneElement = document.querySelector(".dropzone");
        
        if (dropzoneElement) {
            const dropzone = new Dropzone(dropzoneElement, {
                url: "{{route('uploads.upload')}}",
                paramName: "file",
                maxFilesize: 3,
                success: function (file, response) {
                    console.log("Upload concluído:", response);
                },
                error: function (file, errorMessage) {
                    console.error("Erro no upload:", errorMessage);
                }
            });
            
            dropzone.on("addedfile", function (file) {
                $('#upload_filename').attr('value', file.name);
            });
        }
    }

    function removeIssue(id_issue){
        
        Swal.fire({
            title: "Please confirm carrier issue removal!",
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: `No`,
        }).then((result) => {

            if (result.isConfirmed) {
       
                $.ajax({
                    type: 'POST',
                    url: "{{ route('carrierIssues.destroy') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_issue: id_issue  
                    },
                    success: function(response) {
                        $('#carrierIssueRow_' + id_issue).remove();
                        $('#carrierIssue_' + id_issue).remove();
                    }       
                });

            } else if (result.isDenied) {
                Swal.fire("Issue not removed!", '', 'info')
            }

        });
        
    }  
    
    function archiveClaim(id_issue, status){

        let question = "Please confirm carrier issue archive request!";
        
        if(status == 2){
            question = "Please confirm carrier issue status change to retention!";
        }
        
        Swal.fire({
            title: question,
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: `No`,
        }).then((result) => {

            if (result.isConfirmed) {
                
                $.ajax({
                    type: 'POST',
                    url: "{{ route('carrierIssues.archive') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_issue: id_issue, 
                        status: status
                    },
                    success: function(response) {
                        /**
                        if( response == 'PENDENTE' ){
                           Swal.fire("Issue not archived!", 'Invalid issue status for archive.', 'info') 
                        }else{
                            location.reload();
                        }**/
                        location.reload();
                    }       
                });

            } else if (result.isDenied) {
                Swal.fire("Issue not archived!", '', 'info')
            }

        });
        
    }
    
    function executeFunction(){
        
    }
</script>