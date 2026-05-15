@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="card mb-4">
        <div class="card-body">

            <h3 class="mb-4">Car Details</h3>

            <div><strong>ID:</strong> {{ $car->id_asg_car }}</div>
            <div><strong>Name:</strong> {{ $car->name ?? '-' }}</div>
            <div><strong>Position:</strong> {{ $car->position ?? 0 }}</div>
            <div><strong>Display:</strong> {{ $car->display ?? 0 }}</div>

        </div>
    </div>

    <div class="card">
        <div class="card-body">

            <h4 class="mb-3">Products</h4>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Product</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id_product }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection
