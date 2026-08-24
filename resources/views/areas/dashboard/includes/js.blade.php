<script>

function getSalesHistory(){
    
    let fail = 0;
    let date = $('#date_sales_history').val();
    let brand = $('#brand_sales_history').val();
    
    if(date == ''){
        fail +=1;
        $('#alert_sales_history_date').text('{{ __("messages.Please fill the date")}}');
        $('#alert_sales_history_date').css('display', 'block');
        $('#date_sales_history').css('border', '2px solid red');
    }else{
        $('#alert_sales_history_date').css('display', 'none');
        $('#date_sales_history').css('border', '2px solid green');
    }
    
    if(brand == '0'){
        fail +=1;
        $('#alert_sales_history_brand').text('{{ __("messages.Please select the brand")}}');
        $('#alert_sales_history_brand').css('display', 'block');
        $('#brand_sales_history').css('border', '2px solid red');
    }else{
        $('#alert_sales_history_brand').css('display', 'none');
        $('#brand_sales_history').css('border', '2px solid green');
    }

    if(parseDate(date) == ''){
        fail +=1;
        $('#alert_sales_history_date').text('{{ __("messages.Invalid date")}}');
        $('#alert_sales_history_date_invalid').css('display', 'block');
        $('#date_sales_history').css('border', '2px solid red');
    }else{
        $('#alert_sales_history_date_invalid').css('display', 'none');
        $('#date_sales_history').css('border', '2px solid green');
    }
    
    if(fail > 0){
        Swal.fire('{{ __("messages.Please review the form inputs!")}}', '', 'warning')
    }else{

        $.ajax({
            type: 'POST',
            url: "{{route('dashboard.post')}}",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                action: 'getSalesHistory',
                brand: brand,
                date: date,
            },
            success: function(response) {

                var data = JSON.parse(JSON.stringify(response));
                
                $('#sales_history').replaceWith(data.html);

            }       
        });
    }
}   

function parseDate(str) {
    var m = str.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
    return (m) ? new Date(m[1], m[2]-1, m[3]) : '';
}
   

function requestedProductSendEmail(id) {
    
    Swal.fire({
        title: '{{ __("messages.Send email to customer?")}}',
        text: '{{ __("messages.Please confirm sending the email to the customer.")}}',
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: '{{ __("messages.send")}}'
    }).then((result) => {

        if (result.isConfirmed) {

            let element = $('#send_email_' + id).closest('tr');
            element.remove();
                    
            $.ajax({
                type: 'POST',
                url: "{{route('dashboard.post')}}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: 'requestedProductSendEmail',
                    id: id,
                },
                success: function(response) { }       
            });
        } else {
            Swal.fire({
                title: '{{ __("messages.Cancelled")}}',
                text: '{{ __("messages.E-mail delivery cancelled.")}}',
                icon: "error"
            });

        }
    });
}
   

function requestedProductSendEmailReviewed(id, email) {

    Swal.fire({
        title: '{{ __("messages.Send email to customer?")}}',
        text: '{{ __("messages.Please confirm sending the email to the customer.")}}',
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: '{{ __("messages.send")}}'
    }).then((result) => {

        if (result.isConfirmed) {

            let element = $('#row_exception_' + id).closest('tr');
            element.remove();
                    
            $.ajax({
                type: 'POST',
                url: "{{route('orders.sendReviewedEmail')}}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: 'requestedProductSendEmailReviewed',
                    id: id,
                    email: email,
                },
                success: function(response) { }       
            });
        } else {
            Swal.fire({
                title: '{{ __("messages.Cancelled")}}',
                text: '{{ __("messages.E-mail delivery cancelled.")}}',
                icon: "error"
            });

        }
    });
}

function deleteItem(id, table) {
    
    Swal.fire({
        title: '{{ __("messages.Remove item?")}}',
        text: '{{ __("messages.Please confirm item removal.")}}',
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: '{{ __("messages.remove")}}'
    }).then((result) => {

        if (result.isConfirmed) {

            let element = $('#delete_' + id).closest('tr');
            element.remove();
                    
            $.ajax({
                type: 'POST',
                url: "{{route('dashboard.post')}}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    action: 'removeItem',
                    table: table,
                    id: id,
                },
                success: function(response) { }       
            });
        
        } else {
                    
            Swal.fire({
                title: '{{ __("messages.Cancelled")}}',
                text: '{{ __("messages.Remove action cancelled.")}}',
                icon: "error"
            });
        }
    });
}



function getDailyStats(){

    Swal.fire({
      title: '{{ __("messages.Loading...")}}',
      html: '{{ __("messages.Please wait a moment")}}'
    });
    Swal.showLoading()

    $.ajax({
        type: 'POST',
        url: "{{route('stats.daily_stats')}}",
        dataType: "json",
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {

            Swal.hideLoading();
            Swal.close();
            
            var data = JSON.parse(JSON.stringify(response));
            
            $('#dashboard_content').html(data.html);

        }       
    });
} 

</script>
