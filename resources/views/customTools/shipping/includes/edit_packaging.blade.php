<table style="margin: 0 auto; width: 100%;">
    <tr>
        <td colspan="7" style="text-align: center;">
            <h2>PACKAGES</h2>
            <div style="margin: 3px;height: 2px;width: 100%;"></div>
        </td>
    </tr>
    <tr>
        <td style="text-align: center;">TYPE</td>
        <td style="text-align: center;">QUANTITY</td>
        <td colspan="4" style="text-align: center;border-bottom: 1px solid #999">DIMENSIONS</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="4"><div style="margin: 4px;height: 2px;width: 100%;"></div></td>
    </tr>
    @foreach($packages AS $package)
        <tr>
            <td style="text-align: center;">
                <input value="{{$package->id}}" style="width: 100px;text-align: center;margin: 0 10px;" type="hidden" name="package[id][]">
                
                <select name="package[type][]" style="width: 100px; text-align: center;">
                    <option @if($package->type == 'box') selected="selected" @endif value="box">Box</option>
                    <option @if($package->type == 'pallet') selected="selected" @endif value="pallet">Pallet</option>
                    <option @if($package->type == 'container_20') selected="selected" @endif value="container_20">Container - 20</option>
                    <option @if($package->type == 'container_40') selected="selected" @endif value="container_40">Container - 40</option>
                </select>            
            </td>
            <td style="text-align: center;"> <input value="{{$package->quantity}}" style="width: 100px;text-align: center;margin: 0 10px;" type="number" name="package[quantity][]"> </td>
            <td style="text-align: center;"> <input value="{{$package->width}}" style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" width " type="number" name="package[width][]"></td>
            <td style="text-align: center;"> <input value="{{$package->height}}" style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" height " type="number" name="package[height][]"></td>
            <td style="text-align: center;"> <input value="{{$package->depth}}" style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" depth " type="number" name="package[depth][]"></td>
            <td style="text-align: center;"> <input value="{{$package->weight}}" step="any" style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" weight ( kg ) " type="number" name="package[weight][]"></td>
            <td style="text-align: center;"> </td>
        </tr>
    @endforeach
    <tr>
        <td colspan="7"><div style="margin: 10px;height: 2px;width: 100%;border-bottom: 1px solid #999;"></div></td>
    </tr>
    <tr>
        <td style="text-align: center;">
            <select name="package[type][]" style="width: 100px; text-align: center;">
                <option value="box">Box</option>
                <option value="pallet">Pallet</option>
                <option value="container_20">Container - 20</option>
                <option value="container_40">Container - 40</option>
            </select>            
        </td>
        <td style="text-align: center;"> <input style="width: 100px;margin: 0 10px;" type="number" name="package[quantity][]"> </td>
        <td style="text-align: center;"> <input style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" width " type="number" name="package[width][]"></td>
        <td style="text-align: center;"> <input style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" height " type="number" name="package[height][]"></td>
        <td style="text-align: center;"> <input style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" depth " type="number" name="package[depth][]"></td>
        <td style="text-align: center;"> <input step="any" style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" weight ( kg ) " type="number" name="package[weight][]"></td>
        <td style="text-align: center;"> <button class="btn btn-success" type="button" onclick="addRow($(this))"> <i class="fa-solid fa-plus"></i> </button> </td>
    </tr>
    
    <tr id="holderClone" style="display: none;"><td></td></tr>
    
    <tr id="toClone" style="display: none;">
        <td style="text-align: center;">
            <select name="package[type][]" style="width: 100px; text-align: center;">
                <option value="box">Box</option>
                <option value="pallet">Pallet</option>
                <option value="container_20">Container - 20</option>
                <option value="container_40">Container - 40</option>
            </select>            
        </td>
        <td style="text-align: center;"> <input style="width: 100px;margin: 0 10px;" type="number" name="package[quantity][]"> </td>
        <td style="text-align: center;"> <input style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" width " type="number" name="package[width][]"></td>
        <td style="text-align: center;"> <input style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" height " type="number" name="package[height][]"></td>
        <td style="text-align: center;"> <input style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" depth " type="number" name="package[depth][]"></td>
        <td style="text-align: center;"> <input step="any" style="width: 100px;text-align: center;margin: 0 10px;" placeholder=" weight ( kg ) " type="number" name="package[weight][]"></td>
        <td style="text-align: center;"> <button class="btn btn-success" type="button" onclick="addRow($(this))"> <i class="fa-solid fa-plus"></i> </button> </td>
    </tr>
</table>