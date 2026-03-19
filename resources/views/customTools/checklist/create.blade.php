@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-3">Criar Nova Tarefa</h1>

    <form action="{{ route('checklist.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="department_id" class="form-label">Departamento</label>
            <select name="department_id" id="department_id" class="form-select" required>
                @foreach ($departments as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
       {{-- 
        <div class="mb-3">
            <label for="priority" class="form-label">Prioridade</label>
            <select name="priority" id="priority" class="form-control" required>
                <option value="3" selected>Baixa</option>
                <option value="2">Média</option>
                <option value="1">Alta</option>
            </select>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" name="main_task" value="1" class="form-check-input" id="main_task">
            <label class="form-check-label" for="main_task">
                Marcar como Tarefa Principal (será carregada todos os dias, mesmo que concluída)
            </label>
        </div>
        --}}
        
        <div class="mb-3">
            <label for="title" class="form-label">Título *</label>
            <input type="text" name="title" class="form-control" required maxlength="255">
        </div>
        
        {{-- 
        <div class="mb-3">
            <label for="description" class="form-label">Descrição *</label>
            <textarea name="description" class="form-control" ></textarea>
        </div>
       
        <div class="mb-3">
            <label for="notes" class="form-label">Notas (opcional)</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>
        --}}
        <div class="form-check mb-3">
            <input type="checkbox" name="active" value="1" class="form-check-input" checked>
            <label class="form-check-label">Ativa</label>
        </div>

        <button class="btn btn-success">Salvar</button>
    </form>
</div>
@endsection