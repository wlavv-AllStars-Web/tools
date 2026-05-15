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
    .asd-current-resource {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 5px;
        padding: 10px 12px;
        margin-bottom: 8px;
    }
    .asd-current-resource .label { font-weight: 600; }
</style>

<div class="asd-resources-wrap">
    <div class="card asd-card" style="padding: 10px;margin-bottom: 15px;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="fa-solid fa-folder-open me-2"></i>
                    {{ $brand->name }}
                </h4>
                <div class="text-muted small">
                    ASD resources for manufacturer #{{ $brand->id_manufacturer }}
                </div>
            </div>
            
            <a href="{{ route('data.resources.images', $brand->id_manufacturer) }}" class="btn btn-outline-success">
                <i class="fa-solid fa-images"></i> IMAGES
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check me-1"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Validation error.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('data.resources.update', $brand->id_manufacturer) }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card asd-card h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Current resources</h5>

                        <div class="asd-current-resource">
                            <div>
                                <div class="label">Catalog</div>
                                <div class="text-muted small">{{ $resource->catalog_file ?: 'Not uploaded' }}</div>
                            </div>
                            @if($resource->catalog_file)
                                <a href="{{ asset($resource->catalog_file) }}" download class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            @endif
                        </div>

                        <div class="asd-current-resource">
                            <div>
                                <div class="label">Import CSV</div>
                                <div class="text-muted small">{{ $resource->import_file ?: 'Not uploaded' }}</div>
                            </div>
                            @if($resource->import_file)
                                <a href="{{ asset($resource->import_file) }}" download class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            @endif
                        </div>

                        <div class="asd-current-resource">
                            <div>
                                <div class="label">600px Images ZIP</div>
                                <div class="text-muted small">{{ $resource->images_zip ?: 'Not generated' }}</div>
                            </div>
                            @if($resource->images_zip)
                                <a href="{{ asset($resource->images_zip) }}" download class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            @endif
                        </div>

                        <div class="asd-current-resource">
                            <div>
                                <div class="label">Logos ZIP</div>
                                <div class="text-muted small">{{ $resource->logos_zip ?: 'Not uploaded' }}</div>
                            </div>
                            @if($resource->logos_zip)
                                <a href="{{ asset($resource->logos_zip) }}" download class="btn btn-sm btn-outline-primary">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card asd-card">
                    <div class="card-body">

                        <h5 class="mb-3">Uploads</h5>

                        <div class="mb-3">
                            <label class="form-label">Catalog file</label>
                            <input type="file" name="catalog_file" class="form-control" accept=".pdf,.xlsx,application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                            <div class="form-text">Allowed: PDF or XLSX. Replaces current catalog.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Import file</label>
                            <input type="file" name="import_file" class="form-control" accept=".csv,text/csv">
                            <div class="form-text">Allowed: CSV. Replaces current import file.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Logos ZIP</label>
                            <input type="file" name="logos_zip" class="form-control" accept=".zip,application/zip">
                            <div class="form-text">Allowed: ZIP. Replaces current logos ZIP.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product pictures 600x600</label>
                            <input type="file" name="pictures[]" class="form-control" accept="image/*" multiple>
                            <div class="form-text">
                                Upload individual product images. File name should be the product reference.
                                The system saves the 600px file, creates a 125x125 thumb and rebuilds the 600px ZIP.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card asd-card">
                    <div class="card-body">
                        <h5 class="mb-3">Brand information</h5>

                        <div class="mb-3">
                            <label class="form-label">Facebook URL</label>
                            <input type="url" name="facebook_url" class="form-control" value="{{ old('facebook_url', $resource->facebook_url) }}" placeholder="https://facebook.com/...">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Website URL</label>
                            <input type="url" name="website_url" class="form-control" value="{{ old('website_url', $resource->website_url) }}" placeholder="https://example.com">
                        </div>

                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Save resources
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
