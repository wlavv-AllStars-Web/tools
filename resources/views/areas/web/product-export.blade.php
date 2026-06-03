@extends('layouts.app')

@section('content')

    <style>
        .product-export-panel { margin: 10px 0; padding: 20px; width: 100%; }
        .product-export-toolbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 16px; width: 100%; }
        .product-export-toolbar-left { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .product-export-toolbar-right { margin-left: auto; }
        .product-export-toolbar form { margin: 0; }
        .product-export-counters { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px; margin-bottom: 16px; }
        .product-export-counter { background: #fff; border: 1px solid #ddd; padding: 14px 16px; }
        .product-export-counter span { display: block; color: #666; font-size: 12px; text-transform: uppercase; }
        .product-export-counter strong { display: block; margin-top: 4px; font-size: 24px; }
        .product-export-table-wrap { max-height: 70vh; overflow: auto; border: 1px solid #ddd; background: #fff; }
        .product-export-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .product-export-table th, .product-export-table td { border-bottom: 1px solid #eee; padding: 8px 10px; white-space: nowrap; text-align: center; }
        table.dataTable.product-export-table thead th, table.dataTable.product-export-table tbody td { text-align: center; }
        .product-export-table th { position: sticky; top: 0; background: #f8f9fa; z-index: 1; }
        .product-export-table.dataTable { width: 100% !important; }
        .product-export-attribute-id { color: #0d6efd; font-weight: 600; }
        .product-export-status-icon { font-size: 17px; }
        .product-export-status-active { color: #198754; }
        .product-export-status-inactive { color: #dc3545; }
        .product-export-visibility { display: inline-block; min-width: 70px; padding: 3px 8px; border-radius: 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
        .product-export-visibility-both { color: #0f5132; background: #d1e7dd; }
        .product-export-visibility-catalog { color: #055160; background: #cff4fc; }
        .product-export-visibility-search { color: #664d03; background: #fff3cd; }
        .product-export-visibility-none { color: #842029; background: #f8d7da; }
    </style>

    @if(session('success'))
        <div class="alert alert-success" style="width: 100%;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger" style="width: 100%;">{{ session('error') }}</div>
    @endif

    <div class="navbar navbar-light customPanel product-export-panel">
        <div style="width: 100%;">
            <div class="product-export-counters">
                <div class="product-export-counter">
                    <span>{{ __('messages.product_export_counter_unique_products') }}</span>
                    <strong>{{ number_format($counters['unique_references'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="product-export-counter">
                    <span>{{ __('messages.product_export_counter_total_products') }}</span>
                    <strong>{{ number_format($counters['total_products'] ?? 0, 0, ',', '.') }}</strong>
                </div>
                <div class="product-export-counter">
                    <span>{{ __('messages.product_export_counter_stock_value_eur') }}</span>
                    <strong>{{ number_format($counters['stock_value_eur'] ?? 0, 2, ',', '.') }} €</strong>
                </div>
            </div>

            <div class="product-export-toolbar">
                <div class="product-export-toolbar-left">
                    <form method="GET" action="{{ route('web.product_export.index') }}">
                        <select name="file" class="form-control" onchange="this.form.submit()">
                            @forelse($files as $file)
                                <option value="{{ $file['filename'] }}" @selected($file['filename'] === $selectedFile)>
                                    {{ $file['filename'] }} - {{ date('Y-m-d H:i', $file['modified_at']) }}
                                </option>
                            @empty
                                <option>{{ __('messages.product_export_no_files') }}</option>
                            @endforelse
                        </select>
                    </form>

                    @if($selectedFile)
                        <a class="btn btn-success" href="{{ route('web.product_export.download', $selectedFile) }}">
                            <i class="fa-solid fa-download"></i> {{ __('messages.product_export_download_csv') }}
                        </a>
                    @endif
                </div>

                <div class="product-export-toolbar-right">
                    <form method="POST" action="{{ route('web.product_export.generate') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-rotate"></i> {{ __('messages.product_export_generate_now') }}
                        </button>
                    </form>
                </div>
            </div>

            @if($selectedFile)
                <div class="product-export-table-wrap">
                    <table class="product-export-table" id="productExportTable">
                        <thead>
                            <tr>
                                @foreach($headers as $header)
                                    @if($header !== 'id_product_attribute')
                                        <th>{{ __('messages.product_export_column_' . $header) }}</th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                <tr>
                                    @foreach($headers as $header)
                                        @if($header === 'id_product')
                                            <td>
                                                {{ $row['id_product'] ?? '' }}
                                                @if(! empty($row['id_product_attribute']))
                                                    <span class="product-export-attribute-id">({{ $row['id_product_attribute'] }})</span>
                                                @endif
                                            </td>
                                        @elseif($header === 'active')
                                            <td>
                                                @if((int) ($row[$header] ?? 0) === 1)
                                                    <i class="fa-solid fa-circle-check product-export-status-icon product-export-status-active" title="{{ __('messages.product_export_active') }}"></i>
                                                @else
                                                    <i class="fa-solid fa-circle-xmark product-export-status-icon product-export-status-inactive" title="{{ __('messages.product_export_inactive') }}"></i>
                                                @endif
                                            </td>
                                        @elseif($header === 'visibility')
                                            @php
                                                $visibility = (string) ($row[$header] ?? '');
                                                $visibilityClass = in_array($visibility, ['both', 'catalog', 'search', 'none'], true) ? $visibility : 'none';
                                            @endphp
                                            <td>
                                                <span class="product-export-visibility product-export-visibility-{{ $visibilityClass }}">
                                                    {{ $visibility !== '' ? $visibility : '-' }}
                                                </span>
                                            </td>
                                        @elseif($header !== 'id_product_attribute')
                                            <td>{{ $row[$header] ?? '' }}</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('productExportTable');

            if (!table || typeof $ === 'undefined' || !$.fn.DataTable) {
                return;
            }

            $('#productExportTable').DataTable({
                pageLength: 50,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, '{{ __('messages.product_export_all') }}']],
                order: [[0, 'asc']],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-PT.json'
                }
            });
        });
    </script>

@endsection
