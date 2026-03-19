<div class="navbar navbar-light customPanel" id="verificationContainer">
    <table class="table table-striped" style="text-align: center;width: 100%;margin-bottom: 0;">
        <tr>
            <td>
                <img src="/images/logos/carriers/{{$carrier}}.png" style="width: 75px;">
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
            <td>REF.</td>
            <td>VOL</td>
            <td>WEIGHT</td>
            <td>VALUE</td>
            <td>COUNTRY</td>
            <td>W * H * L</td>
            <td>Weight</td>
            <td>QUOTED</td>
        </tr>
        @foreach($asd AS $row)
        
            @php
            
                $measurements = [ $row['width'], $row['height'], $row['length'] ];
                
                sort($measurements);
                
                $volumetric = $measurements[0]*2 + $measurements[1]*2 + $measurements[2];
                
                $value = (float)$row['value'];
                $value = number_format($value, 2, ',', ' ');
                
            @endphp
        
            <tr class="allRows store_asd @if($volumetric > 300) oversized @else  @endif  @if($row['weight'] > 31.49) oversized @else  @endif ">
                <td><span style="color: dodgerblue">ASD</span></td>
                <td>{{$row[0]}}</td>
                <td>{{$row[1]}}</td>
                <td>{{$row[6]}}</td>
                <td><span style="@if( (int)$row['9'] < 31.5) color: green; @else color: red; @endif">{{$row[9]}} Kg</span></td>
                <td>{{$row[10]}} €</td>
                <td>{{$row[26]}}</td>
                <td><span style="@if( (int)$volumetric < 300) color: green; @else color: red; @endif">{{$volumetric}}</span></td>
                <td><span style="@if( (int)$row['weight'] < 31.5) color: green; @else color: red; @endif">{{$row['weight']+0}} Kg</span></td>
                <td>{!!$value!!} €</td>
            </tr>
        @endforeach
        @foreach($asm AS $row)
        
            @php
            
                $measurements = [ $row['width'], $row['height'], $row['length'] ];
                
                sort($measurements);
                
                $volumetric = $measurements[0]*2 + $measurements[1]*2 + $measurements[2];
                
                $value = (float)$row['value'];
                $value = number_format($value, 2, ',', ' ');
                
            @endphp
            
            <tr class="allRows store_asm @if($volumetric > 300) oversized @else  @endif  @if($row['weight'] > 31.49) oversized @else  @endif ">
                <td>
                    <span style="color: red">ASM</span>
                </td>
                <td>{{$row[0]}}</td>
                <td>{{$row[1]}}</td>
                <td>{{$row[6]}}</td>
                <td><span style="@if( (int)$row['9'] < 31.5) color: green; @else color: red; @endif">{{$row[9]}} Kg</span></td>
                <td>{{$row[10]}} €</td>
                <td>{{$row[26]}}</td>
                <td><span style="@if( (int)$volumetric < 300) color: green; @else color: red; @endif">{{$volumetric}}</span></td>
                <td><span style="@if( (int)$row['weight'] < 31.5) color: green; @else color: red; @endif">{{$row['weight']+0}} Kg</span></td>
                <td>{!!$value!!} €</td>
            </tr>
        @endforeach
    </table>
</div>