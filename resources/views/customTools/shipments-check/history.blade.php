@extends('layouts.app')
@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="navbar navbar-light customPanel"
             style="margin-top:10px; display:flex; gap:10px; flex-wrap:nowrap;">
            <a href="{{ route('customTools.shipments.index') }}" class="btn btn-primary"> <i class="fa-solid fa-chevron-left"></i> </a>
            <form method="GET" style="display:flex; gap:10px; margin:0;">
                <select name="year" class="form-control" style="width:90px;">
                    <option value="">Ano</option>
                    @for($y = now()->year; $y >= 2023; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
                <select name="month" class="form-control" style="width:90px;">
                    <option value="">Mês</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endfor
                </select>
                <button class="btn btn-primary"><i class="fa-solid fa-filter"></i></button>
                <a href="{{ route('customTools.shipments.export', request()->all()) }}" class="btn btn-success"><i class="fa-solid fa-file-csv"></i></a>
            </form>
        </div>
        <div class="navbar navbar-light customPanel" style="margin-top:10px;">
            <div style="max-height:70vh; overflow-y:auto;">
                @foreach($checks as $date => $day)
                    <div style="background:#eee; padding:10px; font-weight:bold; margin-top:10px;"> {{ $date }} </div>
                    <table class="table table-striped text-center">
                        <tr>
                            <th>Carrier</th>
                            <th>Shipments</th>
                            <th>Checked</th>
                            <th></th>
                        </tr>
                        @foreach($day as $c)
                        <tr>
                            <td>{{ $c->carrier_name }}</td>
                            <td @if($c->has_diff) style="color:red; font-weight:bold;" @endif> {{ $c->shipments }} </td>
                            <td @if($c->has_diff) style="color:red; font-weight:bold;" @endif> {{ $c->qty_checked }} </td>
                            <td> <button class="btn btn-sm btn-secondary" onclick="toggleRow({{ $c->id }})"> <i class="fa-solid fa-eye"></i> </button> </td>
                        </tr>
                        <tr id="details-{{ $c->id }}" style="display:none;">
                            <td colspan="4" style="text-align:left; padding:10px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div><b>Non-Standard:</b> {{ $c->non_standard }}</div>
                                    <div><b>Checked:</b> {{ $c->qty_checked }}</div>
                                </div>
                                <hr>
                                <b>Nota:</b><br>
                                {{ $c->note ?? '-' }}
                                <br><br>
                                <small>User: {{ $c->user_id }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function toggleRow(id){
    let row = document.getElementById('details-' + id);
    row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
}
</script>

@endsection