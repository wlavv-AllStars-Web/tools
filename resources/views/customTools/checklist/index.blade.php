@extends('layouts.app')

@section('content')

<div class="container">
    <h1 class="my-3">Gestão de Checklist</h1>
    <div class="d-flex justify-content-between align-items-center">
        <a href="{{ route('checklist.create') }}" class="btn btn-primary mb-3">+ Nova Tarefa</a>
        {{--
        @if(auth()->id() === 43 || auth()->id() === 104 || auth()->id() === 105)
            <a href="{{ route('checklist.assignEmployees') }}" class="btn btn-sm btn-secondary">
                Assign to Admin
            </a>
        @endif
        --}}
    </div>

    @foreach($grouped as $deptId => $deptTemplates)
        <div class="mb-4">
            <div class="d-flex gap-3 mb-2 align-items-center row">
                <h5 class="fw-bold mb-0 col-md-2">
                    {{ $deptTemplates->first()->department_id == 2 ? 'Logistica' : 'Permanência' }}
                </h5>
                <div class="d-flex col-md-3 gap-2 justify-content-start">
                    <a href="{{ route('checklist.history', ['department' => $deptTemplates->first()->department_id]) }}" class="btn btn-sm btn-warning" title="History">
                        <i class="fa-solid fa-clock-rotate-left text-dark"></i>
                    </a>
                    <a class="btn btn-sm btn-info" data-bs-toggle="collapse" 
                       href="#checklist_dept_{{ $deptId }}" 
                       role="button" aria-expanded="false" aria-controls="collapseExample">
                        <i class="fa-solid fa-chevron-down"></i>
                        Show Checklist
                    </a>
                </div>
            </div>

            <ul class="list-group collapse" id="checklist_dept_{{ $deptId }}">
                @foreach($deptTemplates as $template)
                    @php
                        $todayTask = $template->dailyTasks->where('for_date', today())->first();
                    @endphp
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div clas="d-flex">
                            @if($template->active != 1)
                            <span title="Checklist not active"><i class="fa-solid fa-triangle-exclamation text-danger"></i></span>
                            @endif
                            <span>{{ $template->title }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            @if($todayTask)
                                <a href="{{ route('checklist.edit', ['id' => $todayTask->id, 'template' => $template->id]) }}" 
                                   title="Editar" class="btn btn-sm btn-warning">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            @endif

                            <form action="{{ route('checklist.destroy', $template) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" title="Remover" onclick="return confirm('Remover?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
            <hr>
        </div>
    @endforeach

    {{ $templates->links() }}
</div>

<style>
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
</style>
@endsection