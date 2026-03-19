<div class="row" style="text-align: center;margin-bottom: 10px;">
    <div class="col-lg-4">
        <div class="navbar navbar-light customPanel categorList">
            <h4>PACKAGE ISSUE</h4>
            <div class="issueBlockContainerDisplay">
                <div> <label>SHOP:</label>
                    @if($issue->shop == 'ASM')  <span style="color: red;">          {{$issue->shop}} </span> @endif
                    @if($issue->shop == 'ASD')  <span style="color: dodgerblue;">   {{$issue->shop}} </span> @endif
                    @if($issue->shop == 'EM')   <span style="color: PURPLE;">       {{$issue->shop}} </span> @endif
                    @if($issue->shop == 'ER')   <span style="color: green;">        {{$issue->shop}} </span> @endif                                            
                </div>
                <div> <label>ORDER:</label>             <span>{{$issue->id_order}}</span>       </div>
                <div> <label>TRACKING:</label>          <span>{{$issue->tracking}}</span>       </div>
                <div> <label>CARRIER:</label>           
                    @if($issue->carrier == 'DPD')   <span style="color: red;">{{$issue->carrier}}</span> @endif
                    @if($issue->carrier == 'Mondial Relay')   <span style="color: dodgerblue;">{{$issue->carrier}}</span> @endif
                    @if($issue->carrier == 'NACEX') <span style="color: orange;">{{$issue->carrier}}</span> @endif
                    @if($issue->carrier == 'TNT')   <span style="color: green;">{{$issue->carrier}}</span> @endif
                    @if($issue->carrier == 'UPS')   <span style="color: black;">{{$issue->carrier}}</span> @endif
                </div>
                <div> <label>COUNTRY:</label>           <span>{{$issue->country}}</span>        </div>
                <div> <label>CUSTOMER CONTACT:</label>  <span>{{$issue->contact_date}}</span>   </div>
                <div> <label>ISSUE:</label>             <span>{{$issue->issue}}</span>          </div>
                <div> <label>DESCRIPTION:</label>       <span>{{$issue->description}}</span>    </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="navbar navbar-light customPanel categorList">
            <h4>CLAIM DETAILS</h4>
            <div class="issueBlockContainerDisplay">
                <div> <label>CLAIM DATE:</label>        <span>{{$issue->claim_date}}</span>     </div>
                <div> <label>DOCS SENT:</label>         <span>@if( strlen($issue->docs_sent) > 0 ) {{$issue->docs_sent}} @else - @endif</span>      </div>
                <div style="display: flex;width: 100%;"></div>
                <div> <label>AMOUNT LOST:</label>       <span>{{number_format($issue->amount_lost, 2, ',', ' ')}} €</span>    </div>
                <div> <label>AMOUNT CLAIMED:</label>    <span>{{number_format($issue->amount_claimed, 2, ',', ' ')}} €</span> </div>
                <div> <label>SHIP CHARGES:</label>      <span>{{number_format($issue->ship_charges, 2, ',', ' ')}} €</span>   </div>
                <div> <label>FILE SET:</label>          <span>@if($issue->file_set == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</span>       </div>
                <div> <label>CLAIM STATUT:</label>      
                    @if($issue->claim_status == 'RECUSADO')     <span style="color: red;">{{$issue->claim_status}}</span> @endif
                    @if($issue->claim_status == 'PENDENTE')     <span style="color: orange;">{{$issue->claim_status}}</span> @endif
                    @if($issue->claim_status == 'RESOLVIDO NATURALMENTE')     <span style="color: orange;">{{$issue->claim_status}}</span> @endif
                    @if($issue->claim_status == 'ACEITE')       <span style="color: green;">{{$issue->claim_status}}</span> @endif
                </div>
                <div> <label>CREDIT NOTE:</label>                  <span>@if(strlen($issue->note) > 0) {{$issue->note}} @else - @endif</span>           </div>
                <div> <label>DELAY:</label>   
                    @if( Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now()) > 59)
                        <span style="color: red;">{{ Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now())}}</span>
                    @elseif( Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now()) > 15)
                        <span style="color: orange;">{{ Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now())}}</span>
                    @else
                        <span style="color: black;">{{ Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now())}}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="navbar navbar-light customPanel categorList">
            <h4>RESOLUTION</h4>
            <div class="issueBlockContainerDisplay" style="display: grid;">
                <div> <label>RESOLUTION CUSTOMER:</label>   <span>{{$issue->resolution}}</span>     </div>
                <div> <label>NEW TRACKING:</label>          <span>{{$issue->new_tracking}}</span>   </div>
            </div>
            <div id="carrierIssuesfiles" style="margin-top: 15px;">
                <div style="margin-bottom: 15px;"><h4>FILES AVAILABLE</h4></div>
                <div style="text-align: center; margin: 5px; padding: 15px; background-color: #eee; border: 1px solid #aaa;display: flow-root;border-radius: 5px;">
                    @if( count(glob(public_path('uploads/carriersIssues/' . $issue->id_issue . '/').'*.*', GLOB_BRACE)) > 0 )
                        @foreach(glob(public_path('uploads/carriersIssues/' . $issue->id_issue . '/').'*.*', GLOB_BRACE) as $file)
                            <div style="float: left; width: 120px; height: 60px;">
                                <a href="https://webtools.all-stars-motorsport.com/uploads/carriersIssues/{{$issue->id_issue}}/{{basename($file)}}" target="_self" title="{{basename($file)}}" download style="padding: 10px;text-decoration: none;text-align: center;"> 
                                    @if(pathinfo($file, PATHINFO_EXTENSION) == 'csv')  
                                        <i class="fa-solid fa-file-csv" style="font-size: 34px;"></i>
                                    @elseif(pathinfo($file, PATHINFO_EXTENSION) == 'xlsx') 
                                        <i class="fa-solid fa-file-excel" style="font-size: 34px;"></i>
                                    @elseif(pathinfo($file, PATHINFO_EXTENSION) == 'pdf')  
                                        <i class="fa-solid fa-file-pdf" style="font-size: 34px;"></i>
                                    @elseif( ( pathinfo($file, PATHINFO_EXTENSION) == 'jpg' ) || ( pathinfo($file, PATHINFO_EXTENSION) == 'jpeg' ) || ( pathinfo($file, PATHINFO_EXTENSION) == 'webp' ) || ( pathinfo($file, PATHINFO_EXTENSION) == 'png' ) )  
                                        <i class="fa-solid fa-file-image" style="font-size: 34px;"></i>
                                    @else
                                        <i class="fa-solid fa-file" style="font-size: 34px;"></i>
                                    @endif
                                </a>
                            </div>
                        @endforeach  
                    @else
                        NO FILES AVAILABLE    
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>