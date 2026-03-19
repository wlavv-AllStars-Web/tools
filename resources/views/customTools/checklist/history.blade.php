@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex gap-3 align-items-center">
        <a class="btn btn-sm btn-info" href="{{ route('checklist.index') }}"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <h1 class="mb-3 mt-3">Histórico de Tarefas — {{ $departmentName }}</h1>
    </div>

    <form method="GET" action="{{ route('checklist.history', ['department' => $departmentId]) }}" class="mb-3">
        <input type="date" name="date" value="{{ $date?->format('Y-m-d') }}" class="form-control w-auto d-inline">
        <button class="btn btn-secondary btn-sm">Filtrar</button>
    </form>



    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Data</th>
                <th>Tarefa</th>
                <th>Estado</th>
                <th>Criada em</th>
                <th>Concluida</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($uniqueTasks as $task)
                @php
                    // If status is 'done', assume it was "completed" at updated_at
                    $lastDone = $task->status === 'done' ? $task->updated_at : null;
                
                    // Make sure it's a Carbon instance
                    $lastDone = $lastDone ? \Carbon\Carbon::parse($lastDone) : null;
                
                    // Check if it was marked done on the same day as for_date
                    $sameDay = $lastDone && $task->for_date->isSameDay($lastDone);
                
                    $bgClass = '';
                    if ($task->status === 'done' && $sameDay) {
                        $bgClass = 'bg-success text-white';  
                    } else {
                        $bgClass = 'bg-warning text-dark'; 
                    }
                @endphp
                <tr>
                    <td>{{ $task->for_date->format('d/m/Y') }}</td>
                    <td>{{ $task->template?->title ?? 'N/A' }}</td>
                    <td>{{ ucfirst($task->status) }}</td>
                    <td>{{ $task->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($task->status === 'done')
                            <div class="d-flex gap-3 justify-content-start align-items-center">
                                {{ $lastDone ? $lastDone->format('d/m/Y H:i') : $task->updated_at->format('d/m/Y H:i') }}
                                <span class="taks-info {{ $bgClass }}"></span>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    @if($date && !empty($deptNotes))
    <div>
        <span>Notas:</span>
        @foreach ($deptNotes as $note)

            <div class="alert alert-warning" role="alert">
              <span>{{$note}}</span>
            </div>
        @endforeach
    </div>
    @endif

    {{ $paginatedTasks->appends(['date' => $date?->format('Y-m-d')])->links() }}
</div>
<style>
    .taks-info {
        display: flex;
        width: 18px;
        height: 18px;
        border-radius: .25rem;
    }
</style>
@endsection