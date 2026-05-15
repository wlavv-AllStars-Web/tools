<div style="width: 200px; border: 1px solid #ddd;text-align: center;display: grid;">
    <div style="text-align: center; font-weight: bolder;padding:5px 0 0 0;font-size: 14px;">{{$data->reference}}</div>
    <img src="{{ config('allstars.services.webtools.base_url') }}/uploads/logistics/barcode/{{$data->image_code}}.png?t={{rand()}}" style="margin: 0 auto;">
    <div style="margin: 0 auto;font-weight: bolder;"> {{ number_format($data->code, 0, ' ', ' ')}} </div>
    <div style="margin-top:5px;">
        <div style="width: 70px; float: left;text-align: center; font-weight: lighter;padding:0 0 0 0px;font-size: 14px;">{{date('m')}}{{$data->reference}}{{date('d')}}</div>
        <div style="width: 70px; float: right;text-align: right; font-weight: bolder;padding:0 0 0 5px;font-size: 14px;">{{$data->id_order}}</div>
    </div>
</div>
