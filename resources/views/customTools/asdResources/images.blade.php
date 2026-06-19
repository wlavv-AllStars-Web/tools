@extends('layouts.app')

@section('content')

<style>
    .asd-images-wrap {
        padding: 15px;
    }

    .asd-stat-card {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 5px;
        background: #fff;
        padding: 14px 16px;
        height: 100%;
    }

    .asd-stat-value {
        font-size: 26px;
        font-weight: 700;
        line-height: 1;
    }

    .asd-stat-label {
        font-size: 13px;
        color: #6c757d;
        margin-top: 6px;
    }

    .asd-image-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }

    .asd-image-card {
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 5px;
        background: #fff;
        padding: 10px;
        min-height: 150px;
    }

    .asd-image-box {
        width: 125px;
        height: 125px;
        margin: 0 auto 8px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 5px;
        overflow: hidden;
        background: #f8f9fa;
        border: 1px solid rgba(0,0,0,.08);
    }

    .asd-image-box img {
        max-width: 125px;
        max-height: 125px;
        display: block;
    }

    .asd-image-box.missing {
        border: 2px solid #dc3545;
    }

    .asd-reference {
        font-weight: 700;
        font-size: 13px;
        word-break: break-word;
        text-align: center;
    }

    .asd-product-name {
        font-size: 12px;
        color: #6c757d;
        text-align: center;
        margin-top: 4px;
        min-height: 34px;
    }

    .asd-status {
        margin-top: 8px;
        text-align: center;
    }
</style>

<div class="asd-images-wrap">
    <div class="row g-3 mb-3" style="text-align: center;">
        <div class="col-md-4">
            <div class="asd-stat-card">
                <div class="asd-stat-value">{{ $totalReferences }}</div>
                <div class="asd-stat-label">Total references</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="asd-stat-card">
                <div class="asd-stat-value text-success">{{ $totalImages }}</div>
                <div class="asd-stat-label">Total images found</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="asd-stat-card">
                <div class="asd-stat-value text-danger">{{ $totalMissing }}</div>
                <div class="asd-stat-label">References without image</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3 text-muted small">
                Lookup rule: <strong>image_code when filled, otherwise product/variation reference</strong>
            </div>

            @if($rows->isEmpty())
                <div class="text-muted text-center p-4">
                    <i class="fa-solid fa-circle-info fa-2x mb-2"></i>
                    <div>No product references found for this brand.</div>
                </div>
            @else
                <div class="asd-image-grid">
                    @foreach($rows as $row)
                        <div class="asd-image-card">
                            <div class="asd-image-box {{ $row['has_image'] ? '' : 'missing' }}">
                                @if($row['has_image'])
                                    <img src="{{ asset($row['image_path']) }}" alt="{{ $row['reference'] }}">
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fa-solid fa-image fa-2x"></i>
                                        <div class="small mt-1">Missing</div>
                                    </div>
                                @endif
                            </div>
                            <div class="asd-reference"> {{ $row['reference'] }} </div>
                            @if(!empty($row['image_code']))
                                <div class="asd-product-name">
                                    Image code: {{ $row['image_code'] }}
                                </div>
                            @elseif(!empty($row['lookup_value']) && $row['lookup_value'] !== $row['reference'])
                                <div class="asd-product-name">
                                    Image: {{ $row['lookup_value'] }}
                                </div>
                            @elseif((int) $row['id_product_attribute'] > 0)
                                <div class="asd-product-name">
                                    Variation specific image
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
