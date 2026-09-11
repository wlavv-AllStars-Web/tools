<div class="col-lg-4">
    <div class="navbar navbar-light customPanel">
        <div class="panel panel-default" style="display: flow-root;">
            <div class="panel-heading text-center" style="padding: 15px;">
                <label for="studioAsdImageManufacturer" style="display: block; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">
                    ASD - Product Image Review
                </label>
                <select id="studioAsdImageManufacturer" class="form-control" data-url-prefix="{{ url('marketing/asd-missing-photos') }}" onchange="if (this.value) window.location.href = this.dataset.urlPrefix + '/' + this.value">
                    <option value="">Select a brand…</option>
                    @foreach($asdMissingImageBrands as $brand)
                        <option value="{{ $brand->id_manufacturer }}">{{ $brand->name }} ({{ $brand->missing_images_count }})</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
