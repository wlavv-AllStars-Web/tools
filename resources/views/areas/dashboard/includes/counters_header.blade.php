<div class="row">
    @if(count($asm) > 0)
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel"> ALL STARS MOTORSPORT PANEL'S </div>
        </div>
        @foreach($asm AS $counter)
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                <div class="panel panel-default" style="display: flow-root">
                        <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
                            <div 
                            @if( ( $counter->counter > 0 ) && ( $counter->panel != 'reviews') ) 
                                onclick="getPanelContent('{{$counter->tab}}', '{{$counter->panel}}')" 
                            @elseif( $counter->panel == 'reviews' ) 
                                onclick="window.open('https://www.all-stars-motorsport.com/admin77500/index.php?controller=AdminModules&token={{Config::get('token')->AdminModules}}&configure=productcomments&tab_module=front_office_features&module_name=productcomments', '_blank')"
                            @endif 
                                style="height: 100px; border-radius: 5px; padding: 5px 0; color: white; 
                                
                                @if( $counter->panel == 'products_pack') background-color: dodgerblue; cursor: pointer; 
                                @elseif( $counter->panel == 'global_discounts') background-color: dodgerblue; cursor: pointer; 
                                @elseif( $counter->panel == 'newsletter_registration') background-color: dodgerblue; cursor: pointer; 
                                @elseif( $counter->counter < 1) background-color: #0BDA51; cursor: default; @else background-color: red; cursor: pointer; @endif
                                
                                ">
                            <div style="font-size: 35px" id="{{$counter->panel}}_quantity">{{$counter->counter}}</div>
                            <div id="{{$counter->panel}}_loading" style="display: none;">
                                <div class="spinner"></div>
                            </div>
    
                            <div style="font-size: 16px; margin: 0 10px;">{{__("dashboard.$counter->name")}}</div>
                        </div>
                        </div>
                        <div id="{{$counter->panel}}" data-open="0" class="panel-body" style="display: none; overflow-x: scroll;"> </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    @if(count($asd) > 0)
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel"> ALL STARS DISTRIBUTION PANEL'S </div>
        </div>
        
        @foreach($asd AS $counter)
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                <div class="panel panel-default" style="display: flow-root">
                        <div class="panel-heading text-center" style="cursor:pointer; text-transform:uppercase;">
                            <div 
                            @if( ( $counter->counter > 0 ) && ( $counter->panel != 'reviews') ) 
                                onclick="getPanelContent('{{$counter->tab}}', '{{$counter->panel}}')" 
                            @elseif( $counter->panel == 'reviews' ) 
                                onclick="window.open('https://www.all-stars-motorsport.com/admin77500/index.php?controller=AdminModules&token={{Config::get('token')->AdminModules}}&configure=productcomments&tab_module=front_office_features&module_name=productcomments', '_blank')"
                            @endif 
                                style="height: 100px; border-radius: 5px; padding: 5px 0; color: white; 
                                
                                @if( $counter->panel == 'products_pack') background-color: dodgerblue; cursor: pointer; 
                                @elseif( $counter->panel == 'global_discounts') background-color: dodgerblue; cursor: pointer; 
                                @elseif( $counter->panel == 'newsletter_registration') background-color: dodgerblue; cursor: pointer; 
                                @elseif( $counter->counter < 1) background-color: #0BDA51; cursor: default; @else background-color: red; cursor: pointer; @endif
                                
                                ">
                            <div style="font-size: 35px" id="{{$counter->panel}}_quantity">{{$counter->counter}}</div>
                            <div id="{{$counter->panel}}_loading" style="display: none;">
                                <div class="spinner"></div>
                            </div>
    
                            <div style="font-size: 16px; margin: 0 10px;">{{__("dashboard.$counter->name")}}</div>
                        </div>
                        </div>
                        <div id="{{$counter->panel}}" data-open="0" class="panel-body" style="display: none; overflow-x: scroll;"> </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
<style>
  .spinner {
    width: 56px;
    height: 56px;
    border: 8px solid #ccc;
    border-top: 8px solid #3498db;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: auto;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }
</style>

<script>
    
    
    function getPanelContent(tab, panel){

        let open = $('#' + panel).attr('data-open');

        if( open == 0){

            $('#'+panel+'_loading').show();
            $('#'+panel+'_quantity').hide();
            
            $.ajax({
                type: 'POST',
                url: "{{route('dashboard.getCountersContent')}}",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    tab: tab,
                    panel: panel
                },
                success: function(response) {
                    
                    console.log(response);
                    
                    if( response.html.update_tag == 1){
                        $('#' + panel + '_quantity').text(response.html.counter);
                    }
                    
                    $('#' + panel).replaceWith(response.html.html);
                    $('#' + panel).attr('data-open', 1);

                    $('#'+panel+'_loading').hide();
                    $('#'+panel+'_quantity').show();
                }     
            });
            
        }else{
            $('#' + panel).toggle();
            $('#' + panel).attr('data-open', 0);
        }
    
    }
    
</script>