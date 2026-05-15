<div style="width: 300px; border: 1px solid #ddd;text-align: center;display: grid;padding:12px 8px 12px 9px;">
    <div style="text-align: center; font-weight: bolder;padding:5px 0 0 0;font-size: 14px;">{{$data->reference}}</div>
    <img src="{{ config('allstars.services.webtools.base_url') }}/uploads/logistics/barcode/{{$data->image_code}}.png?t={{rand()}}" style="margin: 2px auto; height: 80px;">
    <div style="margin: 0 auto;font-weight: bolder;"> @if(is_numeric($data->code)) {{ number_format($data->code, 0, ' ', ' ')}} @else {{$data->code}} @endif </div>
    <div style="margin-top:5px;">
        <div style="width: 70px; float: left;text-align: center; font-weight: lighter;padding:0 0 0 0px;font-size: 14px;">{{rand(8,1)}}{{rand(8,1)}}{{date('Y')}}{{rand(8,1)}}{{rand(8,1)}}{{date('m')}}{{rand(8,1)}}{{rand(8,1)}}{{date('d')}}</div>
        <div style="width: 70px; float: right;text-align: right; font-weight: bolder;padding:0 0 0 5px;font-size: 14px;">{{$data->housing}}</div>
    </div>
</div>


