@if($imageReviewManufacturers->isNotEmpty())
    <div class="nt-quick-select-action">
        <form action="{{ route('design.tools.product_images.index') }}" method="GET">
            <label for="studioImageManufacturer">ASM - Product Image Review</label>
            <select id="studioImageManufacturer" name="manufacturer_id" class="form-control" onchange="if (this.value) this.form.submit()">
                <option value="">{{ __('messages.product_image_review_select_brand') }}</option>
                @foreach($imageReviewManufacturers as $manufacturer)
                    <option value="{{ $manufacturer->id_manufacturer }}">{{ $manufacturer->name }}</option>
                @endforeach
            </select>
        </form>
    </div>
@endif
