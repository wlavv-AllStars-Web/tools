<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $document->carrier_name }} - {{ $document->document_date->format('Y-m-d') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; color: #111; margin: 24px; text-align: center; }
        .header { text-align: center; padding-bottom: 12px; margin-bottom: 16px; }
        .logo { width: 145px; height: auto; margin: 0 auto 8px auto; }
        h1 { font-size: 20px; margin: 0 0 4px 0; text-transform: uppercase; }
        .subtitle { color: #555; font-size: 10px; }
        .meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 14px; }
        .meta div { border: 1px solid #ddd; padding: 8px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px; text-align: center; }
        th { background: dodgerblue; color: #fff; text-transform: uppercase; }
        .tracking { word-break: break-word; }
        .signature { width: 45%; margin: 50px 0 0 auto; border-top: 1px solid #000; padding-top: 6px; text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="margin-bottom:10px;">Print</button>
    <div class="header">
        <img class="logo" src="{{ asset('uploads/logos/asd.png') }}" alt="All Stars Distribution">
        <div>
            <h1>Carrier end of day</h1>
            <div class="subtitle">{{ $document->carrier_name }} | {{ $document->document_date->format('Y-m-d') }}</div>
        </div>
    </div>
    <div class="meta">
        <div><strong>Carrier</strong><br>{{ $document->carrier_name }}</div>
        <div><strong>Shipments</strong><br>{{ $document->shipments_count }}</div>
        <div><strong>Generated</strong><br>{{ optional($document->generated_at)->format('Y-m-d H:i') }}</div>
    </div>
    @include('customTools.carrierEndOfDay.print_table', ['lines' => $document->lines])
    <div class="signature">Carrier signature</div>
</body>
</html>
