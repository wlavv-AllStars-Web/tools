@if($isAdmin)
    <tr class="create-row" data-create-row="1" data-week="{{ now()->format('W') }}">
        <td class="muted"> 
            <input type="hidden" class="new-input" data-field="id_team" value="{{$teamId}}"> 
            <div class="new-cell" data-field="title" contenteditable="true" placeholder="Title..."></div> 
        </td>
        <td> <div class="new-cell" data-field="comment" contenteditable="true" placeholder="Comment..."></div> </td>
        <td> <input type="date" class="new-input" data-field="task_date"> </td>
        <td> <select class="new-input" data-field="status"> <option value="NEW">NEW</option> </select> </td>
        <td> <input type="text" class="new-input" data-field="time_allowed" placeholder="Hours"> </td>
        <td>
            <div class="row-actions">
                <button type="button" class="icon-btn" title="Create task" data-action="create-task">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M4 7a2 2 0 0 1 2-2h10l4 4v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/>
                        <path stroke="currentColor" stroke-width="2" d="M8 5v6h8V5"/>
                        <path stroke="currentColor" stroke-width="2" d="M8 19h8"/>
                    </svg>
                </button>
            
                <button type="button" class="icon-btn" title="Clear" data-action="clear-create-row">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>
        </td>
    </tr>
@endif