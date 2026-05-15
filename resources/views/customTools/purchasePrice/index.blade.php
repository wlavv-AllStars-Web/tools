@extends('layouts.app')
@section('content')

    <div class="navbar navbar-light customPanel categorList listSelector">
        <div class="d-flex align-items-center justify-content-between">
            <h1 class="mb-0">Sync by brand</h1>
            <form method="POST" action="{{ route('purchasePrice.update') }}" class="d-inline sync-form">
                @csrf
                <input type="hidden" name="id_manufacturer" value="all">
                <button type="button" class="btn btn-sm btn-primary btn-sync-all" data-name="ALL" data-id="all"> UPDATE ALL </button>
            </form>        
        </div>
        <div class="d-flex align-items-center justify-content-between">
            <div class="text-muted" style="font-size: 0.95rem;">
                Source: <code style="color: red;">ALL STARS MOTORSPORT</code> |
                Target: <code style="color: dodgerblue;">ALL STARS DISTRIBUTION</code>
            </div>      
        </div>
        @if (session('status')) <div class="alert alert-success" style="margin-top: 10px"> {{ session('status') }} </div> @endif
    
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    
        @if (session('result') && is_array(session('result')['not_found_refs'] ?? null) && count(session('result')['not_found_refs']) > 0)
            <div class="alert alert-warning">
                <b>NOT FOUND:</b>
                <div style="max-height: 140px; overflow:auto; margin-top: 6px;">
                    <code>{{ implode(', ', session('result')['not_found_refs']) }}</code>
                </div>
            </div>
        @endif
    </div>
    <div class="navbar navbar-light customPanel categorList listSelector">
        @if($manufacturers->count() > 0)
            <div class="row g-4" style="margin-top: 5px;">
                @foreach($manufacturers as $m)
                    <div class="col-12 col-md-6 col-lg-2" style="margin-bottom: 10px;margin-top: 0;">
                        <div class="border rounded d-flex">
                            <form method="POST" action="{{ route('purchasePrice.update') }}" class="sync-form m-0">
                                @csrf
                                <input type="hidden" name="id_manufacturer" value="{{ $m->id_manufacturer }}">
                                <button type="button" class="btn btn-sm btn-primary btn-sync" data-name="{{ $m->name }}" data-id="{{ $m->id_manufacturer }}"> UPDATE </button>
                            </form>
                            <div style="font-weight:600; padding-left: 5px;"> {{ $m->name }} </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-muted p-4">No manufacturers found!</div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    document.querySelectorAll('.btn-sync').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const form = btn.closest('form');
    
            Swal.fire({
                icon: 'warning',
                title: 'CONFIRM SYNC',
                html: `Will update TARGET's wholesale prices <br>(Original currency) for brand:<br><br><b>${name}</b><br><br>Proceed?`,
                showCancelButton: true,
                confirmButtonText: 'EXECUTE',
                cancelButtonText: 'CANCEL',
                reverseButtons: true,
            }).then((r) => {
                if (r.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Updating…',
                        text: 'Please wait!',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });
                    form.submit();
                }
            });
        });
    });
    
    document.querySelectorAll('.btn-sync-all').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const form = btn.closest('form');
    
            Swal.fire({
                icon: 'warning',
                title: 'CONFIRM SYNC',
                html: `Will update TARGET's wholesale prices <br>(Original currency) for all brands?<br><br>Proceed?`,
                showCancelButton: true,
                confirmButtonText: 'EXECUTE',
                cancelButtonText: 'CANCEL',
                reverseButtons: true,
            }).then((r) => {
                if (r.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Updating…',
                        text: 'Please wait!',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });
                    form.submit();
                }
            });
        });
    });
    </script>
@endsection