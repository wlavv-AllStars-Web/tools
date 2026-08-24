@if($asdMissingImageBrands->isNotEmpty())
    <div class="col-lg-4">
        <div class="navbar navbar-light customPanel">
            <div class="panel panel-default" style="display: flow-root;">
                <div class="panel-heading" style="padding: 12px 15px; text-transform: uppercase; font-weight: bold; text-align: center;">
                    NO ASD PHOTOS
                </div>
                <div style="max-height: 180px; overflow-y: auto; padding: 0 10px 10px;">
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