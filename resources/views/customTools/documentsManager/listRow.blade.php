<div class="searchList">
    @if( count($documents) )
        <div class="row">
            <div class="col-lg-4">
                <div class="navbar navbar-light customPanel">
                    @include("customTools.documentsManager.listSearch", ['documents' => $documents, 'searchName' => ( isset($searchName) ) ? $searchName : '', 'searchNumber' => ( isset($searchNumber) ) ? $searchName : '', 'searchCategory' => ( isset($searchCategory) ) ? $searchCategory : '', 'searchDate' => ( isset($searchDate) ) ? $searchDate : '' ])
                </div>            
            </div>
            <div class="col-lg-8">
                <div class="navbar navbar-light customPanel" id="loadFile">
                    <div class="alert alert-warning" role="alert"> PLEASE SELECT A DOCUMENT</div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-warning" role="alert" style="margin-bottom: 0;"> NO FILES AVAILABLE</div>
    @endif
</div>