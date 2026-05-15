@extends('layouts.app')

@section('content')
<div class="navbar navbar-light customPanel">
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Existem erros no formulário:</strong>
            <ul style="margin:8px 0 0 18px;">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('customTools.changesTracker.store') }}" enctype="multipart/form-data">
        @csrf
        @include('customTools.changesTracker.partials.form')
        <div style="display:flex;gap:10px;float: right;">
            <button class="btn btn-primary">Guardar alteração</button>
        </div>
    </form>
</div>
@endsection
