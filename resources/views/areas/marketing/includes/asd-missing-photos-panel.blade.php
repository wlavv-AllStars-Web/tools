<div class="nt-quick-select-action">
                <label for="studioAsdImageManufacturer">
                    ASD - Product Image Review
                    @if($asdMissingImageBrands->sum('missing_images_count') > 0)
                        <span class="badge bg-danger text-white" style="vertical-align: middle;">{{ $asdMissingImageBrands->sum('missing_images_count') }} images missing</span>
                    @endif
                </label>
                <select id="studioAsdImageManufacturer" class="form-control" data-url-prefix="{{ url('marketing/asd-missing-photos') }}" onchange="if (this.value) window.location.href = this.dataset.urlPrefix + '/' + this.value">
                    <option value="">Select a brand…</option>
                    @foreach($asdMissingImageBrands as $brand)
                        <option value="{{ $brand->id_manufacturer }}">@if($brand->missing_images_count > 0)&#128308; {{ $brand->missing_images_count }} - @endif{{ $brand->name }}</option>
                    @endforeach
                </select>
</div>
