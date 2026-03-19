 <!DOCTYPE html>
<html>
    <head>
        <title>KPI ALL STARS GROUP</title>
        
        <script>
            
            setTimeout(function(){
               window.location.reload(1);
            }, 300000);
            
        </script>

        <style>
            
            body { background-color: #e5e5e5; font-family: arial; padding: 0px;margin: 0; }
            .mainContainer{ margin: 0; font-family: arial; font-weight: bold; display: flex;flex-direction:column; height:100dvh; justify-content: space-between;}
            .panel { display: flex; }
            .panel:nth-child(1) {height:20vh;padding: 5px;}
            .panel:nth-child(2) {height:40vh;padding: 5px;}
            .panel:nth-child(3) {height:40vh;padding: 5px;}
            .customPanel { background-color: #F2F2F2; border: 2px solid #c2c2c2; border-radius: 5px; display: flex;font-family: arial; width:100%;justify-content:center;align-items:center;height:100%;}
            .counter_container{ text-align: center;flex:1;font-family: arial; }
            .roundCounter { width: 6rem; height: 6rem;border-radius: 50%; font-size: 3rem; margin: 0 auto;font-family: arial; display:flex;justify-content:center;align-items:center;}
            .roundCounter > div{ text-align: center;font-family: arial; }
            .label{ margin-top: 10px; font-weight: bold; text-transform: uppercase;font-size: 1.25rem;font-family: arial; }
            
            .awaiting{      background-color: #FEFFA0; color: #000; }
            .packing{       background-color: #048DCD; color: #fff; }
            .shipped{       background-color: #8A2BE2; color: #fff; }
            .warranty{      background-color: #EBC5E0; color: #000; }
            .backorders{    background-color: #F78E1F; color: #fff; }
            .partial{       background-color: #B46700; color: #fff; }
            .pending{       background-color: #BFBFBF; color: #000; }
            
            .width15{ width: 15%; float: left; }
            .width20{ width: 20%; float: left; }
            .width25{ width: 25%; float: left; }
            .width60{ width: 60%; float: left; }
            .width70{ width: 70%; float: left; }
            .width100{ width: 100%; float: left; }
            
            .spacer-10{ width: 100%; height: .5vh; display: flex; }
            .spacer-20{ width: 100%; height: 1vh; display: flex; }
            
            .mt20{ margin-top: 20px; }
            .ml20{ margin-left: 20px; }
            .mr20{ margin-right: 20px; }
            .m20{ margin: 20px; }
            .mAuto0{ margin: 0 auto; }
            .mAuto10{ margin: 10px auto; }
            .mAuto20{ margin: 20px auto; }
            .mAuto30{ margin: 30px auto; }
            .textCenter{ text-align: center; }
            
            .title2{ width: 100%; margin: .5rem 0; color: #666666; font-size: 3.2vh; }
            .title5{ width: 100%; margin: .85rem 0 0 0; color: dodgerblue; font-size:3.7vh }
            .labelP{ width: 100%; margin: 0px 0 10px 0; color: #666; font-size: 2.9rem; font-weight: bolder;  }
            
            .displayBlock{ display: block !important; }
            .displayGrid{ display: grid !important; }
            .diplayFlexCol{display:flex;flex-direction:column;justify-content:center !important;height: -webkit-fill-available;}
            .fontSize40{ font-size: 4vh; margin: 20px; }
            .fontSize60{ font-size: 5vh; margin: 20px; }
            .fontSize80{ font-size: 5vh; margin: 20px; }
            .fontSize100{ font-size: 7vh; margin: 10px; }
            .fontSize120{ font-size: 8vh; margin: 20px; }
            
            .colorNOK{ color: red; }
            .colorOK{ color: darkgreen; }
            
            .margin-auto{ margin: 0 auto; }
            
            .hLine{ width: calc(100% - 60px);  margin: 5px 30px; background-color: #ccc; height: 2px; }
            
            .width300{ width: 300px; }

            .customPanel > img { width: auto;height:100%;max-height:6.5rem; }
            
            .panel-end {height: 100%; max-height: 36vh;}
            .panel-end .title5 {font-size: 3.2vh}
            .panel-end .labelP {font-size: 3.7vh}
        </style>
        
    </head>
    <body>
        <div class="mainContainer">
            <div class="panel">
                <div class="customPanel width100">
                    <div class="counter_container">
                        <div class="roundCounter awaiting"><div>{{$awaiting}}</div></div>
                        <div class="label">AWAITING</div>
                    </div>
                    <div class="counter_container">
                        <div class="roundCounter packing"><div>{{$packing}}</div></div>
                        <div class="label">PACKING</div>
                    </div>
                    <div class="counter_container">
                        <div class="roundCounter shipped"><div>{{$shipped}}</div></div>
                        <div class="label">SHIPPED</div>
                    </div>
                    <div class="counter_container">
                        <div class="roundCounter warranty"><div>{{$warranty}}</div></div>
                        <div class="label">WARRANTY</div>
                    </div>
                    <div class="counter_container">
                        <div class="roundCounter backorders"><div>{{$backorders}}</div></div>
                        <div class="label">BACKORDERS</div>
                    </div>
                    <div class="counter_container">
                        <div class="roundCounter partial"><div>{{$partial}}</div></div>
                        <div class="label">PARTIAL</div>
                    </div>
                    <div class="counter_container">
                        <div class="roundCounter pending"><div>{{$pending}}</div></div>
                        <div class="label">PENDING</div>
                    </div>
                </div>
            </div>
            <div class="panel">
                <div class="customPanel width20 textCenter diplayFlexCol">
                    <div><h2 class="title2">TODAY</h2></div>
                    <div>
                        <div><h5 class="title5">FORCAST</h5></div>
                        <div><p class="labelP">{{$today->forcast}}</p></div>
                    </div>
                    <div>
                        <div><h5 class="title5">REALIZED</h5></div>
                        <div><p class="labelP @if( $today->realized_value > $daillyGoal ) ok colorOK @else nok colorNOK @endif">{{$today->realized}}</p></div>
                    </div>
                </div>                
                <div class="customPanel width60 ml20 mr20 textCenter diplayFlexCol">
                    <div style="display: none;">
                    <div><h5 class="title5 fontSize60" style="margin: 0">GROUP RESULT</h5></div>
                    <div><p class="labelP fontSize100">{{$group_result}}</p></div>
                    </div>
                    <div style="margin: 30px auto;"><span class="title5 fontSize100" style="margin: 0">GROUP RESULT: </span> <span class="labelP fontSize100">{{$group_result}}</span></div>
                    <div>
                        <p class="labelP fontSize60" style="margin: 0">
                            <div style="display: grid;text-align: center;">
                                <div>
                                    <span style="float: left;"><p class="labelP fontSize40" style="margin: 0 20px;font-weight: 300;">REALISATION UNTIL TODAY: </p></span>
                                    <span style="float: left;color: {{$status_color}}; font-size: 40px; margin:0 10px;font-weight: bold;"> {{ number_format( $status, 2, ',', ' ') }} %</span>                                    
                                </div>
                                <table style="width: 100%;font-weight: bold;color: #777;font-size: 24px;margin-top: 20px;">
                                    <tr>
                                        <td>GOAL UNTIL TODAY</td>
                                        <td>RESULT UNTIL TODAY</td>
                                    </tr>
                                    <tr>
                                        <td>{{ number_format( $objective_until_today, 2, ',', ' ') }} €</td>
                                        <td @if($realised_until_today > $objective_until_today) style="color: green;" @else style="color: red;" @endif>{{ number_format( $realised_until_today, 2, ',', ' ') }} €</td>
                                    </tr>
                                </table>
                            </div>
                        </p>
                    </div>
                    <div style="display: none;">
                        <p class="labelP fontSize60" style="margin: 0">
                            <div style="display: grid;text-align: center;">
                                <div>
                                    <span><p class="labelP fontSize40" style="margin: 0 20px;font-weight: 300;">MONTH REALISATION: </p></span>
                                    <br>
                                    <span style="float: left;color: {{$status_color}}; font-size: 40px; margin:0 10px;font-weight: bold;"> 
                                        {{ number_format( $progress, 2, ',', ' ') }} %
                                        <progress id="progress" value="{{$progress}}" max="100">{{$progress*100}}%</progress>
                                        <span style="color: green;">100 %</span>
                                    </span>      
                                </div>
                                <table style="width: 100%;font-weight: bold;color: #777;font-size: 24px;margin-top: 20px;">
                                    <tr>
                                        <td>GOAL: <span style="color: dodgerblue;">{{ number_format( $monthGoal, 2, ',', ' ') }} €</span></td>
                                        <td>INVOICED: <span style="color: {{ ( $realised_until_today > $monthGoal ) ? 'green' : 'red' }}">{{ number_format( $realised_until_today, 2, ',', ' ') }}</span> </td>
                                    </tr>
                                </table>
                            </div>
                        </p>
                    </div>
                    <div class="row" style="font-size: 26px; text-align: center; display: inline-flex;border-top: 1px solid #aaa;padding: 10px;">
                        <div class="col-lg-3" style="margin: 0 20px;"> 
                            <span style="color: dodgerblue;padding: 0 10px;">元</span>
                            <span style="color: #999;">{{$yuan}}</span>
                        </div>
                        <div class="col-lg-3" style="margin: 0 20px;"> 
                            <span style="color: dodgerblue;padding: 0 10px;">£</span>
                            <span style="color: #999;">{{$pound}}</span>
                        </div>
                        <div class="col-lg-3" style="margin: 0 20px;"> 
                            <span style="color: dodgerblue;padding: 0 10px;">$</span>
                            <span style="color: #999;">{{$dollar}}</span>
                        </div>
                        <div class="col-lg-3" style="margin: 0 20px;"> 
                            <span style="color: dodgerblue;padding: 0 10px;">¥</span>
                            <span style="color: #999;">{{$yen}}</span>
                        </div>
                    </div>
                </div>
                <div class="customPanel width20 textCenter diplayFlexCol">
                    <div><h2 class="title2">YESTERDAY</h2></div>
                    <div>
                        <div><h5 class="title5">FORCAST</h5></div>
                        <div><p class="labelP">{{$today->forcast}}</p></div>
                    </div>
                    <div>
                        <div><h5 class="title5">REALIZED</h5></div>
                        <div><p class="labelP @if( $yesterday->realized_value > $daillyGoal ) ok colorOK @else nok colorNOK @endif">{{$yesterday->realized}}</p></div>
                    </div>
                </div>
            </div>
            <div class="panel panel-end">
                <div class="customPanel width25 mr20 diplayFlexCol">
                    <img src="/images/logos/asd-350x250.png?v1=0" class="mAuto0">
                    <div class="hLine"></div>
                    <div class="textCenter displayBlock">
                    <div><h5 class="title5">FORCAST</h5></div>
                    <div><p class="labelP">{{$forcast_asd}}</p></div>
                        <div><h5 class="title5">REALIZED</h5></div>
                        <div><p class="labelP @if($reached_asd == 1) colorOK @else colorNOK @endif">{{$realized_asd}}</p></div>
                    </div>
                    <div class="spacer-20"></div>
                </div>
                <div class="customPanel width25 mr20 diplayFlexCol">
                    <img src="/images/logos/asm-350x250.png?v1=0" class="mAuto0 ">
                    <div class="hLine"></div>
                    <div class="textCenter displayBlock">
                    <div><h5 class="title5">FORCAST</h5></div>
                    <div><p class="labelP">{{$forcast_asm}}</p></div>
                        <div><h5 class="title5">REALIZED</h5></div>
                        <div><p class="labelP @if($reached_asm == 1) colorOK @else colorNOK @endif">{{$realized_asm}}</p></div>
                    </div>
                    <div class="spacer-20"></div>
                </div>
                <div class="customPanel width25 mr20 diplayFlexCol">
                    <img src="/images/logos/er-350x250.png?v1=0" class="mAuto0 " >
                    <div class="hLine"></div>
                    <div class="textCenter displayBlock">
                    <div><h5 class="title5">FORCAST</h5></div>
                    <div><p class="labelP">{{$forcast_er}}</p></div>
                        <div><h5 class="title5">REALIZED</h5></div>
                        <div><p class="labelP @if($reached_er == 1) colorOK @else colorNOK @endif">{{$realized_er}}</p></div>
                    </div>
                    <div class="spacer-20"></div>
                </div>
                <div class="customPanel width25 diplayFlexCol">
                    <img src="/images/logos/em-350x250.png?v1=0" class="mAuto0 " >
                    <div class="hLine"></div>
                    <div class="textCenter displayBlock">
                    <div><h5 class="title5">FORCAST</h5></div>
                    <div><p class="labelP">{{$forcast_em}}</p></div>
                        <div><h5 class="title5">REALIZED</h5></div>
                        <div><p class="labelP @if($reached_em == 1) colorOK @else colorNOK @endif">{{$realized_em}}</p></div>
                    </div>
                    <div class="spacer-20"></div>
                </div>
            </div>
        </div>
    </body>
</html> 