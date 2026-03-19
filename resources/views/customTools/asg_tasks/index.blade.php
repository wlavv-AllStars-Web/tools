@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="navbar navbar-light customPanel" style="width:100%; padding:10px 12px;">
    
            @include('customTools.asg_tasks.team_selector')
            
            <hr style="margin: 2px;">
            <form method="GET" action="{{ route('asg_tasks.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;margin-top: 10px;">
                <input type="hidden" name="team" value="{{ $teamId }}">
                
                <div style="width: 450px; margin: 0 auto;">
                    <div style="float: left;margin: 5px; align-items:center; gap:8px;">
                        <select name="year" class="cell-input" style="width:140px;text-align: center;" onchange="this.form.submit()">
                            @for($y = (int)now()->format('Y') - 3; $y <= (int)now()->format('Y') + 1; $y++)
                                <option style="text-align: center;" value="{{ $y }}" @selected((int)$year === $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
            
                    <div style="float: left; margin: 5px; align-items:center; gap:8px;">
                        <select name="month" class="cell-input" style="width:140px;text-align: center;" onchange="this.form.submit()">
                            @php $months = [1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez']; @endphp
                            @foreach($months as $m => $label)
                                <option style="text-align: center;" value="{{ $m }}" @selected((int)$month === (int)$m)> {{ str_pad($m,2,'0',STR_PAD_LEFT) }} - {{ $label }} </option>
                            @endforeach
                        </select>
                    </div>
            
                    <div style="float: left; margin: 5px; align-items:center; gap:8px;">
                        <select name="week" class="cell-input" style="width:140px;text-align: center;" onchange="this.form.submit()">
                            <option style="text-align: center;" value="" @selected($week === null)>Todas</option>
                                @foreach($availableWeeks as $w)
                                    <option style="text-align: center;" value="{{ $w }}" @selected((int)$week === (int)$w)>Week - {{ $w }}</option>
                                @endforeach
                        </select>
                    </div>
            
                    <div style="margin-left:auto;gap:8px;display: none;M">
                        <a class="icon-btn" title="Mês atual" href="{{ route('asg_tasks.index', ['team'=>$teamId,'year'=>now()->format('Y'),'month'=>now()->format('n')]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"/>
                                <path stroke="currentColor" stroke-width="2" d="M8 3v4M16 3v4M4 11h16"/>
                            </svg>
                        </a>
            
                        <a class="icon-btn" title="Limpar semana" href="{{ route('asg_tasks.index', ['team'=>$teamId,'year'=>$year,'month'=>$month]) }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" d="M6 6l12 12M18 6 6 18"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@if($isAdmin)
    <div class="row" style="margin-top: 5px;">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel" style="width:100%; padding:0;">
                <div class="overflow-auto" style="width:100%;">
                    <table class="sheet-table" style="width:100%; border-collapse:separate; border-spacing:0;">
                        <tbody>
                            @include('customTools.asg_tasks.new_task')
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row" style="margin-top: 5px;">
    <div class="col-lg-12">
        <div class="navbar navbar-light customPanel" style="width:100%; padding:0;">
            <div class="overflow-auto" style="width:100%;">
                <table class="sheet-table" style="width:100%; border-collapse:separate; border-spacing:0;">
                    <thead class="sheet-thead">
                        <tr>
                            <th style="width:160px;">Task date</th>
                            <th style="min-width:320px;">Title</th>
                            <th style="min-width:220px;">Comment</th>
                            <th style="width:150px;">Status</th>
                            <th style="min-width:200px;">Description</th>
                            <th style="width:100px;">Time allowed</th>
                            <th style="width:50px;"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($weeksToRender as $week)
                            @php $tasks = $tasksByWeek->get($week, collect()); @endphp

                            <tr class="week-row" data-week="{{ $week }}">
                                <td colspan="8" class="week-cell" style="padding:2px;">
                                    <div class="week-cell-inner">
                                        <div class="week-title">WEEK {{ $week }}</div>
                                        <div class="week-meta">{{ $tasks->count() }} tasks</div>
                                    </div>
                                </td>
                            </tr>

                            @foreach($tasks as $t)
                                <tr class="task-row" data-task-id="{{ $t->id }}">
                                    <td>
                                        @if($isAdmin)
                                            <input type="date" class="cell-input" data-field="task_date" value="{{ optional($t->task_date)->format('Y-m-d') }}">
                                        @else
                                            <span class="cell-readonly">{{ optional($t->task_date)->format('Y-m-d') }}</span>
                                        @endif
                                    </td>                                    <td> <div class="cell {{ $isAdmin ? 'editable' : 'readonly' }}" data-field="title" data-editable="{{ $isAdmin ? '1' : '0' }}" contenteditable="{{ $isAdmin ? 'true' : 'false' }}">{{ $t->title }}</div> </td>
                                    <td> <div class="cell {{ $isAdmin ? 'editable' : 'readonly' }}" data-field="comment" data-editable="{{ $isAdmin ? '1' : '0' }}" contenteditable="{{ $isAdmin ? 'true' : 'false' }}">{{ $t->comment }}</div> </td>
                                    <td>
                                        <select class="cell-input" data-field="status">
                                            @php $allStatuses = array_merge($adminStatuses, $userStatuses); @endphp
                                    
                                            @foreach($allStatuses as $st)
                                                @php $isRestricted = !$isAdmin && in_array($st, $adminStatuses); @endphp
                                                <option value="{{ $st }}" @selected($t->status === $st) @disabled($isRestricted) >{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        @if(!$isAdmin)
                                            <div class="cell editable" data-field="description" data-editable="1" contenteditable="true">{{ $t->description }}</div>
                                        @else
                                            @if( strlen($t->description) > 0 )
                                                {{ $t->description }}
                                            @else
                                                -
                                            @endif
                                            
                                        @endif
                                    </td>
                                    <td>
                                        @if($isAdmin)
                                            <input type="number" min="1" class="cell-input" data-field="time_allowed" value="{{ $t->time_allowed }}">
                                        @else
                                            <span class="cell-readonly">{{ $t->time_allowed ? $t->time_allowed.' min' : '-' }}</span>
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="8" style="padding:14px; opacity:.7;">No weeks available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast hidden"></div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.__ASG_TASKS_INLINE_URL = @json(route('asg_tasks.inline', ['task' => 0]));
    window.__ASG_TASKS_STORE_URL  = @json(route('asg_tasks.store'));
    window.__ASG_TASKS_TEAM_ID    = @json($teamId);
</script>

<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // ===== Status color helpers (token-safe) =====
  const statusToToken = (status) => {
    const s = (status || '').trim();
    return 'status-' + s
      .replaceAll(' ', '__')
      .replaceAll('!', '_BANG_');
  };

  const applyRowStatusToken = (tr, status) => {
    if (!tr) return;
    if (tr.classList.contains('week-row')) return;

    [...tr.classList].forEach(c => {
      if (c.startsWith('status-')) tr.classList.remove(c);
    });

    const token = statusToToken(status);
    tr.classList.add(token);
  };

  const toast = (msg, ok=true) => {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.classList.remove('hidden');
    el.classList.toggle('toast-ok', !!ok);
    el.classList.toggle('toast-bad', !ok);
    clearTimeout(el.__t);
    el.__t = setTimeout(()=> el.classList.add('hidden'), 1400);
  };

  const patch = async (taskId, field, value) => {
    const url = window.__ASG_TASKS_INLINE_URL.replace(/\/0\/inline$/, `/${taskId}/inline`);
    const res = await fetch(url, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ field, value })
    });

    if (!res.ok) {
      let err = 'Save failed';
      try { err = (await res.json()).error ?? err; } catch(e){}
      throw new Error(err);
    }
  };

  // ===== Paint initial rows =====
  document.querySelectorAll('tr[data-task-id]').forEach(tr => {
    const sel = tr.querySelector('select.cell-input[data-field="status"]');
    const st = sel ? sel.value : '';
    applyRowStatusToken(tr, st);
  });

  document.querySelectorAll('tr[data-create-row="1"]').forEach(tr => {
    const sel = tr.querySelector('select.new-input[data-field="status"]');
    const st = sel ? sel.value : 'NEW';
    applyRowStatusToken(tr, st);
  });

  // ===== Inline edit (contenteditable) =====
  document.querySelectorAll('.cell[data-editable="1"]').forEach(cell => {
    let last = cell.textContent;

    const save = async () => {
      const tr = cell.closest('tr[data-task-id]');
      if (!tr) return;
      const taskId = tr.getAttribute('data-task-id');
      const field  = cell.getAttribute('data-field');
      const value  = (cell.textContent || '').trim();

      if (value === (last ?? '')) return;

      try {
        await patch(taskId, field, value === '' ? null : value);
        last = value;
        toast('Saved', true);
      } catch (e) {
        cell.textContent = last ?? '';
        toast(e.message || 'Save failed', false);
      }
    };

    cell.addEventListener('blur', save);
    cell.addEventListener('keydown', (ev) => {
      if (ev.key === 'Enter') { ev.preventDefault(); cell.blur(); }
      if (ev.key === 'Escape') { ev.preventDefault(); cell.textContent = last ?? ''; cell.blur(); }
    });
  });

  // ===== Inline edit (inputs/selects) =====
  document.querySelectorAll('.cell-input').forEach(inp => {
    inp.addEventListener('change', async () => {
      const tr = inp.closest('tr[data-task-id]');
      if (!tr) return;

      const taskId = tr.getAttribute('data-task-id');
      const field  = inp.getAttribute('data-field');
      const value  = (inp.value || '').trim() === '' ? null : inp.value;

      try {
        await patch(taskId, field, value);

        // ✅ update row color immediately when status changes
        if (field === 'status') applyRowStatusToken(tr, value);

        toast('Saved', true);
      } catch (e) {
        toast(e.message || 'Save failed', false);
      }
    });
  });

  // ===== Create row =====
  const clearCreateRow = (row) => {
    row.querySelectorAll('.new-cell').forEach(el => el.textContent = '');
    row.querySelectorAll('.new-input').forEach(el => el.value = '');
    const st = row.querySelector('.new-input[data-field="status"]');
    if (st) st.value = 'NEW';

    // ✅ repaint create row
    applyRowStatusToken(row, 'NEW');
  };

  const createFromRow = async (row) => {
    const week = parseInt(row.getAttribute('data-week'), 10);

    const getCellText = (field) => {
      const el = row.querySelector(`.new-cell[data-field="${field}"]`);
      if (!el) return null;
      const v = (el.textContent || '').trim();
      return v === '' ? null : v;
    };

    const getInputVal = (field) => {
      const el = row.querySelector(`.new-input[data-field="${field}"]`);
      if (!el) return null;
      const v = (el.value || '').trim();
      return v === '' ? null : v;
    };

    const payload = {
      title: getCellText('title'),
      comment: getCellText('comment'),
      description: getCellText('description'),
      task_date: getInputVal('task_date'),
      status: getInputVal('status') || 'NEW',
      time_allowed: getInputVal('time_allowed'),
      id_week: week,
      id_team: window.__ASG_TASKS_TEAM_ID
    };

    if (!payload.title) {
      toast('Title is required', false);
      return;
    }

    if (payload.time_allowed !== null) {
      payload.time_allowed = parseInt(payload.time_allowed, 10);
      if (Number.isNaN(payload.time_allowed)) payload.time_allowed = null;
    }

    const res = await fetch(window.__ASG_TASKS_STORE_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload)
    });

    if (!res.ok) {
      let err = 'Create failed';
      try {
        const j = await res.json();
        err = j.message ?? j.error ?? err;
      } catch(e) {}
      toast(err, false);
      return;
    }

    toast('Task created', true);
    window.location.reload();
  };

  // ✅ update create-row color when selecting status
  document.querySelectorAll('tr[data-create-row="1"] select.new-input[data-field="status"]').forEach(sel => {
    sel.addEventListener('change', () => {
      const tr = sel.closest('tr[data-create-row="1"]');
      if (!tr) return;
      applyRowStatusToken(tr, sel.value);
    });
  });

  document.addEventListener('click', async (ev) => {
    const btn = ev.target.closest('button[data-action]');
    if (!btn) return;

    const action = btn.getAttribute('data-action');
    const row = btn.closest('tr[data-create-row="1"]');
    if (!row) return;

    ev.preventDefault();

    if (action === 'clear-create-row') {
      clearCreateRow(row);
      toast('Cleared', true);
      return;
    }

    if (action === 'create-task') {
      await createFromRow(row);
      return;
    }
  });

  // Enter on Title creates
  document.querySelectorAll('tr[data-create-row="1"] .new-cell[data-field="title"]').forEach(titleCell => {
    titleCell.addEventListener('keydown', (ev) => {
      if (ev.key === 'Enter') {
        ev.preventDefault();
        const row = titleCell.closest('tr[data-create-row="1"]');
        if (row) createFromRow(row);
      }
    });
  });
})();
</script>

<style>
  /* Spreadsheet feel */
  .sheet-table thead th{
    position: sticky;
    top: 0;
    z-index: 5;
    background: #fff;
    padding: 10px 10px;
    font-weight: 700;
    border-bottom: 1px solid rgba(0,0,0,.12);
    white-space: nowrap;
  }

  .sheet-table tbody td{
    padding: 8px 10px;
    border-bottom: 1px solid rgba(0,0,0,.07);
    vertical-align: top;
    background: #fff;
  }

  .task-row:hover td { background: rgba(0,0,0,.02); }

  /* Week separator row */
  .week-row td.week-cell{
    padding: 10px 10px;
    background: rgba(0,0,0,.06);
    border-top: 2px solid rgba(0,0,0,.12);
    border-bottom: 1px solid rgba(0,0,0,.12);
  }
  .week-cell-inner{
    display:flex;
    align-items:center;
    justify-content:space-between;
    font-weight: 800;
    letter-spacing: .4px;
  }
  .week-title{ font-size: 13px; }
  .week-meta{ font-size: 12px; opacity: .7; font-weight: 600; }

  /* Cells */
  .cell{
    border: 1px solid transparent;
    border-radius: 10px;
    padding: 6px 8px;
    min-height: 34px;
    white-space: pre-wrap;
  }
  .cell.editable:hover{ border-color: rgba(0,0,0,.18); }
  .cell.editable:focus{
    outline:none;
    border-color: rgba(59,130,246,.9);
    box-shadow: 0 0 0 3px rgba(59,130,246,.18);
    background: rgba(59,130,246,.04);
  }
  .cell.readonly{ opacity:.85; }

  .cell-readonly{ opacity:.85; display:inline-block; padding: 6px 0; }

  .cell-input{
    width: 100%;
    border: 1px solid rgba(0,0,0,.12);
    border-radius: 10px;
    padding: 6px 8px;
    background: #fff;
    min-height: 34px;
  }
  .cell-input:focus{
    outline:none;
    border-color: rgba(59,130,246,.9);
    box-shadow: 0 0 0 3px rgba(59,130,246,.18);
  }

  /* Icons */
  .icon-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:34px;
    height:34px;
    border:1px solid rgba(0,0,0,.12);
    border-radius:12px;
    background:#fff;
    cursor:pointer;
  }
  .icon-btn:hover{ background:rgba(0,0,0,.04); }
  .icon-btn:active{ transform:translateY(1px); }

  /* Toast */
  .toast{
    position:fixed;
    bottom:16px;
    right:16px;
    padding:10px 12px;
    border-radius:12px;
    color:#fff;
    font-size:13px;
    box-shadow:0 10px 30px rgba(0,0,0,.15);
    z-index:9999;
  }
  .toast-ok{ background:#16a34a; }
  .toast-bad{ background:#dc2626; }
  .hidden{ display:none; }

  .sheet-table tbody td{ padding: 5px; }

/* ===== Sheet-table input look (force spreadsheet style) ===== */
.sheet-table .cell-input,
.sheet-table .new-input{
  width: 100%;
  border: 1px solid rgba(0,0,0,.18) !important;
  border-radius: 10px !important;
  padding: 6px 8px !important;
  background: #fff !important;
  min-height: 34px !important;
  height: 34px !important;
  line-height: 20px !important;
  box-shadow: none !important;
  outline: none !important;
}

/* contenteditable cells inside table */
.sheet-table .cell,
.sheet-table .new-cell{
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 6px 8px !important;
  min-height: 34px !important;
  line-height: 20px;
}

/* hover/focus feel like excel */
.sheet-table .cell.editable:hover{
  border-color: rgba(0,0,0,.22) !important;
}
.sheet-table .cell.editable:focus,
.sheet-table .new-cell:focus,
.sheet-table .cell-input:focus,
.sheet-table .new-input:focus{
  border-color: rgba(59,130,246,.95) !important;
  box-shadow: 0 0 0 3px rgba(59,130,246,.18) !important;
}

.sheet-table .cell-input,
.sheet-table .new-input{
  height: 30px !important;
  min-height: 30px !important;
  padding: 4px 8px !important;
}
.sheet-table .cell,
.sheet-table .new-cell{
  min-height: 30px !important;
  padding: 4px 8px !important;
}

/* tighten rows but keep inputs usable */
.sheet-table tbody td{
  padding: 4px 6px !important;
  vertical-align: middle;
}

/* Keep placeholder visible for contenteditable */
.sheet-table .new-cell[placeholder]:empty:before{
  content: attr(placeholder);
  color: rgba(0,0,0,.35);
}

/* For dark status rows, keep inputs readable */
.sheet-table tr.status-FAIL .cell-input,
.sheet-table tr.status-FAIL .new-input,
.sheet-table tr.status-OK .cell-input,
.sheet-table tr.status-OK .new-input,
.sheet-table tr.status-DONE_BANG_ .cell-input,
.sheet-table tr.status-DONE_BANG_ .new-input,
.sheet-table tr.status-WAITING__INFO .cell-input,
.sheet-table tr.status-WAITING__INFO .new-input{
  background: rgba(255,255,255,.92) !important;
  color: #111 !important;
}

  /* Row coloring by status (token-safe) */
  tr.status-NEW td { background:#FFF !important; }
  tr.status-DELAYED td { background:#FCE4D6 !important; }
  tr.status-FAIL td { background:#FF0000 !important; color:#fff !important; }
  tr.status-OK td { background:#70AD47 !important; color:#fff !important; }
  tr.status-RE-TASK td { background:#D9D9D9 !important; }
  tr.status-PENDING td { background:#FFE800 !important; }
  tr.status-DONE_BANG_ td { background:#62CE3D !important; color:#fff !important; }
  tr.status-WAITING__INFO td { background:#7641E0 !important; color:#fff !important; }

  /* Keep week separator style stronger than status rows */
  tr.week-row td.week-cell { background: rgba(0,0,0,.06) !important; color: inherit !important; }

  /* Ensure inputs/selects readable on dark rows */
  tr.status-FAIL input, tr.status-FAIL select,
  tr.status-OK input, tr.status-OK select,
  tr.status-DONE_BANG_ input, tr.status-DONE_BANG_ select,
  tr.status-WAITING__INFO input, tr.status-WAITING__INFO select{
    background: rgba(255,255,255,.92) !important;
    color: #111 !important;
  }

.team-selector .dept-pill{
  display: grid !important;
  place-items: center;
  text-align: center;
  border: 1px solid rgba(0,0,0,.12);
  border-radius: 12px;
  background: #fff;
  text-decoration: none;
  color: inherit;
  padding: 8px 6px;
}

.team-selector .dept-pill:hover{
  background: rgba(0,0,0,.03);
}

.team-selector .dept-pill-active{
  background: dodgerblue;
  color: #000;
}

.team-selector .dept-icon{
  display: flex;
  justify-content: center;
  align-items: center;
}

.team-selector .dept-label{
  font-size: 12px;
  font-weight: 600;
  letter-spacing: .2px;
  margin-top: 4px;
}
</style>

@endsection