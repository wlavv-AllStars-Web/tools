@extends('layouts.app')

@section('content')

<div class="container-fluid mt-3">

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

    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('marketing.homepage_ASD.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-4">

                    <div class="col-lg-5">
                        <h6 class="mb-3">Current image</h6>

                        @if($item->image_path)
                            @php
                                $img = asset($item->image_path);
                            @endphp

                            @if($item->link_url)
                                <a href="{{ $item->link_url }}" target="_blank" rel="noopener" title="{{ $item->title }}">
                                    <img src="{{ $img }}" alt="{{ $item->title ?: 'Homepage image' }}" class="img-fluid rounded border">
                                </a>
                            @else
                                <img src="{{ $img }}" alt="{{ $item->title ?: 'Homepage image' }}" title="{{ $item->title }}" class="img-fluid rounded border">
                            @endif
                        @else
                            <div class="border rounded p-4 text-muted text-center">
                                <i class="fa-solid fa-image fa-2x mb-2"></i>
                                <div>No image configured</div>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-7">
                        <div class="mb-3">
                            <label class="form-label">New image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">
                                Leave empty to keep the current image.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Click link</label>
                            <input type="url" name="link_url" class="form-control" value="{{ old('link_url', $item->link_url) }}" placeholder="https://example.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Title / Alt text</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $item->title) }}" maxlength="255">
                        </div>

                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Save
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>

</div>

@endsection