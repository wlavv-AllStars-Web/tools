@extends('layouts.app')
@section('content')

<div class="navbar navbar-light customPanel">

    {{-- ACTION BAR --}}
    <div style="display:flex; gap:10px; margin-bottom:10px;">
        <a href="{{ route('customTools.shipments.history') }}"
           class="btn btn-secondary" style="flex:1;">
            Histórico
        </a>
    </div>

    <form method="POST" action="{{ route('customTools.shipments.store') }}">
        @csrf

        <div style="overflow-x:auto;">
            <table class="table table-striped text-center" style="min-width:600px;">
                <thead>
                    <tr>
                        <th title="Carrier"> <i class="fa-solid fa-truck"></i> </th>
                        <th title="Shipments"> <i class="fa-solid fa-box"></i> </th>
                        <th title="Non-Standard"><i class="fa-solid fa-ruler"> </i></th>
                        <th title="Checked"> <i class="fa-solid fa-check"></i> </th>
                        <th title="Note"> <i class="fa-solid fa-note-sticky"></i> </th>
                    </tr>
                </thead>
                <tbody>

                @foreach($shipments as $i => $s)
                <tr>

                    <td>
                        <b>{{ $s->carrier_name }}</b>
                        <input type="hidden" name="carrier[]" value="{{ $s->carrier_name }}">
                    </td>

                    <td>
                        {{ $s->total }}
                        <input type="hidden" name="shipments[]" value="{{ $s->total }}">
                    </td>

                    <td>
                        <input type="number"
                               name="non_standard[]"
                               class="form-control"
                               style="width:80px;margin: 0 auto;"
                               placeholder="0">
                    </td>

                    <td>
                        <input type="number"
                               name="qty_checked[]"
                               class="form-control"
                               style="width:80px;margin: 0 auto;"
                               placeholder="0">
                    </td>

                    <td>
                        <input type="text"
                               name="note[]"
                               class="form-control"
                               placeholder="Nota"
                               style="min-width:150px;">
                    </td>

                </tr>
                @endforeach

                </tbody>

            </table>
        </div>

        <button class="btn btn-primary w-100" style="margin-top:10px;"> <i class="fa-solid fa-floppy-disk"></i> </button>

    </form>

</div>

@endsection