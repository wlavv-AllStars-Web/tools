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
        $('.carrierReturnActive').css('display', 'block'); 
        $('.carrierReturnArchived').css('display', 'none'); 
        $('#buttonReturnActive').css('background-color', 'dodgerblue'); 
        $('#buttonReturnArchived').css('background-color', 'transparent');     
    }
    
    function select_archived_issues(){
        $('.carrierReturnActive').css('display', 'none'); 
        $('.carrierReturnArchived').css('display', 'block'); 

        $('#buttonReturnActive').css('background-color', 'transparent'); 
        $('#buttonReturnArchived').css('background-color', 'dodgerblue');          
    }
    
    function editIssue(id){
        $('.showReturnIssue_' + id ).toggle();
        $('.editReturnIssue_' + id ).css('text-align', 'center').toggle();
    }
    
    function updateIssue(id){

        $.ajax({
            type: 'POST',
            url: "{{ route('carrierReturn.update') }}",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                id_order: $('#edit_id_order_' + id).val(),
                date: $('#edit_date_' + id).val(),
                carrier: $('#edit_carrier_' + id).val(),
                tracking: $('#edit_tracking_' + id).val(),
                issue: $('#edit_issue_' + id).val(),
                notes: $('#edit_notes_' + id).val()
            },
            success: function(response) {
                location.reload();
            }       
        });
        
    }
    
    function archiveIssue(id){
        
        Swal.fire({
            title: "Please confirm status change!",
            showDenyButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: `No`,
        }).then((result) => {

            if (result.isConfirmed) {
       
                $.ajax({
                    type: 'POST',
                    url: "{{ route('carrierReturn.archive') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(response) {
                        location.reload();
                    }       
                });

            } else if (result.isDenied) {
                Swal.fire("Issue not removed!", '', 'info')
            }

        });
        
    }  
</script>