<style>
    .form-label { margin-bottom: 0; }
    .media-grid .col-4 { padding: 4px; }
    input, select, textarea{ text-align: center; }
</style>

@if( $new_files == 1 )

<script>

    Swal.fire({
      title: "NEW FILES AVAILABLE!",
      text: "Please check new files added by the customer.",
      icon: "info"
    });

</script>

@endif

<div class="container-fluid">
    {{--
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold">ORDER</label>
            <div class="d-flex gap-2">
                <input type="text" class="form-control" value="{{ $warranty->id_order }}" readonly>
                <input type="text" class="form-control" value="{{ $warranty->order->reference }}" readonly>
            </div>
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">CUSTOMER</label>
            <div class="d-flex gap-2">
                <input style="width: 80px;" type="text" class="form-control" value="{{ $warranty->customer->id_customer }}" readonly>
                <input style="width: 190px;" type="text" class="form-control" value="{{ $warranty->customer->firstname }} {{ $warranty->customer->lastname }}" readonly>
                <input type="text" class="form-control" value="{{ $warranty->customer->email }}" readonly>
            </div>
        </div>
        
        <div class="col-md-4">
            <label class="form-label fw-semibold">Brand</label>
            <input type="text" class="form-control" value="{{ $detail->brand }}" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Model</label>
            <input type="text" class="form-control" value="{{ $detail->model }}" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">VIN / Serial</label>
            <input type="text" class="form-control" value="{{ $detail->chassis }}" readonly>
        </div>
        
        <div class="col-md-1">
            <label class="form-label fw-semibold">QTY</label>
            <input type="text" class="form-control" value="{{ $detail->product_quantity }}" readonly>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Reference</label>
            <input type="text" class="form-control" value="{{ $detail->orderDetail->product->reference }}" readonly>
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">Product Name</label>
            <input type="text" class="form-control" value="{{ $detail->orderDetail->product_name }}" readonly>
        </div>
        
        <div class="col-md-4">
            <div style="margin-top: 0px;">
                <label class="form-label fw-semibold">Warranty reason</label>
                @if($detail->procedure_id==1)<input type="text" class="form-control" value="Product installation issues" readonly>@endif
                @if($detail->procedure_id==2)<input type="text" class="form-control" value="Product incompatibility" readonly>@endif
                @if($detail->procedure_id==3)<input type="text" class="form-control" value="Defective / Dysfunctional product" readonly>@endif
                @if($detail->procedure_id==4)<input type="text" class="form-control" value="Other" readonly>@endif
            </div>
        </div>
        <div class="col-md-8">
            <label class="form-label fw-semibold">Problem Description</label>
            <textarea class="form-control" rows="4" readonly>{{ $detail->problem_description }}</textarea>
        </div>

    </div>
    --}}

@php
use Illuminate\Support\Str;

$files = collect(json_decode($detail->uploaded_files, true));

$images = (object) [
    'product'    => [],
    'problem'    => [],
    'packing'    => [],
    'additional' => [],
];

foreach ($files as $url) {
    if (Str::contains($url, 'photos_product')) {
        $images->product[] = (object) ['url' => $url];
    } elseif (Str::contains($url, 'photos_problem')) {
        $images->problem[] = (object) ['url' => $url];
    } elseif (Str::contains($url, 'photos_packing')) {
        $images->packing[] = (object) ['url' => $url];
    } else {
        $images->additional[] = (object) ['url' => $url];
    }
}
@endphp

    {{-- ======================= IMAGENS ======================= --}}
    <div class="row g-4" style="text-align: center;">

        {{-- PRODUCT --}}
        <div class="col-12 col-md-3">
            <div style="background-color: #dadada; border: 1px solid #bbb; border-radius: 5px;padding: 5px;">
                <h5 class="fw-semibold">Product</h5>
    
                @if(count($images->product) > 0)
                    <div class="row media-grid" style="margin: 0px;">
                        @foreach ($images->product as $media)
                            <div class="col-4">
                                <div class="ratio ratio-1x1 border rounded overflow-hidden preview-item"
                                    data-url="{{ $media->url }}">
                                    <img src="{{ $media->url }}" class="w-100 h-100 object-fit-cover" style="border: 1px solid #bbb;border-radius: 5px;max-height: 800px;">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">NO FILES AVAILABLE.</p>
                @endif
            </div>
        </div>

        {{-- PACKING --}}
        <div class="col-12 col-md-3">
            <div style="background-color: #dadada; border: 1px solid #bbb; border-radius: 5px;padding: 5px;">
                <h5 class="fw-semibold">Packing</h5>

                @if(count($images->packing) > 0)
                    <div class="row media-grid" style="margin: 0px;">
                        @foreach ($images->packing as $media)
                            <div class="col-4">
                                <div class="ratio ratio-1x1 border rounded overflow-hidden preview-item"
                                    data-url="{{ $media->url }}">
                                    <img src="{{ $media->url }}" class="w-100 h-100 object-fit-cover" style="border: 1px solid #bbb;border-radius: 5px;max-height: 800px;">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">NO FILES AVAILABLE.</p>
                @endif
            </div>
        </div>
        
        {{-- ISSUE --}}
        <div class="col-12 col-md-3">
            <div style="background-color: #dadada; border: 1px solid #bbb; border-radius: 5px;padding: 5px;">
                <div class="row media-grid" style="margin: 0px;">
                    <h5 class="fw-semibold">Issue</h5>
                    @foreach ($images->problem as $media)
                        <div class="col-4">
                            <div class="ratio ratio-1x1 border rounded overflow-hidden preview-item position-relative" data-url="{{ $media->url }}">
                                @php
                                    $isVideo = Str::endsWith($media->url, ['.mp4', '.mov', '.webm']);
                                @endphp
                    
                                @if($isVideo)
                                    <video 
                                        src="{{ $media->url }}" 
                                        class="w-100 h-100 object-fit-cover video-thumb" 
                                        muted preload="metadata" style="border: 1px solid #bbb;border-radius: 5px;">
                                    </video>
                                    <div class="play-overlay"> ▶ </div>
                                @else
                                    <img src="{{ $media->url }}" class="w-100 h-100 object-fit-cover" style="border: 1px solid #bbb;border-radius: 5px;max-height: 800px;">
                                @endif
                    
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-12 col-md-3">
            <div style="background-color: #dadada; border: 1px solid #bbb; border-radius: 5px;padding: 5px;">
                <h5 class="fw-semibold">Extra images</h5>
    
                @if(count($images->additional) > 0)
                    <div class="row media-grid" style="margin: 0px;">
                        @foreach ($images->additional as $media)
                            <div class="col-4">
                                <div class="ratio ratio-1x1 border rounded overflow-hidden preview-item position-relative" data-url="{{ $media->url }}">
                                    @php
                                        $isVideo = Str::endsWith($media->url, ['.mp4', '.mov', '.webm']);
                                    @endphp
                        
                                    @if($isVideo)
                                        <video 
                                            src="{{ $media->url }}" 
                                            class="w-100 h-100 object-fit-cover video-thumb" 
                                            muted preload="metadata" style="border: 1px solid #bbb;border-radius: 5px;">
                                        </video>
                                        <div class="play-overlay"> ▶ </div>
                                    @else
                                        <img src="{{ $media->url }}" class="w-100 h-100 object-fit-cover" style="border: 1px solid #bbb;border-radius: 5px;max-height: 800px;">
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">NO FILES AVAILABLE.</p>
                @endif
                <div style="margin: 5px 0;">
                    <label><b>FILES REQUESTED:</b></label>
                    <div></div>
                    <span> {{$detail->files_required}} </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid text-center">
{{--    
    @if( ( $warranty->state != 3 ) && ( $warranty->state != 8 ) )
    <form id="warrantyStatusForm" method="POST" action="{{ route('warranties.changeStatus') }}">
        @csrf
        
        <input type="hidden" name="id_order_return" value="{{$warranty->id_order_return}}" name="id_order_return">
        <input type="hidden" name="return_type" value="warranty" name="return_type">

        <div class="row">
            <div class="col-md-12">
                <div style="height: 2px; width: 100%; margin: 10px 0;background-color: black;"></div>
            </div>
    
            <div class="col-md-3">
                <div>
                    <label class="form-label fw-semibold">Alterar Estado</label>
                    <select id="warrantyStatusSelect" name="warrantyStatusSelect" class="form-select warranty-status-select" data-id="{{ $warranty->id }}">
                        <option value="">Selecione o estado</option>
                        <option value="1" {{ $warranty->state=='1' ? 'selected' : '' }}>Warranty – Request Registered</option>
                        <option value="2" {{ $warranty->state=='2' ? 'selected' : '' }}>Warranty – Request Being Processed</option>
                        <option value="3" {{ $warranty->state=='3' ? 'selected' : '' }}>Warranty – Request for Additional Information</option>
                        <option value="4" {{ $warranty->state=='4' ? 'selected' : '' }}>Warranty – Approved</option>
                        <option value="5" {{ $warranty->state=='5' ? 'selected' : '' }}>Warranty – Not Approved</option>
                    </select>                
                </div>
            </div>
            <div id="extra-fields" class="col-md-3" style="display: contents;"></div>
        </div>
    </form>
    @endif
    --}}
    <style>
.preview-item {
    position: relative; /* garante que o overlay alinha com estas dimensões */
}

.play-overlay {
    position: absolute;
    top: 60%;
    left: 55%;
    transform: translate(-50%, -50%);
    font-size: 40px;
    color: white;
    text-shadow: 0 0 10px rgba(0,0,0,0.7);
    pointer-events: none;
    z-index: 10;
}

.video-thumb {
    object-fit: cover;
    width: 100%;
    height: 100%;
}

        
    </style>
</div>
