<div style="text-align:center;">
    <img src="{{ public_path('uploads/logos/asd.png') }}" width="145">
    <h1 style="font-size:20px; text-transform:uppercase; color:#111; text-align:center;">Carrier end of day</h1>
    <div style="font-size:10px; color:#555; text-align:center;">{{ $document->carrier_name }} | {{ $document->document_date->format('Y-m-d') }}</div>
</div>
<table cellpadding="5" cellspacing="0" border="1" width="100%" style="font-size:9px; text-align:center;">
    <tr>
        <td width="33%" align="center"><strong>Carrier</strong><br>{{ $document->carrier_name }}</td>
        <td width="33%" align="center"><strong>Shipments</strong><br>{{ $document->shipments_count }}</td>
        <td width="34%" align="center"><strong>Generated</strong><br>{{ optional($document->generated_at)->format('Y-m-d H:i') }}</td>
    </tr>
</table>
<br>
@include('customTools.carrierEndOfDay.print_table', ['lines' => $document->lines])
<br><br><br>
<table cellpadding="6" cellspacing="0" border="0" width="100%" style="font-size:9px;">
    <tr>
        <td width="55%"></td>
        <td width="45%" align="center" style="border-top:1px solid #000;">Carrier signature</td>
    </tr>
</table>
