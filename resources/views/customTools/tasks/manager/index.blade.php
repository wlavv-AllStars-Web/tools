@extends('layouts.app')

@section('content')
    <div class="customPanel">
        <form id="filterForm" method="get" class="row align-items-end g-2" style="margin: 0;">
    
            <div class="col-md-2">
                <select name="year" class="form-select" required>
                    @for ($y = now()->year - 3; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ (int)$year === (int)$y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
    
            <div class="col-md-3">
                <select name="month" class="form-select" required>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (int)$month === (int)$m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
    
            {{-- botão opcional (fallback / acessibilidade) --}}
            <div class="col-md-2 d-none">
                <button class="btn btn-primary w-100">Apply</button>
            </div>
    
        </form>
    </div>


    <div class="customPanel">
        <div class="table-responsive">
            <table id="tasksTable" class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>DATE</th>
                    <th>TASK</th>
                    <th>ASSIGNED</th>
                    <th>USER</th>
                    <th>MANAGER</th>
                    <th>ADMIN</th>
                    <th>OBS.</th>
                    <th>FILES</th>
                    <th>TIME (H)</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($tasks as $task)

                    @php
                        // linha pode ser pintada pelo status do manager
                        $rowClass = match ($task->status_manager) {
                            'pending', 'delayed' => 'table-warning',
                            'done', 'ok'         => 'table-success',
                            'fail'               => 'table-danger',
                            'hold', 'extra'      => 'table-secondary',
                            default              => '',
                        };

                        $badge = function ($status) {
                            return match ($status) {
                                'done', 'ok'         => 'bg-success',
                                'pending', 'delayed' => 'bg-warning text-dark',
                                'fail'               => 'bg-danger',
                                'hold', 'extra'      => 'bg-secondary',
                                default              => 'bg-light text-dark',
                            };
                        };

                        $hasObs   = strlen((string)$task->observations_admin) > 0
                                 || strlen((string)$task->observations_manager) > 0;

                        $hasFiles = $task->relationLoaded('files')
                            ? $task->files->count() > 0
                            : $task->files()->count() > 0;
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td>{{ $task->id }}</td>

                        <td>{{ $task->task_date?->format('d/m/Y') ?? '' }}</td>

                        <td>{{ $task->title }}</td>

                        <td>{{ $task->assignedUser?->name ?? '-' }}</td>

                        <td class="text-center">
                            <span class="badge {{ $badge($task->status_user) }}" style="text-transform: uppercase;">
                                {{ $task->status_user ?? '-' }}
                            </span>
                        </td>

                        {{-- STATUS MANAGER --}}
                        <td class="text-center">
                            <span class="badge {{ $badge($task->status_manager) }}" style="text-transform: uppercase;">
                                {{ $task->status_manager ?? '-' }}
                            </span>
                        </td>

                        {{-- STATUS ADMIN --}}
                        <td class="text-center">
                            <span class="badge {{ $badge($task->status_admin) }}" style="text-transform: uppercase;">
                                {{ $task->status_admin ?? '-' }}
                            </span>
                        </td>

                        {{-- OBS --}}
                        <td class="text-center">
                            @if($hasObs)
                                <span class="badge bg-success">✓</span>
                            @else
                                <span class="badge bg-secondary">–</span>
                            @endif
                        </td>

                        {{-- FILES --}}
                        <td class="text-center">
                            @if($hasFiles)
                                <span class="badge bg-success">{{ $task->files->count() }}</span>
                            @else
                                <span class="badge bg-secondary">0</span>
                            @endif
                        </td>

                        {{-- TIME --}}
                        <td class="text-center">
                            {{ $task->time_allowed ?: '-' }}
                        </td>

                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary"
                               href="{{ route('tasks.manager.show', $task->id) }}">
                                OPEN
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filterForm');
            if (!form) return;
    
            form.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', () => {
                    form.submit();
                });
            });
        });

        $(function () {
            $('#tasksTable').DataTable({
                pageLength: 25,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, searchable: false, targets: [10] },
                    { searchable: false, targets: [7,8] },
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_",
                    info: "Showing _START_ to _END_ of _TOTAL_",
                    paginate: { previous: "Prev", next: "Next" },
                    zeroRecords: "No records found",
                    infoEmpty: "No records available"
                }
            });
        });
    </script>
    
    <style>
        
        .badge{ padding: 7px; }
    </style>
@endsection
