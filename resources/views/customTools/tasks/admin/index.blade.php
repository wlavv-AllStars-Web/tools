@extends('layouts.app')

@section('content')

<div class="customPanel">
    <form method="get" class="row align-items-end" style="margin: 0;">
        <div class="col-md-2">
            <select name="year" class="form-select">
                @for ($y = now()->year - 3; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="col-md-2">
            <select name="month" class="form-select">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-4">
            <select name="team_id" class="form-select">
                <option value="">All departments</option>
                @foreach($teams as $t)
                    <option value="{{ $t->id }}" {{ (string)$teamId === (string)$t->id ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>
        </div><div class="col-md-3">
        @php
            $monthParam = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->format('Y-m');
        @endphp
        
        <div class="col-md-3">
            <div class="d-flex gap-2">
                <button class="btn btn-primary flex-grow-1">Apply</button>
        
                {{-- Monthly stats (month=YYYY-MM, team=<id>) --}}
                <a class="btn btn-outline-secondary"
                   title="Productivity (month)"
                   href="{{ route('tasks.reports.monthly', ['month' => $monthParam, 'team' => $teamId]) }}">
                    <i class="fa-solid fa-calendar-days"></i>
                </a>
        
                {{-- Annual stats (year=YYYY, team=<id>) --}}
                <a class="btn btn-outline-secondary"
                   title="Productivity (year)"
                   href="{{ route('tasks.reports.annual', ['year' => $year, 'team' => $teamId]) }}">
                    <i class="fa-solid fa-chart-line"></i>
                </a>
            </div>
        </div>

    </div>

    </form>
</div>

<div class="customPanel p-2">
    <div class="table-responsive">
        <table id="tasksTable" class="table table-striped table-hover align-middle w-100">
            <thead>
                {{-- HEADER (sortable) --}}
                <tr id="headerRow">
                    <th style="width:120px;">Task date</th>
                    <th>Task</th>
                    <th style="width:220px;">Team</th>
                    <th style="width:110px;">Time (h)</th>
                    <th style="width:140px;">Status</th>
                    <th style="width:170px;" class="text-center">Notes</th>
                </tr>

                {{-- INSERT ROW (sortable only if click is OUTSIDE interactive controls) --}}
                <tr id="insertRow" class="table-light">
                    <th>
                        <input id="newTaskDate" type="date" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                    </th>

                    <th>
                        <input id="newTitle" class="form-control form-control-sm" placeholder="New task... (Enter to add)">
                    </th>

                    <th>
                        <select id="newTeam" class="form-select form-select-sm">
                            @foreach($teams as $t)
                                <option value="{{ $t->id }}" {{ (string)$teamId === (string)$t->id ? 'selected' : '' }}>
                                    {{ $t->name }}
                                </option>
                            @endforeach
                        </select>
                    </th>

                    <th>
                        <input id="newTime" type="number" min="1" class="form-control form-control-sm" placeholder="h">
                    </th>

                    <th class="text-muted text-uppercase">NEW</th>

                    <th>
                        <div class="d-flex gap-2">
                            <input type="hidden" id="newDesc">

                            <button type="button" id="newDescBtn" class="btn btn-outline-secondary btn-sm flex-grow-1 text-start">
                                description…
                            </button>

                            <button type="button" id="btnAddTask" class="btn btn-success btn-sm">
                                ADD
                            </button>
                        </div>
                    </th>
                </tr>
            </thead>

            <tbody>
                @foreach($tasks as $task)
                    @php
                        $status = $task->status_admin ?? 'new';
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

                    <tr class="{{ $rowClass }}"
                        data-id="{{ $task->id }}"
                        data-field-url="{{ route('tasks.admin.field', $task->id) }}"
                        data-comments-url="{{ route('tasks.admin.comments', $task->id) }}"
                    >
                        <td data-field="task_date" data-type="date" data-value="{{ $task->task_date?->format('Y-m-d') ?? '' }}"> {{ $task->task_date?->format('d/m/Y') ?? '' }} </td>
                        <td data-field="title" data-type="text">{{ $task->title }}</td>
                        <td data-field="id_team" data-type="team" data-value="{{ $task->id_team }}"> {{ $task->team?->name }} </td>
                        <td data-field="time_allowed" data-type="number">{{ $task->time_allowed }}</td>
                        <td data-field="status_admin" data-type="status">{{ strtoupper($status) }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-link p-0 btn-comments" title="Notes / Documents">
                                <i class="fa-regular fa-comment-dots"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>

@endsection

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function(){

    const CSRF = @json(csrf_token());
    const STORE_URL = @json(route('tasks.admin.store'));

    const TEAMS = @json($teams->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])->values());
    const STATUS = @json(\App\Models\modules\tasks\task::STATUS_ADMIN);

    function rowClassByStatus(s){
        s = (s || 'new').toLowerCase();
        if (s === 'done') return 'table-success';
        if (s === 'fail') return 'table-danger';
        if (s === 'pending' || s === 'delayed') return 'table-warning';
        if (s === 'ok') return 'table-info';
        if (s === 'extra' || s === 'hold') return 'table-secondary';
        return '';
    }

    const table = $('#tasksTable').DataTable({
        order: [[0,'desc']],
        pageLength: 25
    });

    // ✅ Não ordenar quando clico em controlos interativos da insertRow
    // (bubbling phase => não mata os handlers dos botões)
    $('#tasksTable thead').on('click mousedown mouseup', '#insertRow input, #insertRow select, #insertRow textarea, #insertRow button, #insertRow a', function(e){
        e.stopPropagation();
    });

    // ---------- DESCRIPTION editor (SweetAlert textarea) ----------
    $('#newDescBtn').on('click', function (e) {
        e.preventDefault();
    
        Swal.fire({
            title: 'Task description',
            input: 'textarea',
            inputValue: $('#newDesc').val() || '',
            inputPlaceholder: 'Write the description here...',
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                const val = (result.value || '').trim();
                $('#newDesc').val(val);
    
                if (val) {
                    $('#newDescBtn').text(`Description (${val.length} chars)`)
                        .removeClass('btn-outline-secondary')
                        .addClass('btn-outline-primary');
                } else {
                    $('#newDescBtn').text('description…')
                        .removeClass('btn-outline-primary')
                        .addClass('btn-outline-secondary');
                }
            }
        });
    });

function badgeClassByStatus(s){
    s = (s || 'new').toLowerCase();
    if (s === 'done' || s === 'ok') return 'bg-success';
    if (s === 'pending' || s === 'delayed') return 'bg-warning text-dark';
    if (s === 'fail') return 'bg-danger';
    if (s === 'extra' || s === 'hold') return 'bg-secondary';
    return 'bg-light text-dark';
}

function renderStatusBadge(label, status){
    const s = (status || 'new').toLowerCase();
    const cls = badgeClassByStatus(s);
    return `<span class="badge ${cls}" title="${escapeHtml(label)} status">${escapeHtml(label)}: ${escapeHtml(s.toUpperCase())}</span>`;
}

    // ---------- CREATE ----------
    function createTask(){
        const title = $('#newTitle').val().trim();
        if (!title) {
            Swal.fire('Missing title', 'Please write a task title.', 'warning');
            return;
        }

        $.ajax({
            url: STORE_URL,
            method: 'POST',
            data: {
                _token: CSRF,
                title: title,
                id_team: $('#newTeam').val(),
                task_date: $('#newTaskDate').val(),
                time_allowed: $('#newTime').val(),
                description: $('#newDesc').val()
            },
            success: function(res){
                if (!res.ok) return;

                const t = res.task;
                const status = (t.status_admin || 'new').toUpperCase();
                const cls = rowClassByStatus(t.status_admin);

                const rowNode = table.row.add([
                    t.task_date_display || '',
                    escapeHtml(t.title),
                    escapeHtml(t.team_name || ''),
                    t.time_allowed || '',
                    status,
                    `<button type="button" class="btn btn-link p-0 btn-comments" title="Notes / Documents">
                        <i class="fa-regular fa-comment-dots"></i>
                     </button>`
                ]).draw(false).node();

                $(rowNode)
                    .addClass(cls)
                    .attr('data-id', t.id)
                    .attr('data-field-url', t.field_url)
                    .attr('data-comments-url', t.comments_url);

                const $tds = $(rowNode).find('td');
                $tds.eq(0).attr({'data-field':'task_date','data-type':'date','data-value': t.task_date || ''}).text(t.task_date_display || '');
                $tds.eq(1).attr({'data-field':'title','data-type':'text'}).text(t.title);
                $tds.eq(2).attr({'data-field':'id_team','data-type':'team','data-value': t.id_team}).text(t.team_name || '');
                $tds.eq(3).attr({'data-field':'time_allowed','data-type':'number'}).text(t.time_allowed || '');
                $tds.eq(4).attr({'data-field':'status_admin','data-type':'status'}).text(status);
                $tds.eq(5).addClass('text-center');

                // limpar inputs (mantém team/date)
                $('#newTitle').val('').focus();
                $('#newTime').val('');

                // reset descrição
                $('#newDesc').val('');
                $('#newDescBtn').text('description…')
                    .removeClass('btn-outline-primary')
                    .addClass('btn-outline-secondary');
            },
            error: function(xhr){
                Swal.fire('Error', xhr.responseJSON?.message || 'Create failed', 'error');
            }
        });
    }

    $('#btnAddTask').on('click', function () { createTask(); });

    $('#newTitle,#newTime,#newTaskDate').on('keydown', function(e){
        if (e.key === 'Enter') {
            e.preventDefault();
            createTask();
        }
    });

    // ---------- INLINE EDIT (double click) ----------
    $('#tasksTable tbody').on('dblclick', 'td[data-field]', function(){
        const td = $(this);
        const tr = td.closest('tr');
        const type = td.data('type');
        const field = td.data('field');

        if (td.find('input,select').length) return;

        const originalText = td.text().trim();
        const originalValue = td.data('value') ?? originalText;

        let input;

        if (type === 'text') {
            input = $('<input class="form-control form-control-sm">').val(originalText);
        } else if (type === 'number') {
            input = $('<input type="number" min="1" class="form-control form-control-sm">').val(originalText);
        } else if (type === 'date') {
            input = $('<input type="date" class="form-control form-control-sm">').val(originalValue || '');
        } else if (type === 'team') {
            input = $('<select class="form-select form-select-sm"></select>');
            TEAMS.forEach(t => input.append(`<option value="${t.id}">${escapeHtml(t.name)}</option>`));
            input.val(String(originalValue));
        } else if (type === 'status') {
            input = $('<select class="form-select form-select-sm"></select>');
            STATUS.forEach(s => input.append(`<option value="${s}">${s.toUpperCase()}</option>`));
            input.val(originalText.toLowerCase());
        } else {
            return;
        }

        td.empty().append(input);
        input.trigger('focus');

        function commit(){
            const newVal = (type === 'team' || type === 'status') ? input.val() : (input.val() ?? '').toString().trim();

            if (field === 'title' && !newVal) {
                td.empty().text(originalText);
                return;
            }

            if (field === 'status_admin' && newVal === 'fail') {
                Swal.fire({
                    title: 'Duplicate task?',
                    text: 'Create a copy for next week?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No'
                }).then(r => {
                    saveField(tr, td, field, newVal, { duplicate_next_week: r.isConfirmed ? 1 : 0 }, originalText, originalValue);
                });
                return;
            }

            saveField(tr, td, field, newVal, {}, originalText, originalValue);
        }

        input.on('blur', commit);
        input.on('keydown', function(e){
            if (e.key === 'Enter') { e.preventDefault(); commit(); }
            if (e.key === 'Escape') { td.empty().text(originalText); }
        });

        if (type === 'team' || type === 'status' || type === 'date') {
            input.on('change', commit);
        }
    });

    function saveField(tr, td, field, value, extra, originalText, originalValue){
        $.ajax({
            url: tr.data('field-url'),
            method: 'POST',
            data: {
                _token: CSRF,
                field: field,
                value: value,
                ...extra
            },
            success: function(res){
                if (!res.ok) throw new Error('not ok');

                if (field === 'id_team') {
                    td.data('value', value);
                    td.empty().text(res.team_name || '');
                }
                else if (field === 'task_date') {
                    td.data('value', value);
                    td.empty().text(res.task_date_display || '');
                }
                else if (field === 'status_admin') {
                    td.empty().text(String(value).toUpperCase());

                    tr.removeClass('table-success table-danger table-warning table-info table-secondary');
                    tr.addClass(rowClassByStatus(value));
                }
                else {
                    td.empty().text(value);
                }

                const row = table.row(tr);
                if (row.child.isShown()) row.child.hide();
            },
            error: function(xhr){
                if (field === 'id_team' || field === 'task_date') {
                    td.data('value', originalValue);
                }
                td.empty().text(originalText);
                Swal.fire('Error', xhr.responseJSON?.message || 'Update failed', 'error');
            }
        });
    }

    // ---------- COMMENTS (DataTables child row — show files + comments) ----------
    $('#tasksTable tbody').on('click', '.btn-comments', function(){
        const tr = $(this).closest('tr');
        const row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            return;
        }

        table.rows().every(function(){
            if (this.child.isShown()) this.child.hide();
        });

        row.child('<div class="p-2 text-muted">Loading...</div>').show();

        $.get(tr.data('comments-url'), function(res){
            const admin = res.comments?.admin || '—';
            const manager = res.comments?.manager || '—';
            const user = res.comments?.user || '—';
const statusUser    = res.status?.user ?? res.task?.status_user ?? null;
const statusManager = res.status?.manager ?? res.task?.status_manager ?? null;
const statusAdmin   = res.status?.admin ?? res.task?.status_admin ?? null;

            const files = (res.files || []);
            const filesHtml = files.length
                ? `<ul class="mb-0" style="padding-left: 0; list-style-type: none;">
                      ${files.map(f => `<li><a href="${f.download_url}">${escapeHtml(f.filename)}</a> <span class="text-muted small">(${formatBytes(f.size)})</span></li>`).join('')}
                   </ul>`
                : `<div class="text-muted small">No files</div>`;

const html = `
    <div class="p-2">

        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <div class="fw-bold me-2">Status</div>
            ${renderStatusBadge('USER', statusUser)}
            ${renderStatusBadge('MANAGER', statusManager)}
            ${renderStatusBadge('ADMIN', statusAdmin)}
        </div>

        <div class="d-flex gap-4 flex-wrap">
            <div style="min-width:260px;">
                <div class="fw-bold text-success">Admin</div>
                <div class="text-success-emphasis">${escapeHtml(admin)}</div>
            </div>

            <div style="min-width:260px;">
                <div class="fw-bold text-primary">Manager</div>
                <div class="text-primary-emphasis">${escapeHtml(manager)}</div>
            </div>

            <div style="min-width:260px;">
                <div class="fw-bold text-warning">User</div>
                <div class="text-warning-emphasis">${escapeHtml(user)}</div>
            </div>
        </div>

        <hr class="my-2">

        <div class="fw-bold">Documents</div>
        ${filesHtml}
    </div>
`;


            row.child(html).show();
        }).fail(function(){
            row.child('<div class="p-2 text-danger">Failed to load notes.</div>').show();
        });
    });

    function formatBytes(bytes) {
        bytes = Number(bytes || 0);
        if (!bytes) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        const val = bytes / Math.pow(k, i);
        return `${val.toFixed(val >= 10 || i === 0 ? 0 : 1)} ${sizes[i]}`;
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

});
</script>
