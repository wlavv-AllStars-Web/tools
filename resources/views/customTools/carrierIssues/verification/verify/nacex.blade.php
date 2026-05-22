<div class="navbar navbar-light customPanel" id="verificationContainer">
    <table class="table table-striped" style="text-align: center;width: 100%;margin-bottom: 0;">
        <tr>
            <td>
                <img src="/uploads/logos/carriers/{{$carrier}}.png" style="width: 75px;">
            </td>
            <td>
                <div><h3>SHIPPEMENTS</h3></div> 
                <div><h3>{{count($asd) + count($asm)}}</h3></div>
            </td>
            <td colspan="2">
                <div><h3 style="color: dodgerblue;">ASD</h3></div> 
                <div><h3>{{count($asd)}}</h3></div>
            </td>
            <td colspan="2">
                <div><h3 style="color: red;">ASM</h3></div> 
                <div><h3>{{count($asm)}}</h3></div>
            </td>
            <td>
                <div><h3>TOTAL INVOICE</h3></div> 
                <div><h3>{{number_format($total, 2, ',', ' ')}} €</h3></div>
            </td>
            <td>
                <div><h3>WEIGHT OVERSIZED</h3></div> 
                <div><h3>{{count($oversized_weight)}}</h3></div>
            </td>
        </tr>
    </table>
</div>

<div class="navbar navbar-light customPanel" id="verificationContainer">
    <table class="table table-striped" style="text-align: center;width: 100%;margin-bottom: 0;">
        <tr>
            <td>
                <button class="btn btn-info"   onclick="$('.allRows').css('visibility', 'collapse').css('height','0'); $('.store_asd').css('visibility', 'initial').css('height','initial')" style="width: 100px;">ASD</button> 
                <button class="btn btn-danger" onclick="$('.allRows').css('visibility', 'collapse').css('height','0'); $('.store_asm').css('visibility', 'initial').css('height','initial')" style="margin: 0 20px; width: 100px;">ASM</button>
                <button class="btn btn-info"   onclick="$('.allRows').css('visibility', 'collapse').css('height','0'); $('.oversized').css('visibility', 'initial').css('height','initial')" style="background-color: #E0B0FF;border: 1px solid #800080;color: #800080;width: 100px;">OVERSIZED</button>
            </td>
        </tr>
    </table>
</div>

<div class="navbar navbar-light customPanel" id="verificationContainer">
    <table class="table table-striped table-wrapper" style="text-align: center;width: 100%;margin-bottom: 0;table-layout: fixed">
        <tr>
            <td>STORE</td>
            <td>GUIA</td>
            <td>ORDER ID</td>
            <td>VOL</td>
            <td>WEIGHT</td>
            <td>VALUE</td>
            <td>LOCALITY</td>
            <td>W * H * L</td>
            <td>Weight</td>
            <td>QUOTED</td>
        </tr>
        @foreach($asd AS $row)
        
            @php
            
                $carrierWeight = (float)$row[18];
                $carrierWeight = number_format($carrierWeight, 2, ',', '');
                
                $weight = (float)$row['weight'];
                $weight = number_format($weight, 2, ',', '');
                
                $total = (float)$row['value'];
                $total = number_format($total, 2, ',', '');
                
                $carrierTotal = (float)$row[22];
                $carrierTotal = number_format($carrierTotal, 2, ',', '');
                
            @endphp
            
            <tr class="allRows store_asd">
                <td><span style="color: dodgerblue">ASD</span></td>
                <td>{{$row[4]}}</td>
                <td>{{str_replace('pedido_', '', $row[6])}}</td>
                <td>{{$row[17]}}</td>
                <td><span @if( $weight < $carrierWeight ) style="color: red;" @else style="color: green;" @endif >{{$carrierWeight}} Kg</span></td>
                <td><span @if( $total > $carrierTotal ) style="color: red;" @else style="color: green;" @endif >{{$carrierTotal}} €</span></td>
                <td>{{$row[8]}}</td>
                <td> - </td>
                <td><span @if( $weight < $carrierWeight ) style="color: red;" @else style="color: green;" @endif >{{$weight}} Kg</span></td>
                <td><span @if( $total > $carrierTotal ) style="color: red;" @else style="color: green;" @endif >{{$total}} €</span></td>
            </tr>
            
        @endforeach
        
        @foreach($asm AS $row)
        
            @php
            
                $carrierWeight = (float)$row[18];
                $carrierWeight = number_format($carrierWeight, 2, ',', '');
                
                $weight = (float)$row['weight'];
                $weight = number_format($weight, 2, ',', '');
                
                $total = (float)$row['value'];
                $total = number_format($total, 2, ',', '');
                
                $carrierTotal = (float)$row[22];
                $carrierTotal = number_format($carrierTotal, 2, ',', '');
                
            @endphp
            
            <tr class="allRows store_asm">
                <td><span style="color: red">ASM</span></td>
                <td>{{$row[4]}}</td>
                <td>{{str_replace('pedido_', '', $row[6])}}</td>
                <td>{{$row[17]}}</td>
                <td><span @if( $weight < $carrierWeight ) style="color: red;" @else style="color: green;" @endif >{{$carrierWeight}} Kg</span></td>
                <td><span @if( $total > $carrierTotal ) style="color: red;" @else style="color: green;" @endif >{{$carrierTotal}} €</span></td>
                <td>{{$row[8]}}</td>
                <td>{{$row['width']}} * {{$row['height']}} * {{$row['length']}} </td>
                <td><span @if( $weight < $carrierWeight ) style="color: red;" @else style="color: green;" @endif >{{$weight}} Kg</span></td>
                <td><span @if( $total > $carrierTotal ) style="color: red;" @else style="color: green;" @endif >{{$total}} €</span></td>
            </tr>
            
        @endforeach
    </table>
</div>
