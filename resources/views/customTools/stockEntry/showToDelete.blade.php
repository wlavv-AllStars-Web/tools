@extends('layouts.app')

@section('content')
    <div class="navbar navbar-light customPanel">
        <div style="width: 100%; overflow-x: auto;">
            <table class="table table-striped table-hover" style="width: 100%; margin-bottom: 0;">
                <thead>
                    <tr style="text-align: center;">
                        <th>Reception</th>
                        <th>Order</th>
                        <th>Reference</th>
                        <th>SKU</th>
                        <th>Qty</th>
                        <th>User</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr style="text-align: center; vertical-align: middle;">
                            <td>{{ $entry['oms_reception_id'] }}</td>
                            <td>{{ $entry['po_id'] }}</td>
                            <td>{{ $entry['reference'] }}</td>
                            <td>{{ $entry['sku'] }}</td>
                            <td>{{ $entry['qty'] }}</td>
                            <td>{{ trim(($entry['firstname'] ?? '') . ' ' . ($entry['lastname'] ?? '')) }}</td>
                            <td>
                                <form method="POST" action="{{ route('stockEntry.destroy', $entry['oms_reception_id']) }}" onsubmit="return confirm('Remove this stock entry?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No stock entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
