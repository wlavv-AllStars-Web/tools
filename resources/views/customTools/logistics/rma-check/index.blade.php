@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel rma-check-panel">
        <div class="rma-check-shell">
            <div>
                <h3 class="rma-check-title">Returns and Warranties</h3>
                <div class="rma-check-scan">
                    <p>Por favor faca scan do codigo de barras da nota de devolucao.</p>
                    <input type="text" value="" id="barcodeScan" autocomplete="off" autofocus>
                </div>
            </div>
            <div>
                <div id="ajax_response_container" style="display: none;"></div>
            </div>
        </div>
    </div>

    <style>
        .rma-check-panel{padding:22px 14px;display:flex;justify-content:center;}
        .rma-check-shell{width:100%;max-width:680px;text-align:center;}
        .rma-check-title{margin:0 0 16px 0;font-size:clamp(26px, 7vw, 38px);font-weight:800;color:#222;line-height:1.1;}
        .rma-check-scan{margin:18px auto 8px auto;}
        .rma-check-scan p{margin:0 auto 12px auto;color:#555;font-size:clamp(16px, 4.5vw, 20px);max-width:520px;}
        #barcodeScan{width:100%;max-width:560px;height:58px;border:2px solid #999;border-radius:6px;padding:8px 12px;font-size:26px;text-align:center;font-weight:700;background:#fff;}
        #barcodeScan:focus{border-color:#0d6efd;box-shadow:0 0 0 .2rem rgba(13,110,253,.18);outline:0;}
        .rma-result{width:100%;max-width:620px;margin:20px auto 0 auto;text-align:center;}
        .rma-result-title{font-size:clamp(28px, 8vw, 42px);margin:0 0 14px 0;font-weight:800;line-height:1.1;}
        .rma-result-title.valid{color:darkgreen;}
        .rma-result-title.invalid{color:#b00020;}
        .rma-result-title.warning{color:darkgoldenrod;}
        .rma-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-bottom:12px;}
        .rma-summary-box{border:1px solid #ddd;background:#fff;padding:8px;border-radius:4px;}
        .rma-summary-label{display:block;font-size:11px;text-transform:uppercase;color:#666;}
        .rma-summary-value{display:block;font-size:20px;font-weight:800;color:#222;}
        .rma-detail-table{width:100%;border-collapse:collapse;background:#fff;}
        .rma-detail-table td{border:1px solid #bbb;padding:9px;font-size:15px;vertical-align:middle;}
        .rma-detail-table .td_label{width:42%;background:#666;color:#fff;font-weight:700;}
        .rma-detail-table .td_value{background:#fff;color:#333;font-weight:600;word-break:break-word;}
        .rma-lines{margin-top:12px;}
        @media (max-width: 700px){
            .rma-check-panel{padding:16px 8px;}
            .rma-summary{grid-template-columns:1fr;}
            .rma-detail-table .td_label{width:38%;}
            .rma-detail-table td{font-size:14px;padding:8px 6px;}
        }
    </style>

    <script>
        $(document).ready(function() {
            clearRmaResult();
            $('#barcodeScan').focus();
        });

        function clearRmaResult() {
            $('#ajax_response_container').replaceWith('<div id="ajax_response_container" style="display: none;"></div>');
        }

        $('#barcodeScan').on('change', clearRmaResult);

        $('#barcodeScan').on('keyup', function(event) {
            if (event.keyCode !== 13) {
                return;
            }

            clearRmaResult();

            let returnCode = $('#barcodeScan').val().trim();

            if (returnCode.length < 2) {
                alert('Por favor verifique o codigo de devolucao lido.');
                return;
            }

            $.ajax({
                url: "{{ route('logistics.tools.rma_check.check') }}",
                method: 'POST',
                cache: false,
                data: {
                    _token: "{{ csrf_token() }}",
                    return_code: returnCode
                },
                success: function(response) {
                    if (response.error === 1 && response.error_message) {
                        alert(response.error_message);
                    }

                    $('#ajax_response_container').replaceWith('<div id="ajax_response_container">' + response.html + '</div>');
                    $('#barcodeScan').val('').focus();
                },
                error: function(xhr) {
                    let response = xhr.responseJSON || {};
                    alert(response.error_message || 'Nao foi possivel validar o RMA.');
                    $('#barcodeScan').val('').focus();
                }
            });
        });
    </script>
@endsection
