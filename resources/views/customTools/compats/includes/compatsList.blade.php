<div class="row" id="compatsList">
    <div class="col-lg-1"></div>
    <div class="col-lg-10">
        <div class="navbar navbar-light customPanel">
            <table class="table table-striped" style="text-align: center;">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">STORE</th>
                        <th scope="col">Brand</th>
                        <th scope="col">Model</th>
                        <th scope="col">Type</th>
                        <th scope="col">Version</th>
                        <th scope="col" style="width: 60px;"></th>
                        <th scope="col" style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compats AS $compat)
                    <tr onclick="$('.hideRows').css('display', 'none'); $('#editRow_{{$compat->id_compat}}').toggle();" style="text-align: center;">
                        <td>{{$compat->id_compat}}</td>
                        <td><img src="/images/logos/{{$compat->store}}.png?v1=1" style="width: 60px;padding: 7px 2px;border: 1px solid #ccc; background: #fff;border-radius: 5px 0 0 5px;"></td>
                        <td>{{$compat->brand->name}}</td>
                        <td>{{$compat->model->name}}</td>
                        <td>{{$compat->type->name}}</td>
                        <td>{{$compat->version->name}}</td>
                        <td><button class="btn btn-warning" onclick="$('.hideRows').css('display', 'none'); $('#editRow_{{$compat->id_compat}}').toggle();"><i class="fa-solid fa-pencil"></i></button></td>
                        <td><button class="btn btn-danger" onclick="removeCompat({{$compat->id_compat}})"><i class="fa-solid fa-trash"></i></button></td>
                    </tr>
                    <tr class="hideRows" style="display: none;border: 1px solid #333" id="editRow_{{$compat->id_compat}}">
                        <td colspan="7">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div>
                                        <table class="table table-striped" style="text-align: center;border: 1px solid #333; width: 600px; margin: 20px auto;">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Brand</th>
                                                    <th scope="col">Model</th>
                                                    <th scope="col">Type</th>
                                                    <th scope="col">Version</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><div onclick="editCompatItem(1, {{$compat->id_brand}},   '{{$compat->brand->name}}')">{{$compat->brand->name}}    </div></td>
                                                    <td><div onclick="editCompatItem(1, {{$compat->id_model}},   '{{$compat->model->name}}')">{{$compat->model->name}}    </div></td>
                                                    <td><div onclick="editCompatItem(1, {{$compat->id_type}},    '{{$compat->type->name}}')">{{$compat->type->name}}     </div></td>
                                                    <td><div onclick="editCompatItem(1, {{$compat->id_version}}, '{{$compat->version->name}}')">{{$compat->version->name}}  </div></td>
                                                </tr>
                                                <tr style="display: none;">
                                                    <td colspan="4"> <div class="spacer-20"></div> </td>
                                                </tr>
                                                <tr style="display: none;">
                                                    <td colspan="2">
                                                        <div>
                                                            <h5>ROW</h5>
                                                            <input type="text" onblur="setData($(this), 'row', {{$compat->id_compat}})" onclick="" value="{{$compat->row}}" placeholder="0" style="text-align: center;">
                                                        </div>
                                                    </td>
                                                    <td colspan="2">
                                                        <div>
                                                            <h5>POSITION</h5>
                                                            <input type="text" onblur="setData($(this), 'position', {{$compat->id_compat}})" value="{{$compat->position}}" placeholder="0" style="text-align: center;">
                                                        </div>
                                                        
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>        
                                    </div> 
                                </div> 
                                <div class="col-lg-12">
                                    <div style="width: 100%; height: 20px;"></div>
                                </div>
                                <div class="col-lg-3">
                                    <div style="text-align: center;background-color: #999; border: 1px solid #333;height: 212px;" id="brandLogo_{{$compat->id_compat}}" onclick="openModelUpload('logo', {{$compat->brand->id_option}})">
                                        <h5 style="margin-top: 20px;color: #FFF;">BRAND LOGO</h5>
                                        <img style="max-width: 150px;" src="https://webtools.all-stars-motorsport.com/uploads/compats/brand/{{$compat->brand->id_option}}.png?t={{rand()}}">                                            
                                    </div>
                                </div> 
                                <div class="col-lg-6">
                                    <div style="text-align: center;background-color: #999; border: 1px solid #333;height: 212px;" id="carCartoon_{{$compat->id_compat}}" onclick="openModelUpload('cartoon', {{$compat->id_compat}})">
                                        <h5 style="margin-top: 20px;color: #FFF;">CAR CARTOON</h5>
                                        <img style="max-width: 250px;" src="https://webtools.all-stars-motorsport.com/uploads/compats/compat/{{$compat->id_compat}}.png?t={{rand()}}">                                            
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div style="text-align: center;background-color: #999; border: 1px solid #333;height: 212px;" id="brandLogoHover_{{$compat->id_compat}}" onclick="openModelUpload('hover', {{$compat->brand->id_option}})">
                                        <h5 style="margin-top: 20px;color: #FFF;">BRAND LOGO ( HOVER )</h5>
                                        <img style="max-width: 150px;" src="https://webtools.all-stars-motorsport.com/uploads/compats/brand_hover/{{$compat->brand->id_option}}.png?t={{rand()}}">                                            
                                    </div>
                                </div>
                                <div class="col-lg-2"></div>
                                <div class="col-lg-12">
                                    <div style="width: 100%; height: 20px;"></div>
                                </div>
                            </div> 
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-1"></div>
</div>