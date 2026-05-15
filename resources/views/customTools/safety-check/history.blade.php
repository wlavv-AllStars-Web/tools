@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-10">

        {{-- HEADER (ONE LINE ACTION BAR) --}}
        <div class="navbar navbar-light customPanel"
             style="margin-top:10px; display:flex; align-items:center; gap:10px; flex-wrap:nowrap;">

            {{-- BACK --}}
            <a href="{{ route('customTools.safety.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-chevron-left"></i>
            </a>

            {{-- FILTER FORM --}}
            <form method="GET"
                  style="display:flex; gap:10px; align-items:center; flex-wrap:nowrap; margin:0;">

                <select name="year" class="form-control" style="width:90px;">
                    <option value="">Ano</option>
                    @for($y = now()->year; $y >= 2023; $y--)
                        <option value="{{ $y }}" @selected((string) request('year') === (string) $y)>{{ $y }}</option>
                    @endfor
                </select>

                <select name="month" class="form-control" style="width:90px;">
                    <option value="">Mês</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @selected((string) request('month') === (string) $m)>{{ $m }}</option>
                    @endfor
                </select>

                <button class="btn btn-primary"><i class="fa-solid fa-filter"></i></button>

                <a href="{{ route('customTools.safety.export', request()->all()) }}" class="btn btn-success"><i class="fa-solid fa-file-export"></i></a>

            </form>

        </div>

        {{-- TABLE --}}
        <div class="navbar navbar-light customPanel" style="margin-top:10px;">

            <div style="max-height:70vh; overflow-y:auto;">

                <table class="table table-striped" style="text-align:center; margin-bottom:0;">

                    <thead style="position:sticky; top:0; background:#fff; z-index:1;">
                        <tr>
                            <th>Equipamento</th>
                            <th>Data</th>
                            <th>Geral</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody>

                    @foreach($checks as $c)

                        {{-- MAIN ROW --}}
                        <tr>
                            <td>{{ $c->equipment }}</td>

                            <td>
                                {{ \Carbon\Carbon::parse($c->created_at)->format('Y-m-d') }}
                            </td>

                            <td>
                                @if($c->estado_geral == 1)
                                    <i class="fa-solid fa-check" style="color:green"></i>
                                @else
                                    <i class="fa-solid fa-xmark" style="color:red"></i>
                                @endif
                            </td>

                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="toggleRow({{ $c->id }})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- DETAILS --}}
                        <tr id="details-{{ $c->id }}" style="display:none; background:#f9f9f9;">
                            <td colspan="4" style="text-align:left; padding:15px;">

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    @foreach($fields as $field => $label)
                                        @php
                                            $applies = !(str_contains((string) $c->equipment, 'plataforma') && in_array($field, ['garfos', 'travao_emergencia', 'buzina'], true));
                                        @endphp

                                        @continue(!$applies)

                                        <div>
                                            @if($c->$field == 1)
                                                <i class="fa-solid fa-check" style="color:green"></i>
                                            @else
                                                <i class="fa-solid fa-xmark" style="color:red"></i>
                                            @endif
                                            <b> {{ $label }}</b>
                                        </div>
                                    @endforeach
                                </div>

                                <hr>

                                <b>Observações:</b><br>
                                {{ $c->observacoes ?? '-' }}

                                <br><br>

                                <small>
                                    User: {{ $c->user_id }} |
                                    Data: {{ \Carbon\Carbon::parse($c->created_at)->format('Y-m-d') }}
                                </small>

                            </td>
                        </tr>

                    @endforeach

                    </tbody>

                </table>

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
