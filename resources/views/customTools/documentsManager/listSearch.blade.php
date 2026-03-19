
<table class="table table-striped text-center table-hover" style="width: 100%;" id="searchListContainer">
<tr>
    <td>DOC. NAME</td>
    <td>DOC. #</td>
    <td>CATEGORY</td>
    <td>DATE</td>
    <td></td>
</tr>   
<tr>
    <td><input style="max-width: 90px; padding: 5px;text-align: center;" type="text" name="searchName"        id="searchName"     @if(strlen($searchName) > 0)        value="{{$searchName}}"     @else value="" @endif></td>
    <td><input style="max-width: 90px; padding: 5px;text-align: center;" type="text" name="searchNumber"      id="searchNumber"   @if(strlen($searchNumber) > 0)      value="{{$searchNumber}}"   @else value="" @endif></td>
    <td><input style="max-width: 90px; padding: 5px;text-align: center;" type="text" name="searchCategory"    id="searchCategory" @if(strlen($searchCategory) > 0)    value="{{$searchCategory}}" @else value="" @endif></td>
    <td><input style="max-width: 90px; padding: 5px;text-align: center;" type="text" name="searchDate"        id="searchDate"     @if(strlen($searchDate) > 0)        value="{{$searchDate}}"     @else value="" @endif></td>
    <td> <button class="btn btn-light" style="border: 1px solid #999;" onclick="listSearch()"> <i style="color: orange;" class="fa-solid fa-magnifying-glass"></i> </button> </button> </td>
</tr>    
@if(count($documents) > 0)
    @foreach($documents AS $document)
        <tr class="searchRows" id="row_{{$document->id_document}}">
            <td onclick="loadDocument('{{$document->id_document}}')" style="padding: 15px 0;"> <div> {{$document->name}} </div> </td>
            <td onclick="loadDocument('{{$document->id_document}}')" style="padding: 15px 0;"> <div> {{$document->document_number}} </div> </td>
            <td onclick="loadDocument('{{$document->id_document}}')" style="padding: 15px 0;"> <div> {{$document->category}} </div> </td>
            <td onclick="loadDocument('{{$document->id_document}}')" style="padding: 15px 0;"> <div>{{$document->year}}-{{$document->month}}-{{$document->day}} </div> </td>
            <td> <button class="btn btn-light" onclick="deleteDocument('{{$document->id_document}}');" style="border: 1px solid #999;"> <i style="color: red;" class="fa-solid fa-trash"></i> </button></td>
        </tr> 
    @endforeach
@else
    <tr class="searchRows">
        <td colspan="5" style="text-align: center;"> <div class="alert alert-danger" role="alert"> NO DOCUMENTS FOUND! </div> </td>
    </tr> 
@endif
    <tr class="waitWhileSearch" style="display: none;">
        <td colspan="5" style="text-align: center;"> <div class="alert alert-warning" role="alert"> PLEASE WAIT WHILE IS SEARCHING! </div> </td>
    </tr> 
</table>

<style>

    .tdStyle{  }
    
    .outer-wrapper {
        overflow: hidden;
        height: 1.2em;
        line-height: 1.2em;
        border: 1px dotted black;
        margin: 1em;
    }
    .outer-wrapper::before {
        content: '';
        display: inline-block;
    }
    .inner-wrapper {
        display: inline-block;
        white-space: nowrap;
    }

</style>