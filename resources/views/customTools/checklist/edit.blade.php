@extends('layouts.app')

@section('content')
    <div class="checklist-page">
        <div class="row">
            <div class="col-lg-12">
                <div class="navbar navbar-light customPanel checklist-toolbar">
                    <h1 class="checklist-title">EDIT CHECKLIST TASK</h1>
                    <a href="{{ route('checklist.index') }}" class="checklist-action">
                        <i class="fa-solid fa-arrow-left"></i> BACK
                    </a>
                </div>
            </div>
        </div>

        <form action="{{ route('checklist.update', $template) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="task_id" value="{{ $task->id }}">

            <div class="row">
                <div class="col-lg-12">
                    <div class="navbar navbar-light customPanel checklist-panel">
                        <table class="checklist-form-table">
                            <tr>
                                <td class="left_column"><label for="department_id">Department</label></td>
                                <td class="right_column">
                                    <select name="department_id" id="department_id" required readonly>
                                        <option value="{{ $department }}" selected>
                                            {{ $department == 0 ? 'PERMANENCIA' : 'LOGISTICA' }}
                                        </option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="left_column"><label for="title">Title</label></td>
                                <td class="right_column">
                                    <input type="text" id="title" name="title" value="{{ $template->title }}" required maxlength="255">
                                </td>
                            </tr>
                            <tr>
                                <td class="left_column"><label for="active">Active</label></td>
                                <td class="right_column">
                                    <input type="checkbox" id="active" name="active" value="1" class="checklist-checkbox" {{ $template->active ? 'checked' : '' }}>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="navbar navbar-light customPanel checklist-toolbar">
                        <button class="btn btn-success">
                            <i class="fa-solid fa-floppy-disk"></i> SAVE
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('customTools.checklist.includes.css')
@endsection
