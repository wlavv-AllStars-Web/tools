<table cellpadding="3" cellspacing="0" border="1" style="font-size:8px; text-align:center;">
    <thead>
        <tr style="font-weight:bold; background-color:#1e90ff; color:#ffffff;">
            <th width="11%" align="center">Order ID</th>
            <th width="13%" align="center">Reference</th>
            <th width="15%" align="center">Country</th>
            <th width="9%" align="center">Weight</th>
            <th width="8%" align="center">Width</th>
            <th width="8%" align="center">Length</th>
            <th width="8%" align="center">Depth</th>
            <th width="28%" align="center">Tracking</th>
        </tr>
    </thead>
    <tbody>
        @foreach($lines as $line)
            <tr>
                <td width="11%" align="center">{{ $line->order_id }}</td>
                <td width="13%" align="center">{{ $line->order_reference }}</td>
                <td width="15%" align="center">{{ $line->country }}</td>
                <td width="9%" align="center">{{ $line->weight !== null ? number_format((float) $line->weight, 2, '.', '') : '-' }}</td>
                <td width="8%" align="center">{{ $line->width ?? '-' }}</td>
                <td width="8%" align="center">{{ $line->length ?? '-' }}</td>
                <td width="8%" align="center">{{ $line->depth ?? '-' }}</td>
                <td width="28%" align="center">{{ $line->tracking_number }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
