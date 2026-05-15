<div class="col-lg-12" id="daily_stats">
    <div class="navbar navbar-light customPanel">
        <div class="panel panel-default" style="display: flow-root">
            <div class="panel-body ">
                <div class="row">
                <div class="col-lg-10" id="monthly_goal_1">
                	<div class="panel" style="padding: 5px 10px;margin: 0;border: 1px solid #aaa;border-radius: 5px;background-color: #eee;">
                		<div style="height: 30px;color: dodgerblue; padding: 0px;font-size: 24px;text-transform: uppercase;font-weight: bold;text-align: center;cursor: pointer;" onclick="$('#table_resumo_ano').toggle();">
                            {{ __('tags.Values by month until')}} ( {{date('Y')}}-{{date('m')}}-{{date('d')}} )
                		</div>
                		<div style="margin-top: 10px;">
                    		<table class="table table-bordered" style="text-align: center;display: none;font-weight: bolder;" id="table_resumo_ano">
                			    <tbody>
                			        <tr>
                    			        <td>{{ __('tags.month')}}</td>
                    			        {{--<td>LAST YEAR - ASM</td>--}}
                    			        {{--<td>LAST YEAR - ASD</td>--}}
                    			        <td>{{ __('tags.n-1')}}</td>
                    			        <td style="color: dodgerblue">{{ __('tags.Accomplished')}}</td>
                    			        <td>{{ __('tags.Objective')}}</td>
                    			        <td style="text-align: right;">{{ __('tags.Difference')}}</td>
                    			    </tr>  
                    			    @foreach($months AS $month)
                        			    <tr>
                        					<td>{{$month->name}}</td>
                        					{{--<td>@if(isset($month->asm)){{$month->asm}} @else @endif</td>--}}
                        					{{--<td>@if(isset($month->asd)){{$month->asd}} @else @endif</td>--}}
                        					<td>{{$month->last_year}}</td>
                        					<td>{{$month->accomplished}}</td>
                        					<td>{{$month->objective}}</td>
                        					<td @if($month->difference > 0 ) style="color: green; text-align: right;" @else style="color: red; text-align: right;" @endif>{{$month->difference}}</td>
                        				</tr>	
                    				@endforeach
                    			</tbody>
                			</table>
                        </div>
                    </div>
                    <div class="panel" style="padding: 5px 10px;margin: 20px 0;border: 1px solid #aaa;border-radius: 5px;background-color: #eee;">
                    		<div style="height: 30px;color: dodgerblue; padding: 0px;font-size: 24px;text-transform: uppercase;font-weight: bold;text-align: center;">
                            {{ __('tags.Monthly goal')}} ( {{date('Y')}}-{{date('m')}} )
                		</div>
                		<div class="hidden_links_out_of_stock" style="margin-top: 10px;">
                    		<table class="table table-bordered" style="text-align: center;font-weight: bolder;">
                				<tbody>
                				    <tr style="font-weight: bolder;">
                    			        <td>{{ __('tags.day')}}</td>
                    			        {{-- <td>Last year - ASM</td> --}}
                    			        {{--<td>Last year - ASD</td> --}}
                    			        <td>Last year</td>
                    			        <td>{{ __('tags.Objective')}}</td>
                    			        <td>{{ __('tags.Invoiced')}}</td>
                    			        {{--<td style="text-align: right;">{{ __('tags.%')}}</td>--}}
                					</tr>
                					
                    			    @foreach($goals->dataMonth AS $goal)
                					<tr>
                    					<td @if( ( $goals->totalMonthObjectivoValue/date('t') ) < $goal->invoicedValue) style="color: darkgreen;" @else style="color: red;" @endif>{{$goal->name}}</td>
                    					{{-- <td @if( ( $goals->totalMonthObjectivoValue/date('t') ) > $goal->invoicedValue) style="color: darkgreen;" @else style="color: red;" @endif>{{$goal->lastyear_asm}}</td> --}}
                    					{{-- <td @if($goal->accomplished > 0) style="color: darkgreen;" @else style="color: red;" @endif>{{$goal->lastyear_asd}}</td> --}}
                    					<td @if( ( $goals->totalMonthObjectivoValue/date('t') ) < $goal->invoicedValue) style="color: darkgreen;" @else style="color: red;" @endif>{{$goal->lastyear}}</td>
                    					{{--<td @if($goal->accomplished > 0) style="color: darkgreen;" @else style="color: red;" @endif>{{$goal->objective}}</td>--}}
                    					<td @if( ( $goals->totalMonthObjectivoValue/date('t') ) < $goal->invoicedValue) style="color: darkgreen;" @else style="color: red;" @endif>{{number_format($goals->totalMonthObjectivoValue/date('t'), 2, ',', ' ')}} €</td>
                    					<td @if( ( $goals->totalMonthObjectivoValue/date('t') ) < $goal->invoicedValue) style="color: darkgreen;" @else style="color: red;" @endif>{!! $goal->invoiced !!}</td>
                    					{{--<td @if( ( $goals->totalMonthObjectivoValue/date('t') ) < $goal->invoicedValue) style="color: darkgreen; text-align: right;" @else style="color: red; text-align: right;" @endif>{{$goal->percentage}}</td>--}}
                					</tr>
                					@endforeach
                					<tr>
                    					<td></td>
                    					{{--<td style="font-size: 28px;font-weight: bold;">{{$goals->totalMonthObjectivoASM}}</td>--}}
                    					{{--<td style="font-size: 28px;font-weight: bold;">{{$goals->totalMonthObjectivoASD}}</td>--}}
                    					<td style="font-size: 28px;font-weight: bold;">{{$goals->totalMonthLastYear}}</td>
                    					<td style="font-size: 28px;font-weight: bold;">{{$goals->totalMonthObjectivo}}</td>
                    					<td style="font-size: 28px;font-weight: bold;">{{$goals->totalMonthFacturado}}</td>
                    					{{--<td></td>--}}
                					</tr>
                				</tbody>
                			</table>
                		</div>
                	</div>
                </div>
                <div class="col-lg-2" id="monthly_goal_2" style="text-align: center; padding: 0px;font-size: 24px;text-transform: uppercase;font-weight: bold;float: left;">
                	<div class="panel" style="padding: 30px 10px;margin: 0 20px 20px 0;border: 1px solid #ddd;border-radius: 5px;background-color: #eee;">
                	    <div style="height: 30px;color: dodgerblue;font-size: 20px;display: inline-table;">{{ __('tags.Accumulated N-1')}}</div>
                	    <div style="height: 30px;color: grey;margin: 20px 0; font-size: 28px">{{$side->accumulated_last_year_until_now}}</div>
                	</div>
                	<div class="panel" style="padding: 30px 10px;margin: 0 20px 20px 0;border: 1px solid #ddd;border-radius: 5px;background-color: #eee;">
                	    <div style="height: 30px;color: dodgerblue;font-size: 20px;display: inline-table;">{{ __('tags.Accomplished until today')}}</div>
                	    <div style="height: 30px;color: grey;margin: 20px 0; font-size: 28px">{{$side->until_today}}</div>
                	</div>
                	<div class="panel" style="padding: 30px 10px;margin: 0 20px 20px 0;border: 1px solid #ddd;border-radius: 5px;background-color: #eee;">
                	    <div style="height: 30px;color: dodgerblue;font-size: 20px;display: inline-table;">{{ __('tags.Difference')}}</div>
                	    <div style="height: 30px;color:  @if($side->difference > 0) green @else red @endif ;margin: 20px 0 15px 0; font-size: 28px">{{$side->difference}}</div>
                	</div>
                	
                	<div class="panel" style="padding: 30px 10px;margin: 0 20px 20px 0;border: 1px solid #ddd;border-radius: 5px;background-color: #eee;">
                	    <div style="height: 30px;color: dodgerblue;font-size: 20px;display: inline-table;">{{ __('tags.Objective until today')}}</div>
                	    <div style="height: 30px;color: grey;margin: 20px 0; font-size: 28px">{{$side->objective_until_today}}</div>
                	</div>
                	
                	<div class="panel" style="padding: 30px 10px;margin: 0 20px 20px 0;border: 1px solid #ddd;border-radius: 5px;background-color: #eee;">
                	    <div style="height: 30px;color: dodgerblue;font-size: 20px;display: inline-table;">Missing to month goal</div>

                        @php
                            $percent_reached = $goals->totalMonthObjectivoValue > 0 ? ((($goals->totalMonthFacturadoValue * 100) / $goals->totalMonthObjectivoValue) - 100) : 0;
                        @endphp

                        @if( $percent_reached < 0 ) <div style="height: 30px;color:  red ; margin: 20px 0; font-size: 28px"> {{number_format( $percent_reached , 2, ',', ' ') }} % </div> @endif
                        @if( ( $percent_reached > 0 ) && ( $percent_reached < 5 )  ) <div style="height: 30px;color:  darkgreen ; margin: 20px 0; font-size: 28px"> {{number_format( $percent_reached , 2, ',', ' ') }} % </div> @endif
                        @if( $percent_reached > 5 ) <div style="height: 30px;color:  dodgerblue ; margin: 20px 0; font-size: 28px"> {{number_format( $percent_reached , 2, ',', ' ') }} % </div> @endif
                	    
                	    @if( ( $percent_reached > 0 ) && ( $percent_reached < 5 )  ) <div style="height: 30px;color: darkgreen ; margin: 20px 0; font-size: 22px"> 1º TIER REACHED! </div> @endif
                	    @if( $percent_reached > 5 )  <div style="height: 30px;color:  dodgerblue ; margin: 20px 0; font-size: 22px"> GOAL REACHED! </div> @endif
                	    
                	    
                	</div>
                	
                	<div class="panel" style="padding: 30px 10px;margin: 0 20px 20px 0;border: 1px solid #ddd;border-radius: 5px;background-color: #eee;">
                	    <div style="height: 30px;color: dodgerblue;font-size: 20px;display: inline-table;">{{ __('tags.Difference objective until today')}}</div>
                	    <div style="height: 30px;color: @if( $side->difference_objective_until_today < 0) red @else green @endif ; margin: 20px 0; font-size: 28px">{{$side->difference_objective_until_today}}</div>
                	</div>
                	
                	<div class="panel" style="padding: 30px 10px;margin: 0 20px 20px 0;border: 1px solid #ddd;border-radius: 5px;background-color: #eee;">
                	    <div style="height: 30px;color: dodgerblue;font-size: 20px;display: inline-table;">{{ __('tags.Objective')}}</div>
                	    <div style="height: 30px;color: grey;margin: 20px 0; font-size: 28px">{{$side->objective}}</div>
                	</div>
                	
                	<div class="panel" style="padding: 30px 10px;margin: 0 20px 20px 0;border: 1px solid #ddd;border-radius: 5px;background-color: #eee;">
                	    <div style="height: 30px;color: dodgerblue;font-size: 20px;display: inline-table;">{{ __('tags.Missing to achieve the final objective')}}</div>
                	    <div style="height: 30px;color:  red ; margin: 20px 0; font-size: 28px">{{$side->missing_to_objective}}</div>
                	</div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>