@extends('layouts.app')

@section('content')

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<div class="checklist-page">
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel checklist-toolbar">
                <h1 class="checklist-title">CHECK LOG</h1>
                <a href="{{ route('checklist.index') }}" class="checklist-action">
                    <i class="fa-solid fa-list-check"></i> MANAGER
                </a>
            </div>
        </div>
    </div>

    @forelse ($groupedTasks as $deptId => $deptTasks)
        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel checklist-panel">
                    @if(auth()->user()->permanent == 1)
                        <div class="checklist-tabs">
                            <div class="checklist-tab is-active">
                                {{ $deptTasks->first()->department_id == 2 ? 'LOGISTICA' : 'PERMANENCIA' }}
                            </div>
                        </div>
                    @endif

                    <table class="checklist-list">
                        <thead>
                            <tr>
                                <th>Task</th>
                                <th style="width: 220px;">Assigned</th>
                                <th style="width: 90px; text-align: center;">Done</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deptTasks as $task)
                                @if($task->active)
                                    <tr class="{{ $task->status === 'done' ? 'checklist-row-done' : '' }}" data-task-row="{{ $task->id }}">
                                        <td>{{ $task->template?->title ?? 'N/A' }}</td>
                                        <td>{{ $task->employee?->name ?? '-' }}</td>
                                        <td style="text-align: center;">
                                            <input
                                                class="checklist-checkbox task-status-toggle"
                                                type="checkbox"
                                                data-task-id="{{ $task->id }}"
                                                onclick="changeStatusItem(this)"
                                                {{ $task->status === 'done' ? 'checked' : '' }}
                                                id="toggle_done_{{ $task->id }}"
                                            >
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    <table class="checklist-form-table" style="margin-top: 12px;">
                        <tr>
                            <td class="left_column"><label for="note_dept_{{ $deptId }}">Department Notes</label></td>
                            <td class="right_column">
                                <textarea
                                    id="note_dept_{{ $deptId }}"
                                    name="note"
                                    rows="1"
                                    data-dept-id="{{ $deptId }}"
                                    onblur="saveDeptNote(this)"
                                >{{ $deptNotes[$deptId] ?? '' }}</textarea>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel checklist-panel">
                    No tasks for today.
                </div>
            </div>
        </div>
    @endforelse
</div>

@include('customTools.checklist.includes.css')

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
                textarea.style.backgroundColor = '#e8f5e8';
                setTimeout(() => {
                    textarea.style.backgroundColor = '';
                }, 1500);
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
        const row = checkbox.closest('tr');

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

                if(data.status == 'done'){
                    row.classList.add('checklist-row-done');
                }else{
                    row.classList.remove('checklist-row-done');
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
            checkbox.checked = !checkbox.checked;
            alert('Failed to update status. Please try again.');
        });
    }
</script>

@endsection
