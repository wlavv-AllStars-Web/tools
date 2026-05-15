@extends('layouts.app')
@section('content')

<div style="display: inline-flex;">
    <div class="navbar navbar-light customPanel" style="width: 250px;float: left;margin-right: 20px;">
        <div id="backorders_suppliers">
            <table class="table table-hover">
                @foreach($suppliers AS $supplier)
                
                    <tr style="cursor: pointer;font-size: 17px">
                        <td>
                            <div onclick="changeSupplierBackorder({{$supplier->id_supplier}})"><div style="width: 15px;float: left;">@if($supplier->quantity_replied == $supplier->number_of_rows) <i style="color: green;" class="fa-solid fa-check"></i> @else <i style="color: red;" class="fa-solid fa-xmark"></i> @endif</div> <div style="margin: 0 5px;float: left;" >|</div> {{$supplier->supplier}}</div>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="navbar navbar-light customPanel" style="width: calc( 100% - 270px);float: left;">
        <div id="backorders_suppliers_holder">
            {!! $supplierBackorder !!}
        </div>
    </div>    
</div>


<script>

    function changeSupplierBackorder(id_supplier){
        $.ajax({
            type: 'POST',
            url: "{{route('suppliersBackorders.getSuppliersBackorders')}}",
            dataType: "json",
            data: {
                _token: "{{ csrf_token() }}",
                id_supplier: id_supplier
            },
            success: function(response) {
                
                var data = JSON.parse(JSON.stringify(response));

                $('#backorders_suppliers_holder').replaceWith(data.html);
            }       
        });
    }

</script>

@endsection