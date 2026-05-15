@extends('layouts.app')

@section('content')
    <div class="checklist-page">
        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel checklist-toolbar">
                    <h1 class="checklist-title">TASK HISTORY - {{ $departmentName }}</h1>
                    <a class="checklist-action" href="{{ route('checklist.index') }}">
                        <i class="fa-solid fa-arrow-left"></i> BACK
                    </a>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('checklist.history', ['department' => $departmentId]) }}">
            <div class="row">
                <div class="col-lg-12">
                    <div class="navbar navbar-light customPanel checklist-toolbar">
                        <table class="checklist-form-table">
                            <tr>
                                <td class="left_column"><label for="date">Date</label></td>
                                <td class="right_column">
                                    <input type="date" id="date" name="date" value="{{ $date?->format('Y-m-d') }}">
                                </td>
                                <td style="width: 120px; text-align: right;">
                                    <button class="btn btn-secondary btn-sm">FILTER</button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel checklist-panel">
                    <table class="checklist-list">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Task</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($uniqueTasks as $task)
                                @php
                                    $lastDone = $task->status === 'done' ? $task->updated_at : null;
                                    $lastDone = $lastDone ? \Carbon\Carbon::parse($lastDone) : null;
                                    $sameDay = $lastDone && $task->for_date->isSameDay($lastDone);
                                    $bgClass = $task->status === 'done' && $sameDay ? 'bg-success' : 'bg-warning';
                                @endphp
                                <tr>
                                    <td>{{ $task->for_date->format('d/m/Y') }}</td>
                                    <td>{{ $task->template?->title ?? 'N/A' }}</td>
                                    <td>{{ strtoupper($task->status) }}</td>
                                    <td>{{ $task->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($task->status === 'done')
                                            {{ $lastDone ? $lastDone->format('d/m/Y H:i') : $task->updated_at->format('d/m/Y H:i') }}
                                            <span class="checklist-status-dot {{ $bgClass }}"></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($date && !empty($deptNotes))
            <div class="row">
                <div class="col-lg-12">
                    <div class="navbar navbar-light customPanel checklist-panel">
                        <table class="checklist-list">
                            <thead>
                                <tr>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($deptNotes as $note)
                                    <tr>
                                        <td>{{ $note }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if(method_exists($paginatedTasks, 'links'))
            <div class="row">
                <div class="col-lg-12">
                    <div class="navbar navbar-light customPanel checklist-panel">
                        {{ $paginatedTasks->appends(['date' => $date?->format('Y-m-d')])->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    @include('customTools.checklist.includes.css')
@endsection
