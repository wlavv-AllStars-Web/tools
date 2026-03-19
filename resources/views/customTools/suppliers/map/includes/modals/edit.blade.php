<div class="modal fade" id="myModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">UPDATE INFO</h5>
            </div>
            <form method="POST" action="{{route('suppliersMap.store') }}">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-lg-4" style="text-align: right;font-weight: bolder"><label for="type">SUPPLIER</label></div>
                        <div class="col-lg-8" style="text-align: center;">
                            <select name="id_supplier" style="width: 100%;">
                                @foreach($new_suppliers AS $new_supplier)
                                    <option @if($supplier_map->id_supplier == $new_supplier->id_supplier) selected="selected" @endif value="{{$new_supplier->id_supplier}}">{{$new_supplier->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4" style="text-align: right;font-weight: bolder; margin-top: 10px;"><label for="type">BRAND</label></div>
                        <div class="col-lg-8" style="text-align: center; margin-top: 10px;">
                            <select name="id_manufacturer" style="width: 100%;">
                                <option value="0" @if($supplier_map->id_manufacturer == 0) selected="selected" @endif>UNDEFINED</option>
                                @foreach($new_manufacturers AS $new_manufacturer)
                                    <option @if($supplier_map->id_manufacturer == $new_manufacturer->id_manufacturer) selected="selected" @endif value="{{$new_manufacturer->id_manufacturer}}">{{$new_manufacturer->name}}</option>
                                @endforeach
                            </select>
                        </div>
                            
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;font-weight: bolder"></div>
                        <div class="col-lg-2" style="text-align: center;"><label for="type">Warranty</label>
                            <br>
                            <input name="warranty" type="checkbox" value="1" @if($supplier_map->warranty == 1) checked="checked" @endif>
                        </div>
    
                        <div class="col-lg-2" style="text-align: center;"><label for="type">Description</label>
                            <br>
                            <input name="description" type="checkbox" value="1" @if($supplier_map->description == 1) checked="checked" @endif>
                        </div>
    
                        <div class="col-lg-2" style="text-align: center;"><label for="type">EAN-13</label>
                            <br>
                            <input name="ean13" type="checkbox" value="1" @if($supplier_map->ean13 == 1) checked="checked" @endif>
                        </div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Dealer website</label></div>
                        <div class="col-lg-8"><input name="dealer_website" type="text" value="{{$supplier_map->dealer_website}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Contact</label></div>
                        <div class="col-lg-8"><input name="contact" type="text" value="{{$supplier_map->contact}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Email</label></div>
                        <div class="col-lg-8"><input name="email" type="text" value="{{$supplier_map->email}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Address</label></div>
                        <div class="col-lg-8"><input name="address" type="text" value="{{$supplier_map->address}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Country</label></div>
                        <div class="col-lg-8"><input name="country" type="text" value="{{$supplier_map->country}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Phone</label></div>
                        <div class="col-lg-8"><input name="phone" type="text" value="{{$supplier_map->phone}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Website</label></div>
                        <div class="col-lg-8"><input name="website" type="text" value="{{$supplier_map->website}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        
                        <div class="col-lg-4" style="text-align: right;"><label for="type">B2B link</label></div>
                        <div class="col-lg-8"><input name="b2b_link" type="text" value="{{$supplier_map->b2b_link}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Username</label></div>
                        <div class="col-lg-8"><input name="b2b_username" type="text" value="{{$supplier_map->b2b_username}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Password</label></div>
                        <div class="col-lg-8"><input name="b2b_password" type="text" value="{{$supplier_map->b2b_password}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Pics</label></div>
                        <div class="col-lg-8"><input name="pics" type="text" value="{{$supplier_map->pics}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Catalogue</label></div>
                        <div class="col-lg-8"><input name="catalogue" type="text" value="{{$supplier_map->catalogue}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Instructions</label></div>
                        <div class="col-lg-8"><input name="instructions" type="text" value="{{$supplier_map->instructions}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Inventory</label></div>
                        <div class="col-lg-8"><input name="inventory" type="text" value="{{$supplier_map->inventory}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Discount</label></div>
                        <div class="col-lg-8"><input name="discount" type="text" value="{{$supplier_map->discount}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Terms</label></div>
                        <div class="col-lg-8"><input name="terms" type="text" value="{{$supplier_map->terms}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">IBAN / Account number</label></div>
                        <div class="col-lg-8"><input name="iban" type="text" value="{{$supplier_map->iban}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">BIC / SWIFT</label></div>
                        <div class="col-lg-8"><input name="swift" type="text" value="{{$supplier_map->swift}}" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Incoterm</label></div>
                        <div class="col-lg-8">
                            <select name="incoterm" style="width: 100%;">
                                <option @if($supplier_map->incoterm == 'Credit') selected="selected" @endif value="Credit">Credit</option>
                                <option @if($supplier_map->incoterm == 'CPT') selected="selected" @endif value="CPT">CPT</option>
                                <option @if($supplier_map->incoterm == 'DAP') selected="selected" @endif value="DAP">DAP</option>
                                <option @if($supplier_map->incoterm == 'DDP') selected="selected" @endif value="DDP">DDP</option>
                                <option @if($supplier_map->incoterm == 'DPU') selected="selected" @endif value="DPU">DPU</option>
                                <option @if($supplier_map->incoterm == 'EXW') selected="selected" @endif value="EXW">EXW</option>
                                <option @if($supplier_map->incoterm == 'FCA') selected="selected" @endif value="FCA">FCA</option>
                                <option @if($supplier_map->incoterm == 'FOB') selected="selected" @endif value="FOB">FOB</option>
                                <option @if($supplier_map->incoterm == 'Missing') selected="selected" @endif value="Missing">Missing</option>
                                
                                
                            </select>
                        </div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Currency</label></div>
                        <div class="col-lg-8">
                            <select name="currency" style="width: 100%;">
                                <option @if($supplier_map->currency == 'UNKNOWN') selected="selected" @endif value="UNKNOWN">UNKNOWN</option>
                                <option @if($supplier_map->currency == 'EUR') selected="selected" @endif value="EUR">EUR</option>
                                <option @if($supplier_map->currency == 'GBP') selected="selected" @endif value="GBP">GBP</option>
                                <option @if($supplier_map->currency == 'USD') selected="selected" @endif value="USD">USD</option>
                                <option @if($supplier_map->currency == 'YEN') selected="selected" @endif value="YEN">YEN</option>
                            </select>
                        </div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>
    <style>
        .spacer-10{ width: 100%; height: 10px; }
    </style>
</div>