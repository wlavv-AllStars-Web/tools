@if(empty($rows))
<div class="alert alert-success">No missing quantities for this brand.</div>
@else
<table class="table table-sm table-striped"><thead><tr><th>Reference</th><th>Sold 12mo</th><th>Target</th><th>Shared stock</th><th>Arriving</th><th>Draft</th><th>To order</th></tr></thead><tbody>
@foreach($rows as $row)<tr><td>{{ $row->sku->reference }}</td><td>{{ $row->sold }}</td><td>{{ $row->target }}</td><td>{{ $row->physical }}</td><td>{{ $row->arrive }}</td><td>{{ $row->draft }}</td><td><strong>{{ $row->needed }}</strong></td></tr>@endforeach
</tbody></table>
@endif
