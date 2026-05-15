@extends('layouts.app')

@section('content')
    <div class="checklist-page">
        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel checklist-toolbar">
                    <h1 class="checklist-title">CHECKLIST MANAGER</h1>
                    <a href="{{ route('checklist.create') }}" class="checklist-action">
                        <i class="fa-solid fa-plus"></i> NEW TASK
                    </a>
                </div>
            </div>
        </div>

        @foreach($grouped as $deptId => $deptTemplates)
            @php
                $departmentId = $deptTemplates->first()->department_id;
                $departmentName = $departmentId == 2 ? 'LOGISTICA' : 'PERMANENCIA';
            @endphp
            <div class="row">
                <div class="col-lg-12">
                    <div class="navbar navbar-light customPanel checklist-panel">
                        <div class="checklist-tabs">
                            <a href="{{ route('checklist.history', ['department' => $departmentId]) }}" class="checklist-tab" title="History">
                                <i class="fa-solid fa-clock-rotate-left"></i> HISTORY
                            </a>
                            <a class="checklist-tab is-active" data-bs-toggle="collapse"
                               href="#checklist_dept_{{ $deptId }}"
                               role="button" aria-expanded="true" aria-controls="checklist_dept_{{ $deptId }}">
                                {{ $departmentName }}
                            </a>
                        </div>

                        <div class="collapse show" id="checklist_dept_{{ $deptId }}">
                            <table class="checklist-list">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th style="width: 120px; text-align: center;">Status</th>
                                        <th style="width: 120px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deptTemplates as $template)
                                        @php
                                            $todayTask = $template->dailyTasks->where('for_date', today())->first();
                                        @endphp
                                        <tr class="{{ $template->active != 1 ? 'checklist-row-muted' : '' }}">
                                            <td>
                                                @if($template->active != 1)
                                                    <i class="fa-solid fa-triangle-exclamation text-danger" title="Checklist not active"></i>
                                                @endif
                                                {{ $template->title }}
                                            </td>
                                            <td style="text-align: center;">
                                                {{ $template->active == 1 ? 'ACTIVE' : 'INACTIVE' }}
                                            </td>
                                            <td style="text-align: center;">
                                                @if($todayTask)
                                                    <a href="{{ route('checklist.edit', ['id' => $todayTask->id, 'template' => $template->id]) }}"
                                                       title="Edit" class="checklist-icon-link">
                                                        <i class="fa-solid fa-pencil" style="color: orange;font-size: 18px; font-weight: bolder;"></i>
                                                    </a>
                                                @endif

                                                <form action="{{ route('checklist.destroy', $template) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="checklist-icon-button" title="Remove" onclick="return confirm('Remover?')">
                                                        <i class="fa-solid fa-trash" style="color: #dc3545;font-size: 18px; font-weight: bolder;"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel checklist-panel">
                    {{ $templates->links() }}
                </div>
            </div>
        </div>
    </div>

    @include('customTools.checklist.includes.css')
@endsection
