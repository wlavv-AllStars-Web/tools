@extends('layouts.app')

@section('content')

<h2>Editar Slot {{ $item->slot_id }}</h2>

<form method="POST" action="{{ route('marketing.homepage.update', $item->slot_id) }}">
@csrf

<input type="text" name="title_en" value="{{ $item->title_en }}">
<input type="text" name="title_es" value="{{ $item->title_es }}">
<input type="text" name="title_fr" value="{{ $item->title_fr }}">

<input type="text" name="link" value="{{ $item->link }}">

<input type="number" name="icon_type" value="{{ $item->icon_type }}">

<input type="checkbox" name="active" value="1" {{ $item->active ? 'checked' : '' }}>

<button type="submit">Guardar</button>

</form>

@endsection