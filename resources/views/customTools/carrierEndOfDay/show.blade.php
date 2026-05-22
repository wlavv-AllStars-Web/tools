@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    <div style="width:100%; display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
        <h5 style="margin:0;">{{ $document->carrier_name }} - {{ $document->document_date->format('Y-m-d') }}</h5>
        <div style="display:flex; gap:8px;">
            <a class="btn btn-secondary btn-sm" href="{{ route('logistics.tools.carrier_end_of_day.index', ['date' => $document->document_date->format('Y-m-d')]) }}">Back</a>
            <a class="btn btn-success btn-sm" href="{{ route('logistics.tools.carrier_end_of_day.pdf', $document) }}">PDF</a>
            <a class="btn btn-warning btn-sm" href="{{ route('logistics.tools.carrier_end_of_day.print', $document) }}" target="_blank">Print</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success" style="width:100%;">{{ session('success') }}</div> @endif

    @include('customTools.carrierEndOfDay.table', ['lines' => $document->lines, 'tableId' => 'carrierEndOfDayDocumentTable'])
</div>

@push('scripts')
<script>
$(function () {
    $('#carrierEndOfDayDocumentTable').DataTable({
        pageLength: 25,
        order: [[0, 'asc'], [1, 'asc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json'
        }
    });
});
</script>
@endpush
@endsection
