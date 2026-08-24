@if($asdMissingImageBrands->isNotEmpty())
    @php($missingImagesTotal = $asdMissingImageBrands->sum('missing_images_count'))
    <div class="col-lg-4">
        <div class="navbar navbar-light customPanel">
            <div class="panel panel-default" style="display: flow-root;">
                <div class="panel-heading text-center" style="cursor: pointer; text-transform: uppercase;">
                    <div onclick="$('#studio_asd_missing_photos').toggle()" style="height: 100px; border-radius: 5px; padding: 5px 0; color: white; background-color: red;">
                        <div style="font-size: 35px">{{ $missingImagesTotal }}</div>
                        <div style="font-size: 16px; margin: 0 10px;">NO ASD PHOTOS</div>
                    </div>
                </div>
                <div id="studio_asd_missing_photos" class="panel-body" style="display: none; max-height: 260px; overflow-y: auto; padding: 0 10px 10px;">
                    @foreach($asdMissingImageBrands as $brand)
                        <a href="{{ route('web.tools.resources.asd.edit', ['id_manufacturer' => $brand->id_manufacturer, 'from' => 'studio']) }}" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 5px; border-top: 1px solid #eee; color: #333; text-decoration: none;">
                            <span>{{ $brand->name }}</span>
                            <span class="badge" style="background-color: #d9534f;">{{ $brand->missing_images_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif