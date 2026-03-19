@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-3">Editar Template</h1>

    <form action="{{ route('checklist.update', $template) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="">
        <input type="hidden" name="task_id" value="{{ $task->id }}">
        <div class="col-md-12">
            <div class="mb-3 col-md-12">
                <label for="department_id" class="form-label">Department</label>
                <select name="department_id" id="department_id" class="form-select" required readonly>
                        <option value="{{ $department }}" selected }}>
                            {{ $department == 0 ? 'Permanência' : 'Logistica' }}
                        </option>
                </select>
            </div>
            {{--
            <div class="mb-3 col-md-2">
                
                <label for="priority" class="form-label">Prioridade</label>
                <select name="priority" id="priority" class="form-control" required>
                    <option class="option-1" value="1" {{ $task->state_priority == 1 ? 'selected' : '' }}>Alta </option>
                    <option class="option-2" value="2" {{ $task->state_priority == 2 ? 'selected' : '' }}>Média </option>
                    <option class="option-3" value="3" {{ $task->state_priority == 3 ? 'selected' : '' }}>Baixa </option>
                </select>
            </div>
            --}}
        </div>
        {{--
        <div class="form-check mb-3">
            <input type="checkbox" name="main_task" value="1" class="form-check-input" id="main_task">
            <label class="form-check-label" for="main_task">
                Marcar como Tarefa Principal (será carregada todos os dias, mesmo que concluída)
            </label>
        </div>
        --}}
        <div class="mb-3 col-md-12">
            <label for="title" class="form-label">Título</label>
            <input type="text" name="title" class="form-control" value="{{ $template->title }}" required maxlength="255">
        </div>

        {{--
        <div class="mb-3 col-md-12">
            <label for="description" class="form-label">Descrição</label>
            <textarea name="description" class="form-control" style="min-height: 150px">{{ $template->description }}</textarea>
        </div>
        
        <div class="mb-3 col-md-12">
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" class="form-control" style="min-height: 80px">{{ $task->notes }}</textarea>
        </div>
        --}}
        
        <div class="form-check mb-3 col-md-12">
            <input type="checkbox" name="active" value="1" class="form-check-input" {{ $template->active ? 'checked' : '' }}>
            <label class="form-check-label">Ativa</label>
        </div>

        <button class="btn btn-success">Salvar</button>
    </form>
</div>

<style>
    .option-1 {
        background: var(--bs-danger);
        color: #fff;
    }
    .option-2 {
        background: var(--bs-warning);
    }
    .option-3 {
        background: var(--bs-success);
        color: #fff;
    }
</style>
@endsection