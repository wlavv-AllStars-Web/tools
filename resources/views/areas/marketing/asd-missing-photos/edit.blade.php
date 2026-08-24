@extends('layouts.app')

@section('content')
<style>
    .studio-asd-photos { max-width: 760px; margin: 15px auto; }
    .studio-asd-photos-card { background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 22px; }
    .studio-asd-photos-brand { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 22px; padding-bottom: 16px; border-bottom: 1px solid #eee; }
</style>

<div class="studio-asd-photos">
    <div class="studio-asd-photos-card">
        <div class="studio-asd-photos-brand">
            <div>
                <h4 style="margin: 0;">{{ $brand->name }}</h4>
                <small class="text-muted">ASD · manufacturer #{{ $brand->id_manufacturer }}</small>
            </div>
            <a href="{{ route('web.tools.resources.asd.images', ['id_manufacturer' => $brand->id_manufacturer, 'filter' => 'missing']) }}" class="btn btn-outline-success">
                <i class="fa-solid fa-images"></i> IMAGES
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('marketing.asd_missing_photos.images.upload', $brand->id_manufacturer) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="pictures_selected_count" id="pictures_selected_count" value="0">
            <div class="mb-3">
                <label for="pictures" class="form-label">Product pictures 600x600</label>
                <input type="file" name="pictures[]" id="pictures" class="form-control" accept="image/jpeg,image/png,image/webp" multiple required data-max-files="{{ (int) ini_get('max_file_uploads') }}">
                <div class="form-text">Use the product reference as file name. JPG, PNG and WEBP up to 20 MB each.</div>
                <div class="text-danger small mt-2 d-none" id="pictures_count_error"></div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i> Upload photos</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('pictures');
    const count = document.getElementById('pictures_selected_count');
    const error = document.getElementById('pictures_count_error');
    if (!input || !count || !error) return;
    input.addEventListener('change', function () {
        const selected = input.files ? input.files.length : 0;
        const maximum = parseInt(input.dataset.maxFiles, 10) || 20;
        count.value = selected;
        error.classList.toggle('d-none', selected <= maximum);
        error.textContent = selected > maximum ? 'Select at most ' + maximum + ' images at a time.' : '';
    });
});
</script>
@endpush