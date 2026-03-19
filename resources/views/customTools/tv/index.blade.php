@extends('layouts.app')

@section('content')

@if(session('success'))
    <div class="navbar navbar-light customPanel">
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    </div>
@endif


<div class="navbar navbar-light customPanel">
    <div onclick="$('#newTv').toggle();" style="text-transform: uppercase;cursor:pointer;">Add New TV Item? </div>
    <div id="newTv" style="display: none;">
        <div style="width: 100%; border-top: 1px solid #ddd; margin: 10px 0;padding: 10px 0;">
            <form action="{{ route('tv.store') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                @csrf
            
                <div class="col-md-2">
                    <label for="id_manufacturer" class="form-label">Manufacturer:</label>
                    <select name="id_manufacturer" id="id_manufacturer" required class="form-select">
                        <option value="">Select Manufacturer</option>
                        @foreach($manufacturers as $manufacturer)
                            <option value="{{ $manufacturer->id_manufacturer }}" {{ old('id_manufacturer') == $manufacturer->id_manufacturer ? 'selected' : '' }}>
                                {{ $manufacturer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_manufacturer')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            
                <div class="col-md-3">
                    <label for="src" class="form-label">Upload Image:</label>
                    <input type="file" name="src" id="src" required class="form-control">
                    @error('src')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            
                <div class="col-md-4">
                    <label for="text" class="form-label">Text:</label>
                    <input type="text" name="text" id="text" value="{{ old('text') }}" class="form-control">
                    @error('text')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            
                <div class="col-md-1">
                    <label for="text" class="form-label">Active:</label>
                    <br>
                    <input type="checkbox" name="active" id="active" value="1" class="form-check-input" {{ old('active') ? 'checked' : '' }}>
                    @error('text')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            
                <div class="col-md-1 d-flex align-items-center">
                    <button type="submit" class="btn btn-success mt-4 w-100">Save</button>
                </div>
            
            </form>
        </div>
    </div>
</div>


<div class="navbar navbar-light customPanel">
    @if(count($items) > 0 )
        <table id="dashboardWelcomeTv" class="table table-bordered table-striped text-center">
            <thead class="thead-dark">
                <tr>
                    <th>Image</th>
                    <th>Manufacturer</th>
                    <th>Text</th>
                    <th>Toggle Active</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr data-id="{{ $item->id }}">
                    <td><img src="{{$item->src}}" class="img-thumbnail" style="max-width: 200px;" alt="Image"></td>
                    <td>{{ optional($manufacturers->firstWhere('id_manufacturer', $item->id_manufacturer))->name ?? '--' }}</td>
                    <td class="editable-text" style="cursor: pointer;" data-id="{{ $item->id }}" data-text="{{ $item->text }}">{{ $item->text ? $item->text : '--' }}</td>
                    <td>
                        <form action="{{ route('tv.toggleActive', $item->id) }}" method="POST" class="d-inline">
                            @csrf
                            <div class="form-check form-switch">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="switch-{{ $item->id }}" 
                                    name="active" 
                                    onchange="this.form.submit()" 
                                    {{ $item->active ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="switch-{{ $item->id }}">
                                    {{ $item->active ? 'Active' : 'Inactive' }}
                                </label>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-warning" role="alert" style="margin-bottom: 0; padding: 10px;"> NO ITEMS TO DISPLAY YET</div>
    @endif
</div>


<style>
    #dashboardWelcomeTv tbody td {
        vertical-align: middle;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.editable-text').forEach(cell => {
            cell.addEventListener('click', () => {
                const id = cell.getAttribute('data-id');
                const currentText = cell.getAttribute('data-text') || '';
    
                Swal.fire({
                    title: 'Edit Text',
                    input: 'text',
                    inputValue: currentText,
                    showCancelButton: true,
                    confirmButtonText: 'Save',
                    preConfirm: (newText) => {
                        if (newText.length > 255) {
                            Swal.showValidationMessage('Text cannot exceed 255 characters');
                        }
                        return newText;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const newText = result.value.trim();
    
                        if (newText === currentText) {
                            return; // no change
                        }
    
                        fetch("{{ route('tv.changeText') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ id: id, text: newText })
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network error');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                cell.textContent = newText || '--';
                                cell.setAttribute('data-text', newText);
                                Swal.fire('Saved!', '', 'success');
                            } else {
                                Swal.fire('Error', 'Failed to save text.', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Failed to save text.', 'error');
                        });
                    }
                });
            });
        });
    });

</script>

@endsection