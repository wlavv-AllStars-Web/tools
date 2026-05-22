<div style="width:100%; overflow-x:auto;">
    <table id="{{ $tableId ?? '' }}" class="table table-bordered customTable text-center" style="min-width:1050px;">
        <thead>
            <tr style="text-transform:uppercase;">
                <th>Order ID</th>
                <th>Reference</th>
                <th>Country</th>
                <th>Weight</th>
                <th>Width</th>
                <th>Length</th>
                <th>Depth</th>
                <th>Tracking</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
                <tr class="carrier-eod-row"
                    data-order="{{ strtolower($line->order_id . ' ' . $line->order_reference) }}"
                    data-country="{{ strtolower((string) $line->country) }}"
                    data-tracking="{{ strtolower((string) $line->tracking_number) }}">
                    <td>{{ $line->order_id }}</td>
                    <td>{{ $line->order_reference }}</td>
                    <td>{{ $line->country }}</td>
                    <td>{{ $line->weight !== null ? number_format((float) $line->weight, 2, '.', '') : '-' }}</td>
                    <td>{{ $line->width ?? '-' }}</td>
                    <td>{{ $line->length ?? '-' }}</td>
                    <td>{{ $line->depth ?? '-' }}</td>
                    <td style="word-break:break-word;">{{ $line->tracking_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
