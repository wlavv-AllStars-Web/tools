@if($imageReviewManufacturers->isNotEmpty())
    <div class="col-lg-4">
        <div class="navbar navbar-light customPanel">
            <div class="panel panel-default" style="display: flow-root;">
                <div class="panel-heading text-center" style="padding: 15px;">
                    <form action="{{ route('marketing.product_images.index') }}" method="GET" style="margin: 0;">
                        <label for="studioImageManufacturer" style="display: block; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">
                            {{ __('messages.product_image_review') }}
                        </label>
                        <select id="studioImageManufacturer" name="manufacturer_id" class="form-control" onchange="if (this.value) this.form.submit()">
                            <option value="">{{ __('messages.product_image_review_select_brand') }}</option>
                            @foreach($imageReviewManufacturers as $manufacturer)
                                <option value="{{ $manufacturer->id_manufacturer }}">{{ $manufacturer->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif