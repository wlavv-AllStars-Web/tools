
    <table class="table table-striped" style="width: 100;">
        <tr>
            <td class="left_column">SUPPLIER</td>
            <td class="right_column">
                <select name="supplier" style="width: 100%;">
                    @foreach($suppliers AS $id_supplier => $supplier)
                        <option value="{{$id_supplier}}">{{$supplier}}</option>
                    @endforeach
                </select>
            </td>
        </tr>
        <tr>
            <td class="left_column">READY DATE</td>
            <td class="right_column"><input style="width: 100%;" type="date" name="ready_date"></td>
        </tr>
        <tr>
            <td class="left_column">INCOTERM</td>
            <td class="right_column">
                <select name="incoterm" style="width: 100%;">
                    <option value=""    >SELECT INCOTERM</option>
                    <option value="CPT">CPT</option>
                    <option value="DAP">DAP</option>
                    <option value="DDP">DDP</option>
                    <option value="EXW">EXW</option>
                    <option value="FCA">FCA</option>
                    <option value="FOB">FOB</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="left_column">INVOICE NUMBER</td>
            <td class="right_column"><input placeholder="Invoice number" style="width: 100%;" type="text" name="invoice_number"></td>
        </tr>
        <tr>
            <td class="left_column">INVOICE VALUE</td>
            <td class="right_column"><input placeholder="€uros" style="width: 100%;" step="any" type="number" name="invoice"></td>
        </tr>
        <tr>
            <td class="left_column">COMMENTS</td>
            <td class="right_column">
                <textarea name="comments" rows="4" style="width: 100%;"></textarea>
            </td>
        </tr>
    </table>
    <button class="btn btn-success" type="submit" style="width: 100%;">SAVE</button>
</form>