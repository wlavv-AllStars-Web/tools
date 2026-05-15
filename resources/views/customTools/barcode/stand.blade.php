<div style="width: 145mm; height: 85mm; border: 1px solid #ddd;text-align: center;display: grid;padding:12px 8px 12px 9px;">
    @if($data->product->wmdeprecated == 0)
        <div style="width: 100%;height: 50px;"></div>   
    @else
        <div style="width: 100%;height: 20px;"></div>    
    @endif
    <div style="text-align: center; font-weight: bolder;padding:5px 0 0 0;font-size: 35px;margin-bottom: 20px;">{{$data->reference}}</div>
    <img src="{{ config('allstars.services.webtools.base_url') }}/uploads/logistics/barcode/{{str_replace('/', '&&', $data->image_code)}}.png?t={{rand()}}" style="margin: 0 auto; width: 150px;">
    
    @if(is_numeric($data->code))
        <div style="margin: 0 auto;font-weight: bolder;font-size: 25px;"> {{ number_format($data->code, 0, ' ', ' ')}} </div>
    @else
        <div style="width: 100%;height: 50px;"></div>  
    @endif
    
    @if($data->product->wmdeprecated == 1)
        <div style="width: calc( 100% - 50px); margin-left: 20px;height: 2px; background-color: #333;"></div>
        <div style="margin: 20px 20px 0px 20px;font-weight: bolder;font-size: 45px;padding: 10px;color: #555;line-height: 1;">END OF LIFE</div>
    @else
        <div style="width: 100%;height: 50px;"></div>    
    @endif
</div>