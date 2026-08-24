@extends('layouts.app')

@section('content')
<style>
    .asd-resources-wrap { padding: 15px; }
    .asd-card {
        border: 1px solid var(--border-soft, rgba(0,0,0,.08));
        border-radius: 5px;
        box-shadow: var(--shadow-soft, 0 8px 24px rgba(0,0,0,.06));
        background: var(--card-bg, #fff);
    }
    .asd-table th, .asd-table td { vertical-align: middle; }
    .asd-resource-icons { display: flex; gap: 6px; flex-wrap: wrap; }
    .asd-resource-icons a, .asd-resource-icons span {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        border: 1px solid rgba(0,0,0,.12);
        text-decoration: none;
    }
    .asd-resource-icons span { opacity: .35; }

.asd-resource-icons i {
    font-size: 16px;
    margin-right: 8px;
    transition: 0.2s;
}

.asd-resource-icons a:hover i {
    transform: scale(1.15);
}

</style>

<div class="container-fluid asd-resources-wrap">
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="card asd-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm asd-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th style="width: 70px;">Status</th>
                            <th style="width: 250px;">Resources</th>
                            <th style="width: 110px;" class="text-center">Missing photos</th>
                            <th>Brand</th>
                            <th style="width: 140px;">Last update</th>
                            <th style="width: 60px;" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($brands as $brand)
                            @php
                                $resource = $resources->get($brand->id_manufacturer);
                            @endphp
                            <tr>
                                <td>{{ $brand->id_manufacturer }}</td>
                                <td>
                                    @if((int) $brand->active === 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="asd-resource-icons">
                                        {{-- Catalog --}}
                                        @if($resource?->catalog_file)
                                            <a href="{{ asset($resource->catalog_file) }}" download title="Catalog">
                                                <i class="fa-solid fa-file-lines text-primary"></i>
                                            </a>
                                        @else
                                            <span title="No catalog">
                                                <i class="fa-solid fa-file-lines text-muted"></i>
                                            </span>
                                        @endif
                                    
                                        {{-- Import CSV --}}
                                        @if($resource?->import_file)
                                            <a href="{{ asset($resource->import_file) }}" download title="Import CSV">
                                                <i class="fa-solid fa-file-csv text-primary"></i>
                                            </a>
                                        @else
                                            <span title="No import CSV">
                                                <i class="fa-solid fa-file-csv text-muted"></i>
                                            </span>
                                        @endif
                                    
                                        {{-- Images ZIP --}}
                                        @if($resource?->images_zip)
                                            <a href="{{ asset($resource->images_zip) }}" download title="600px images ZIP">
                                                <i class="fa-solid fa-images text-primary"></i>
                                            </a>
                                        @else
                                            <span title="No images ZIP">
                                                <i class="fa-solid fa-images text-muted"></i>
                                            </span>
                                        @endif
                                    
                                        {{-- Logos ZIP --}}
                                        @if($resource?->logos_zip)
                                            <a href="{{ asset($resource->logos_zip) }}" download title="Logos ZIP">
                                                <i class="fa-solid fa-icons text-primary"></i>
                                            </a>
                                        @else
                                            <span title="No logos ZIP">
                                                <i class="fa-solid fa-icons text-muted"></i>
                                            </span>
                                        @endif
                                    
                                        {{-- Facebook --}}
                                        @if($resource?->facebook_url)
                                            <a href="{{ $resource->facebook_url }}" target="_blank" rel="noopener" title="Facebook">
                                                <i class="fa-brands fa-facebook text-primary"></i>
                                            </a>
                                        @else
                                            <span title="No Facebook">
                                                <i class="fa-brands fa-facebook text-muted"></i>
                                            </span>
                                        @endif
                                    
                                        {{-- Website --}}
                                        @if($resource?->website_url)
                                            <a href="{{ $resource->website_url }}" target="_blank" rel="noopener" title="Website">
                                                <i class="fa-solid fa-globe text-primary"></i>
                                            </a>
                                        @else
                                            <span title="No website">
                                                <i class="fa-solid fa-globe text-muted"></i>
                                            </span>
                                        @endif
                                    
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $missingImagesCount = (int) ($missingImagesByBrand->get($brand->id_manufacturer, 0));
                                    @endphp
                                    <span class="badge {{ $missingImagesCount > 0 ? 'bg-danger' : 'bg-success' }}">
                                        {{ $missingImagesCount }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $brand->name }}</strong>
                                </td>
                                <td>
                                    {{ optional($resource?->updated_at)->format('Y-m-d') ?: '-' }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('web.tools.resources.asd.edit', $brand->id_manufacturer) }}" class="btn btn-sm btn-outline-primary"> <i class="fa-solid fa-pencil me-1"></i> </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No ASD brands found for shop ID 3.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
