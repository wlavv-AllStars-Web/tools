@extends('layouts.app')

@section('content')

<style>
    .asm-wrap {
        padding: 15px;
    }

    .asm-card {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 5px;
        background: #fff;
    }

    .asm-brand-title {
        font-weight: 700;
    }

    .asm-brand-meta {
        font-size: 12px;
        color: #6c757d;
    }

    .asm-upload-cell {
        width: 100%;
    }

    .asm-image-upload-label {
        display: inline-block;
        cursor: pointer;
        margin: 0;
        position: relative;
    }

    .asm-image-box {
        border-radius: 5px;
        border: 1px solid rgba(0,0,0,.08);
        background: #f8f9fa;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: .15s ease-in-out;
        position: relative;
    }

    .asm-image-box:hover {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,.12);
    }

    .asm-image-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .asm-placeholder {
        text-align: center;
        color: #6c757d;
        font-size: 12px;
    }

    .asm-placeholder i {
        font-size: 22px;
        display: block;
        margin-bottom: 5px;
    }

    .asm-language-badge {
        position: absolute;
        top: 5px;
        left: 5px;
        font-size: 11px;
        border-radius: 5px;
        padding: 2px 6px;
        background: rgba(13,110,253,.92);
        color: #fff;
        font-weight: 700;
        z-index: 2;
    }

    .asm-upload-overlay {
        position: absolute;
        right: 5px;
        bottom: 5px;
        background: rgba(0,0,0,.65);
        color: #fff;
        border-radius: 5px;
        padding: 3px 6px;
        font-size: 11px;
        opacity: 0;
        transition: .15s ease-in-out;
    }

    .asm-image-box:hover .asm-upload-overlay {
        opacity: 1;
    }

    .asm-file-input {
        display: none;
    }


    .asm-language-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 58px;
        padding: 8px 12px;
        border-radius: 5px;
        color: #fff;
        font-weight: 700;
        transition: .15s ease-in-out;
    }

    .asm-language-status.is-present { background: #198754; }
    .asm-language-status.is-missing { background: #dc3545; }
    .asm-language-status:hover { filter: brightness(.92); transform: translateY(-1px); }

    .asm-brand-banners-preview {
        display: none;
        position: absolute;
        top: 50%;
        left: calc(100% + 10px);
        transform: translateY(-50%);
        width: 360px;
        padding: 10px;
        border: 1px solid rgba(0,0,0,.15);
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
        pointer-events: none;
        z-index: 1050;
    }

    .asm-brand-preview-trigger:hover .asm-brand-banners-preview {
        display: block;
    }

    .asm-brand-preview-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        width: 30px;
        height: 30px;
        margin-top: 7px;
        border: 0;
        border-radius: 5px;
        color: #fff;
        background: #0d6efd;
        cursor: help;
    }

    .asm-preview-row + .asm-preview-row {
        margin-top: 8px;
    }

    .asm-preview-language {
        display: block;
        margin-bottom: 3px;
        color: #212529;
        font-size: 11px;
        font-weight: 700;
    }

    .asm-preview-image {
        display: block;
        width: 100%;
        max-height: 150px;
        object-fit: contain;
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 4px;
        background: #f8f9fa;
    }

    .asm-preview-missing {
        padding: 12px;
        border: 1px dashed #dc3545;
        border-radius: 4px;
        color: #dc3545;
        background: #fff5f5;
        font-size: 12px;
        text-align: center;
    }

    .asm-card .table-responsive {
        overflow: visible;
    }

    .asm-youtube-form {
        display: flex;
        gap: 6px;
        min-width: 260px;
    }

    .asm-manufacturer-logo {
        width: 64px;
        height: 42px;
        object-fit: contain;
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 5px;
        background: #fff;
    }
    @media (max-width: 992px) {
        .asm-card .table-responsive {
            overflow-x: auto;
        }

        .asm-brand-banners-preview {
            width: 280px;
        }

        .asm-image-box {
            width: 180px;
            height: 80px;
        }
    }
</style>

<div class="asm-wrap">

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

    <div class="card asm-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 8%;">Logo</th>
                            <th style="width: 12%;">Brand</th>
                            <th style="width: 15%;">Image EN</th>
                            <th style="width: 15%;">Image ES</th>
                            <th style="width: 15%;">Image FR</th>
                            <th style="width: 45%;">YouTube</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $brand)
                            <tr>
                                <td>
                                    <img
                                        src="{{ \App\Services\Prestashop\PrestashopAdminLinkService::storeBaseUrl('ASM') }}/img/m/{{ $brand->id_manufacturer }}.jpg"
                                        alt="{{ $brand->name }}"
                                        class="asm-manufacturer-logo"
                                        loading="lazy"
                                    >
                                </td>

                                <td>
                                    <div class="asm-brand-title">
                                        {{ $brand->name }}
                                    </div>

                                    <div class="asm-brand-meta">
                                        ID: {{ $brand->id_manufacturer }}

                                        @if(!$brand->active)
                                            <span class="badge bg-secondary ms-1">Inactive</span>
                                        @endif
                                    </div>

                                    <span class="asm-brand-preview-trigger" title="Preview EN, ES and FR banners">
                                        <i class="fa-solid fa-eye"></i>

                                        <span class="asm-brand-banners-preview">
                                            @foreach($languages as $previewLang)
                                                @php
                                                    $previewPath = 'uploads/asm/product/' . $brand->id_manufacturer . '_' . $previewLang . '.webp';
                                                    $previewFullPath = public_path($previewPath);
                                                    $previewExists = file_exists($previewFullPath);
                                                @endphp

                                                <span class="asm-preview-row">
                                                    <span class="asm-preview-language">{{ $previewLang }}</span>

                                                    @if($previewExists)
                                                        <img
                                                            src="{{ asset($previewPath) }}?v={{ filemtime($previewFullPath) }}"
                                                            alt="{{ $brand->name }} {{ $previewLang }} banner"
                                                            class="asm-preview-image"
                                                            loading="lazy"
                                                        >
                                                    @else
                                                        <span class="asm-preview-missing">No image</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </span>
                                    </span>
                                </td>

                                @foreach($languages as $lang)
                                    @php
                                        $bannerPath = 'uploads/asm/product/' . $brand->id_manufacturer . '_' . $lang . '.webp';
                                        $bannerFullPath = public_path($bannerPath);
                                        $bannerExists = file_exists($bannerFullPath);
                                        $inputId = 'banner_' . $brand->id_manufacturer . '_' . strtolower($lang);
                                    @endphp

                                    <td>
                                        <form
                                            method="POST"
                                            action="{{ route('marketing.resources.upload', [$brand->id_manufacturer, $lang]) }}"
                                            enctype="multipart/form-data"
                                            class="asm-upload-cell"
                                        >
                                            @csrf

                                            <label
                                                class="asm-image-upload-label"
                                                for="{{ $inputId }}"
                                                title="{{ $bannerExists ? 'Click to replace' : 'Click to upload' }} {{ $lang }} banner"
                                            >
                                                <span class="asm-language-status {{ $bannerExists ? 'is-present' : 'is-missing' }}">
                                                    {{ $lang }}
                                                </span>
                                            </label>

                                            <input
                                                id="{{ $inputId }}"
                                                type="file"
                                                name="banner"
                                                class="asm-file-input"
                                                accept="image/*"
                                                onchange="this.form.submit();"
                                            >
                                        </form>
                                    </td>
                                @endforeach

                                <td>
                                    <form method="POST" action="{{ route('marketing.resources.youtube', $brand->id_manufacturer) }}" class="asm-youtube-form">
                                        @csrf
                                        <input type="text" name="youtube" class="form-control form-control-sm" value="{{ old('youtube', $brand->youtube) }}" placeholder="YouTube URL or code">
                                        <button type="submit" class="btn btn-sm btn-primary" title="Save YouTube">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if($brands->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center text-muted p-4">
                                    No brands found for shop 2.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="text-muted small mt-3">
                Final paths:
                <code>/uploads/asm/product/{id_manufacturer}_EN.webp</code>,
                <code>/uploads/asm/product/{id_manufacturer}_ES.webp</code>,
                <code>/uploads/asm/product/{id_manufacturer}_FR.webp</code>
            </div>
        </div>
    </div>

</div>

@endsection
