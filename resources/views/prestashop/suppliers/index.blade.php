@extends('layouts.app')
@section('content')
<div class="navbar navbar-light customPanel">
    <ul class="listUL">
        @foreach ($suppliers as $supplier)
            <li class="rowStyling">
                <a href="{{route('suppliers.show', $supplier->id_supplier )}}" title="{{ $supplier->name }}">
                    <div style="display: flex;">
                        <div style="width: 40px;float:left;">{{ $supplier->id_supplier }} - </div> 
                        <div style="width: calc( 100% - 40px );float:left;">{{ $supplier->name }}</div>
                    </div>
                </a>
            </li>
        @endforeach
    </ul>
</div>
@endsection