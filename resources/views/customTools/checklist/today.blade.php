@extends('layouts.app')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<div class="container">
    <h1 class="my-3">Check Log</h1>
    
    @forelse ($groupedTasks as $deptId => $deptTasks)
        <div class="mb-4">
            @if(auth()->user()->permanent == 1)
            <h3 class="fw-bold border-bottom pb-2 mb-3">
                {{ $deptTasks->first()->department_id == 2 ? 'Logistica' : 'Permanencia' }}
            </h3>
            @endif
    
            @foreach($deptTasks as $task)
                @if($task->active)
                    <div class="card mb-2" style="border-radius: 0;">
                        <div class="card-body" style="{{$task->status === 'done' ? 'background: #ddd; border-radius: 0;' : 'background: #fff;'}}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span>{{ $task->template->title }}</span>
                                    @if($task->employee)
                                        <small class="d-block text-muted">Assigned to: {{ $task->employee->name }}</small>
                                    @endif
                                </div>
                                <div class="form-check d-flex align-items-center">
                                    <input 
                                        class="form-check-input me-2 custom-checkbox task-status-toggle" 
                                        type="checkbox" 
                                        data-task-id="{{ $task->id }}"
                                        onclick="changeStatusItem(this)"
                                        {{ $task->status === 'done' ? 'checked' : '' }}
                                        id="toggle_done_{{ $task->id }}"
                                        
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                @endif
            @endforeach
            
            <div class="form-group mt-2">
                <label for="note_dept_{{ $deptId }}">Department Notes:</label>
                <textarea 
                    class="form-control dept-note" 
                    id="note_dept_{{ $deptId }}" 
                    name="note" 
                    rows="1"
                    data-dept-id="{{ $deptId }}"
                    onblur="saveDeptNote(this)"
                >{{ $deptNotes[$deptId] ?? '' }}</textarea>
            </div>
        </div>
    @empty
        <p>Ainda não tens tarefas para hoje 🎉</p>
    @endforelse
</div>
<style>
    .radio-lg{
        width: 30px;
        height: 30px;
    }
    .ball-info-1{
        display:flex;
        width:18px;
        height:18px;
        background: var(--bs-danger);
        border-radius: 50%;
    }
    .ball-info-2{
        display:flex;
        width:18px;
        height:18px;
        background: var(--bs-warning);
        border-radius: 50%;
    }
    .ball-info-3{
        display:flex;
        width:18px;
        height:18px;
        background: var(--bs-success);
        border-radius: 50%;
    }
    .custom-checkbox {
    width: 26px;
    height: 26px;
    accent-color: #007bff;
    transform: scale(1.1);
    cursor: pointer;
    }
    .custom-checkbox:checked {
        accent-color: #28a745;
    }
    
    .form-check-input {
        --bs-form-check-bg: #f266661c !important;
        outline: 1px solid #f00 !important;
    }
    
    .form-check-input:checked {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        outline: 1px solid #28a745 !important;
    }
</style>

<script>
    function saveDeptNote(textarea) {
        const deptId = textarea.dataset.deptId;
        const note = textarea.value;
    
        fetch("{{ route('checklist.updateNote')}}", {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ note: note, department_id: deptId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Note saved for department ' + deptId);
                textarea.style.backgroundColor = '#e8f5e8';
                setTimeout(() => {
                    textarea.style.backgroundColor = '';
                }, 1500);
    
                // Optional: Refresh metadata via AJAX or just reload page
                // For now, let’s reload the page to show updated “saved by...”
                // location.reload();
            }
        })
        .catch(error => {
            console.error('Error saving note:', error);
            alert('Failed to save note. Please try again.');
        });
    }
    
    
    function changeStatusItem(checkbox) {
        const taskId = checkbox.dataset.taskId;
        const newStatus = checkbox.checked ? 'done' : 'pending'; 
        
        const bodyItemList = checkbox.closest(".card-body") 
    
        fetch(`/customTools/checklist/${taskId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Server error:', response.status, text);
                    throw new Error('Request failed');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Toastify({
                    text: "Task Updated",
                    duration: 3000,
                    gravity: "bottom",
                    position: "right",
                    backgroundColor: "#28a745",
                }).showToast();
                console.log('Status updated for task ' + taskId);
                // Optional: add visual feedback here
                console.log(data.status)
                if(data.status == 'done'){
                    bodyItemList.style.background = "#ddd"
                    bodyItemList.style.borderRadius = 0
                }else{
                    bodyItemList.style.background = "#fff"
                }
                
            } else {
                throw new Error('Update not successful');
            }
        })
        .catch(error => {
            Toastify({
                text: "Task not updated",
                duration: 3000,
                gravity: "bottom",
                position: "right",
                backgroundColor: "#f50000",
            }).showToast();
            console.error('Error:', error);
            // Revert checkbox state
            checkbox.checked = !checkbox.checked;
            alert('Failed to update status. Please try again.');
        });
    }
</script>

@endsection