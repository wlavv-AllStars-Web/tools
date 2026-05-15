<div class="navbar navbar-light customPanel categorList {{$listType}}">
    <table class="table table-striped text-center">
        <tr>
            <td style="width: 60px;">SHOP</td>
            <td>ORDER</td>
            <td>TRACKING</td>
            <td>CARRIER</td>
            <td>COUNTRY</td>
            <td>CUSTOMER CONTACT</td>
            <td>ISSUE</td>
            <td>CLAIM DATE</td>
            <td>CLAIM STATUT</td>
            <td>FILE SENT</td>
            @if( $listType == 'carrierIssuesActive')
                <td>DELAY</td>
                <td style="width: 30px;"></td>
                <td style="width: 30px;"></td>
            @elseif($listType == 'carrierIssuesRetention')
                <td style="width: 30px;"></td>
            @endif
        </tr>
        @if( count($carrierIssues) > 0)
            @foreach($carrierIssues AS $issue)
                <tr id="carrierIssueRow_{{$issue->id_issue}}" onclick="showContent({{$issue->id_issue}})">
                    <td>
                        @if($issue->shop == 'ASM')  <span style="color: red;">          {{$issue->shop}} </span> @endif
                        @if($issue->shop == 'ASD')  <span style="color: dodgerblue;">   {{$issue->shop}} </span> @endif
                        @if($issue->shop == 'EM')   <span style="color: purple;">       {{$issue->shop}} </span> @endif
                        @if($issue->shop == 'ER')   <span style="color: green;">        {{$issue->shop}} </span> @endif
                    </td>
                    <td>{{$issue->id_order}}</td>
                    <td>{{$issue->tracking}}</td>
                    <td>           
                        @if($issue->carrier == 'DPD')   <span style="color: red;">{{$issue->carrier}}</span> @endif
                        @if($issue->carrier == 'Mondial Relay')   <span style="color: dodgerblue;">{{$issue->carrier}}</span> @endif
                        @if($issue->carrier == 'NACEX') <span style="color: orange;">{{$issue->carrier}}</span> @endif
                        @if($issue->carrier == 'TNT')   <span style="color: green;">{{$issue->carrier}}</span> @endif
                        @if($issue->carrier == 'UPS')   <span style="color: black;">{{$issue->carrier}}</span> @endif
                    </td>
                    <td>{{$issue->country}}</td>
                    <td>{{$issue->contact_date}}</td>
                    <td>{{$issue->issue}}</td>
                    <td>{{$issue->claim_date}}</td>
                    <td>      
                        @if($issue->claim_status == 'RECUSADO')     <span style="color: red;">{{$issue->claim_status}}</span> @endif
                        @if($issue->claim_status == 'PENDENTE')     <span style="color: orange;">{{$issue->claim_status}}</span> @endif
                        @if($issue->claim_status == 'RESOLVIDO NATURALMENTE') <span style="color: dodgerblue;">{{$issue->claim_status}}</span> @endif
                        @if($issue->claim_status == 'ACEITE')       <span style="color: green;">{{$issue->claim_status}}</span> @endif
                    </td>
                    <td>@if($issue->file_set == 1) <i class="fa-solid fa-check" style="color: green;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                    @if($listType == 'carrierIssuesActive')
                        <td>
                            @if( Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now()) > 59)
                                <span style="color: red;">{{ Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now())}}</span>
                            @elseif( Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now()) > 15)
                                <span style="color: orange;">{{ Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now())}}</span>
                            @else
                                <span style="color: black;">{{ Carbon\Carbon::parse($issue->claim_date)->diffInDays(Carbon\Carbon::now())}}</span>
                            @endif                        
                        </td>
                        <td> <button class="btn btn-light" style="padding: 1px 6px; color: orange;" onclick="editIssue({{$issue->id_issue}})"> <i class="fa-solid fa-pencil"></i> </button> </td>
                        <td> <button class="btn btn-light" style="padding: 1px 6px; color: red;" onclick="removeIssue({{$issue->id_issue}})"> <i class="fa-solid fa-trash"></i> </button> </td>
                    @elseif($listType == 'carrierIssuesRetention')
                        <td> <button class="btn btn-light" style="padding: 1px 6px; color: orange;" onclick="editIssue({{$issue->id_issue}})"> <i class="fa-solid fa-pencil"></i> </button> </td>
                    @endif
                </tr>
                <tr open="close" id="carrierIssue_{{$issue->id_issue}}" class="carrierIssueRow" style="display: none;">
                    <td colspan="13">
                            @include("customTools.carrierIssues.includes.showContent", [ 'issue' => $issue ])
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="13"> NO DATA TO SHOW!</td>
            </tr>
        @endif
    </table>
</div>