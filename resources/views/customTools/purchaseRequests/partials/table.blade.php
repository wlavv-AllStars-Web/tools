@if(count($items))
<table class="table table-bordered table-striped datatable" style="text-align: left;">
    <thead>
        <tr>
            <th>Inquiry data</th>
            <th>Customer</th>
            <th>Request</th>
            <th>Reference</th>
            <th>Store</th>
            <th>Lead</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ \Carbon\Carbon::parse($item->first_contact_date)->format('d/m/Y') }}</td>
            <td>{{ $item->customer_contact }}</td>
            <td>{{ $item->request }}</td>
            <td>{{ $item->reference }}</td>
            <td>{{ $item->store }}</td>
            <td>{{ $item->sales_lead }}</td>
            <td> <a href="{{ route('quote.edit', $item) }}" > <i class="fa-solid fa-pencil" style="color: orange; font-size: 20px;"></i> </a> </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p class="text-muted" style="margin-bottom: 0;">No records found.</p>
@endif
