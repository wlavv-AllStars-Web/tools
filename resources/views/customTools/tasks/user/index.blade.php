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
            <div class="col-md-2 d-none">
                <button class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    </div>

    <div class="customPanel" style="margin-bottom: 15px;">
        <div class="table-responsive">
            <table id="tasksTable" class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>DATE</th>
                    <th>TASK</th>
                    <th>STATUS</th>
                    <th>OBS.</th>
                    <th>FILES.</th>
                    <th>TIME ( H )</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($tasks as $task)

                    @php
                        $status = $task->status_user ?? 'new';
                        $rowClass = match ($status) {
                            'pending' => 'table-warning',
                            'done'    => 'table-success',
                            'fail'    => 'table-danger',
                            'delayed' => 'table-warning',
                            'ok'      => 'table-info',
                            'extra'   => 'table-secondary',
                            'hold'    => 'table-secondary',
                            default   => '',
                        };
                    @endphp

                    <tr class="{{ $rowClass }}">
                        <td>{{ $task->task_date?->format('d/m/Y') ?? '' }}</td>
                        <td>{{ $task->title }}</td>
                        <td style="text-align:center;">{{ $task->status_user }}</td>
                        <td style="text-align:center;">
                            @if(strlen((string) $task->observations_admin) > 0)
                                <div style="margin: 5px 0; width: 15px; height: 15px; border-radius: 15px; background-color: green;"></div>
                            @else
                                <div style="margin: 5px 0; width: 15px; height: 15px; border-radius: 15px; background-color: red;"></div>
                            @endif
                        </td>
                            <td style="text-align:center;">
                                @if($task->files->count() > 0)
                                    <div title="Files attached" style="margin: 5px 0; width: 15px; height: 15px; border-radius: 15px; background-color: green;"> </div>
                                @else
                                    <div title="No files" style="margin: 5px 0; width: 15px; height: 15px; border-radius: 15px; background-color: red;"> </div>
                                @endif
                            </td>
                        <td>
                            {{ strlen((string) $task->time_allowed) > 0 ? $task->time_allowed : '-' }}
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('tasks.user.show', $task->id) }}">OPEN</a>
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
        $(function () {
            $('#tasksTable').DataTable({
                pageLength: 25,
                order: [[0, 'desc']], // DATE
                columnDefs: [
                    { orderable: false, searchable: false, targets: [5] },
                    { searchable: false, targets: [3] },
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

        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('filterForm');
            if (!form) return;
    
            form.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', () => {
                    form.submit();
                });
            });
        });
        
    </script>
@endsection
