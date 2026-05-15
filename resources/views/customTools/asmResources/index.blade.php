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
        display: block;
        cursor: pointer;
        margin: 0;
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

    @media (max-width: 992px) {
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
                            <th style="width: 10%;">Brand</th>
                            <th style="width: 30%;">Image EN</th>
                            <th style="width: 30%;">Image ES</th>
                            <th style="width: 30%;">Image FR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($brands as $brand)
                            <tr>
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

                                            <label class="asm-image-upload-label" for="{{ $inputId }}" title="Click to upload {{ $lang }} banner">
                                                <div class="asm-image-box">
                                                    <span class="asm-language-badge">{{ $lang }}</span>

                                                    @if($bannerExists)
                                                        <img
                                                            src="{{ asset($bannerPath) }}?v={{ filemtime($bannerFullPath) }}"
                                                            alt="{{ $brand->name }} {{ $lang }}"
                                                        >
                                                        <span class="asm-upload-overlay">
                                                            <i class="fa-solid fa-upload me-1"></i>
                                                            Replace
                                                        </span>
                                                    @else
                                                        <div class="asm-placeholder">
                                                            <i class="fa-solid fa-cloud-arrow-up"></i>
                                                            Upload {{ $lang }}
                                                        </div>
                                                    @endif
                                                </div>
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
                            </tr>
                        @endforeach

                        @if($brands->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-muted p-4">
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
