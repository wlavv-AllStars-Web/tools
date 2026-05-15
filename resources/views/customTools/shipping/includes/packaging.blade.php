<div style="text-align: center;border-bottom: 1px solid #888;margin-bottom: 40px;">
    <h2>PACKAGING DETAIL</h2>
</div>

        
<table style="margin: 0 auto;">
    <tr>
        <td style="text-align: center;">TYPE</td>
        <td style="text-align: center;">QUANTITY</td>
        <td colspan="4" style="text-align: center;">DIMENSIONS</td>
        <td></td>
    </tr>
    <tr>
        <td style="text-align: center;">
            <select name="package[type][]" style="width: 200px; text-align: center;">
                <option value="box">Box</option>
                <option value="pallet">Pallet</option>
                <option value="container_20">Container - 20</option>
                <option value="container_40">Container - 40</option>
            </select>            
        </td>
        <td style="text-align: center;"> <input style="width: 150px;margin: 0 10px;" type="number" name="package[quantity][]"> </td>
        <td style="text-align: center;"> <input style="width: 150px;text-align: center;margin: 0 10px;" placeholder=" width  ( cm ) " type="number" name="package[width][]"></td>
        <td style="text-align: center;"> <input style="width: 150px;text-align: center;margin: 0 10px;" placeholder=" height ( cm ) " type="number" name="package[height][]"></td>
        <td style="text-align: center;"> <input style="width: 150px;text-align: center;margin: 0 10px;" placeholder=" depth  ( cm ) " type="number" name="package[depth][]"></td>
        <td style="text-align: center;"> <input step="any" style="width: 150px;text-align: center;margin: 0 10px;" placeholder=" weight ( kg ) " type="number" name="package[weight][]"></td>
        <td style="text-align: center;"> <button class="btn btn-success" type="button" onclick="addRow($(this))"> <i class="fa-solid fa-plus"></i> </button> </td>
    </tr>
    
    <tr id="holderClone" style="display: none;"><td></td></tr>
    
    <tr id="toClone" style="display: none;">
        <td style="text-align: center;">
            <select name="package[type][]" style="width: 100%; text-align: center;">
                <option value="box">Box</option>
                <option value="pallet">Pallet</option>
                <option value="container_20">Container - 20</option>
                <option value="container_40">Container - 40</option>
            </select>            
        </td>
        <td style="text-align: center;"> <input style="width: 150px;margin: 0 10px;" type="number" name="package[quantity][]"> </td>
        <td style="text-align: center;"> <input style="width: 150px;text-align: center;margin: 0 10px;" placeholder=" width  ( cm ) " type="number" name="package[width][]"></td>
        <td style="text-align: center;"> <input style="width: 150px;text-align: center;margin: 0 10px;" placeholder=" height ( cm ) " type="number" name="package[height][]"></td>
        <td style="text-align: center;"> <input style="width: 150px;text-align: center;margin: 0 10px;" placeholder=" depth  ( cm ) " type="number" name="package[depth][]"></td>
        <td style="text-align: center;"> <input step="any" style="width: 150px;text-align: center;margin: 0 10px;" placeholder=" weight ( kg ) " type="number" name="package[weight][]"></td>
        <td style="text-align: center;"> <button class="btn btn-success" type="button" onclick="addRow($(this))"> <i class="fa-solid fa-plus"></i> </button> </td>
    </tr>
</table>