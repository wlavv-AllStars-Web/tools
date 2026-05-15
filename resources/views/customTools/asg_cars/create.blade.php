@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="card">
        <div class="card-body">

            <h3 class="mb-4">Create Car</h3>

            <form method="POST" action="{{ route('asg_cars.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Name</label>

                    <input type="text"
                           name="name"
                           class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Position</label>

                    <input type="number"
                           name="position"
                           class="form-control"
                           value="0">
                </div>

                <div class="mb-3">
                    <label class="form-label">Display</label>

                    <select name="display" class="form-select">
                        <option value="1">Enabled</option>
                        <option value="0">Disabled</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">
                    Save
                </button>

            </form>

        </div>
    </div>

</div>
@endsection
