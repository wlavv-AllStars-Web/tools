<div style="width: 200px; border: 1px solid #ddd;text-align: center;display: grid;padding:12px 8px 12px 9px;">
    <img src="{{ config('allstars.services.webtools.base_url') }}/uploads/logistics/barcode/{{$data->image_code}}.png?t={{rand()}}" style="margin: 15px auto; width: 150px;">
    <div style="margin: 0 auto;font-weight: bolder;font-size: 30px"> {{ $data->code }} </div>
</div>