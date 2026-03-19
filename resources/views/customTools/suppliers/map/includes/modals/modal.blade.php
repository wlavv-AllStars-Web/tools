<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ADD INFO</h5>
            </div>
            <form method="POST" action="{{route('suppliersMap.store') }}">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-lg-4" style="text-align: right;font-weight: bolder"><label for="type">SUPPLIER</label></div>
                        <div class="col-lg-8" style="text-align: center;">
                            <select name="id_supplier" style="width: 100%;">
                                @foreach($new_suppliers AS $new_supplier)
                                    <option value="{{$new_supplier->id_supplier}}">{{$new_supplier->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4" style="text-align: right;font-weight: bolder; margin-top: 10px;"><label for="type">BRAND</label></div>
                        <div class="col-lg-8" style="text-align: center; margin-top: 10px;">
                            <select name="id_manufacturer" style="width: 100%;">
                                    <option value="0">UNDEFINED</option>
                                @foreach($new_manufacturers AS $new_manufacturer)
                                    <option value="{{$new_manufacturer->id_manufacturer}}">{{$new_manufacturer->name}}</option>
                                @endforeach
                            </select>
                        </div>
                            
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;font-weight: bolder"></div>
                        <div class="col-lg-2" style="text-align: center;"><label for="type">Warranty</label>
                            <br>
                            <input name="warranty" type="checkbox" value="1">
                        </div>
    
                        <div class="col-lg-2" style="text-align: center;"><label for="type">Description</label>
                            <br>
                            <input name="description" type="checkbox" value="1">
                        </div>
    
                        <div class="col-lg-2" style="text-align: center;"><label for="type">EAN-13</label>
                            <br>
                            <input name="ean13" type="checkbox" value="1">
                        </div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Dealer website</label></div>
                        <div class="col-lg-8"><input name="dealer_website" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Contact</label></div>
                        <div class="col-lg-8"><input name="contact" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Email</label></div>
                        <div class="col-lg-8"><input name="email" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Address</label></div>
                        <div class="col-lg-8"><input name="address" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Country</label></div>
                        <div class="col-lg-8"><input name="country" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Phone</label></div>
                        <div class="col-lg-8"><input name="phone" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Website</label></div>
                        <div class="col-lg-8"><input name="website" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        
                        <div class="col-lg-4" style="text-align: right;"><label for="type">B2B link</label></div>
                        <div class="col-lg-8"><input name="b2b_link" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Username</label></div>
                        <div class="col-lg-8"><input name="b2b_username" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Password</label></div>
                        <div class="col-lg-8"><input name="b2b_password" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Pics</label></div>
                        <div class="col-lg-8"><input name="pics" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Catalogue</label></div>
                        <div class="col-lg-8"><input name="catalogue" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Instructions</label></div>
                        <div class="col-lg-8"><input name="instructions" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Inventory</label></div>
                        <div class="col-lg-8"><input name="inventory" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Discount</label></div>
                        <div class="col-lg-8"><input name="discount" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Terms</label></div>
                        <div class="col-lg-8"><input name="terms" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">IBAN / Account number</label></div>
                        <div class="col-lg-8"><input name="iban" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">BIC / SWIFT</label></div>
                        <div class="col-lg-8"><input name="swift" type="text" value="" style="width: 100%;"></div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Incoterm</label></div>
                        <div class="col-lg-8">
                            <select name="incoterm" style="width: 100%;">
                                <option value="Credit">Credit</option>
                                <option value="CPT">CPT</option>
                                <option value="DAP">DAP</option>
                                <option value="DDP">DDP</option>
                                <option value="DPU">DPU</option>
                                <option value="EXW">EXW</option>
                                <option value="FCA">FCA</option>
                                <option value="FOB">FOB</option>
                                <option value="Missing">Missing</option>
                            </select>
                        </div>
                        <div class="col-lg-12"><div class="spacer-10"></div></div>
                        <div class="col-lg-4" style="text-align: right;"><label for="type">Currency</label></div>
                        <div class="col-lg-8">
                            <select name="currency" style="width: 100%;">
                                <option value="UNKNOWN">UNKNOWN</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                                <option value="USD">USD</option>
                                <option value="YEN">YEN</option>
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