@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    <div class="card-body p-0">

        <table class="table mb-0 align-middle" style="text-align: center;">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>W</th>
                    <th>H</th>
                    <th>D</th>
                    <th>Max Kg</th>
                    <th>Max Pallets</th>
                    <th>Active</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>

                <tr>
                    <form method="POST" action="{{ route(($routePrefix ?? 'erp.oms.logistic_containers') . '.store') }}">
                        @csrf

                        <td style="background-color: #ccc;"><input name="name" class="form-control form-control-sm"></td>

                        <td style="background-color: #ccc;">
                            <select name="type" class="form-select form-select-sm">
                                <option value="box">Box</option>
                                <option value="pallet">Pallet</option>
                                <option value="container">Container</option>
                            </select>
                        </td>

                        <td style="background-color: #ccc;"><input name="width_cm" class="form-control form-control-sm"></td>
                        <td style="background-color: #ccc;"><input name="height_cm" class="form-control form-control-sm"></td>
                        <td style="background-color: #ccc;"><input name="depth_cm" class="form-control form-control-sm"></td>
                        <td style="background-color: #ccc;"><input name="max_weight_kg" class="form-control form-control-sm"></td>
                        <td style="background-color: #ccc;"><input name="max_pallets" class="form-control form-control-sm"></td>

                        <td style="background-color: #ccc;" class="text-center">
                            <input type="checkbox" name="is_active" value="1" checked>
                        </td>

                        <td style="background-color: #ccc;" class="text-end">
                            <button class="btn btn-sm btn-outline-success">
                                <i class="fa fa-plus"></i>
                            </button>
                        </td>
                    </form>
                </tr>

                {{-- 🔹 LIST --}}
                @foreach($containers as $c)

                    {{-- VIEW MODE --}}
                    <tr id="row-{{ $c->id }}">
                        <td>{{ $c->name }}</td>
                        <td>{{ $c->type }}</td>
                        <td>{{ $c->width_cm }}</td>
                        <td>{{ $c->height_cm }}</td>
                        <td>{{ $c->depth_cm }}</td>
                        <td>{{ $c->max_weight_kg }}</td>
                        <td>{{ $c->max_pallets }}</td>

                        <td class="text-center">
                            @if($c->is_active)
                                <span class="badge bg-success">ON</span>
                            @else
                                <span class="badge bg-secondary">OFF</span>
                            @endif
                        </td>

                        <td class="text-end">

                            <button class="btn btn-sm btn-outline-warning"
                                onclick="editRow({{ $c->id }})">
                                <i class="fa fa-pen"></i>
                            </button>

                            <form method="POST" action="{{ route(($routePrefix ?? 'erp.oms.logistic_containers') . '.destroy', $c->id) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>

                    {{-- EDIT MODE --}}
                    <tr id="edit-{{ $c->id }}" style="display:none;" class="table-warning">
                        <form method="POST" action="{{ route(($routePrefix ?? 'erp.oms.logistic_containers') . '.update', $c->id) }}">
                            @csrf @method('PUT')

                            <td><input name="name" value="{{ $c->name }}" class="form-control form-control-sm"></td>

                            <td>
                                <select name="type" class="form-select form-select-sm">
                                    <option value="box" {{ $c->type=='box'?'selected':'' }}>Box</option>
                                    <option value="pallet" {{ $c->type=='pallet'?'selected':'' }}>Pallet</option>
                                    <option value="container" {{ $c->type=='container'?'selected':'' }}>Container</option>
                                </select>
                            </td>

                            <td><input name="width_cm" value="{{ $c->width_cm }}" class="form-control form-control-sm"></td>
                            <td><input name="height_cm" value="{{ $c->height_cm }}" class="form-control form-control-sm"></td>
                            <td><input name="depth_cm" value="{{ $c->depth_cm }}" class="form-control form-control-sm"></td>
                            <td><input name="max_weight_kg" value="{{ $c->max_weight_kg }}" class="form-control form-control-sm"></td>
                            <td><input name="max_pallets" value="{{ $c->max_pallets }}" class="form-control form-control-sm"></td>

                            <td class="text-center">
                                <input type="checkbox" name="is_active" value="1" {{ $c->is_active ? 'checked' : '' }}>
                            </td>

                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="fa fa-save"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    onclick="cancelEdit({{ $c->id }})">
                                    <i class="fa fa-times"></i>
                                </button>
                            </td>
                        </form>
                    </tr>

                @endforeach

            </tbody>
        </table>

    </div>
</div>


<script>
    function editRow(id){
        document.getElementById('row-'+id).style.display = 'none';
        document.getElementById('edit-'+id).style.display = '';
    }
    
    function cancelEdit(id){
        document.getElementById('row-'+id).style.display = '';
        document.getElementById('edit-'+id).style.display = 'none';
    }
</script>
@endsection
