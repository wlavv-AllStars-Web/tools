<div class="col-lg-12" id="shipping_report">
    <div class="navbar navbar-light customPanel">
        <div class="panel panel-default" style="display: flow-root">
            <div class="panel-body" style="text-align: center; font-size: 40px;">
                <div class="row">
                    <div class="col-lg-4">
                        <div> <h2>{{ __('tags.PAID BY CUSTOMER')}}</h2> </div>
                        <div> <h5 class="custom_title">{{ number_format( $totalPaidByCustomer['total'], 2, ',', ' ' )}} €</h5>       </div>
                    </div>
                    <div class="col-lg-4"><h2 style="font-weight: bolder; font-size: 40px;">{{ __('tags.TOTAL SHIPPING COST')}}</h2></div>
                    <div class="col-lg-4">
                        <div> <h2>{{ __('tags.PAID BY ALL STARS ( ASM ONLY )')}}</h2> </div>
                        <div> <h5 class="custom_title">{{ number_format( $totalByOrder['total'], 2, ',', ' ' )}} €</h5>  </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
    <div class="navbar navbar-light customPanel">
        <div class="panel panel-default" style="display: flow-root">
            <div class="panel-body" style="text-align: center; font-size: 40px;">
                <div class="row" style="margin-top: 40px;">
                    @foreach($totalPaidByCustomer AS $key => $shipping)
                        @if($key !='total')
                            <div class="col-lg-4">
                                <div> <h5 class="custom_title">{{ number_format( $totalPaidByCustomer[$key], 2, ',', ' ' )}} €</h5>       </div>
                            </div>
                            <div class="col-lg-4"><h2 style="font-weight: bolder; font-size: 50px;">{{$key}}</h2></div>
                            <div class="col-lg-4">
                                <div> <h5 class="custom_title">{{ number_format( $totalByOrder[$key], 2, ',', ' ' )}} €</h5>  </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                
                </div>
            </div> 
        </div>
    </div>
</div>