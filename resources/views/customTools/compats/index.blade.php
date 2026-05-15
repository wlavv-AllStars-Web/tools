@extends('layouts.app')
@section('content')

    <div class="row">
        <div class="col-lg-2"></div>
        <div class="col-lg-2"><div class="navbar navbar-light customPanel"><button style="width: 100%;" type="button" class="btn btn-primary"   onclick="listCompats()">    LIST COMPATs    </button> </div> </div>
        <div class="col-lg-2"><div class="navbar navbar-light customPanel"><button style="width: 100%;" type="button" class="btn btn-warning"   onclick="createCompat()">   CREATE COMPATS  </button> </div> </div>
        <div class="col-lg-2"><div class="navbar navbar-light customPanel"><button style="width: 100%;" type="button" class="btn btn-success"   onclick="createOption()">   CREATE OPTION   </button> </div> </div>
        <div class="col-lg-2"><div class="navbar navbar-light customPanel"><button style="width: 100%;" type="button" class="btn btn-danger"    onclick="drawMenu()">       DRAW MENU       </button> </div> </div>
        <div class="col-lg-2"></div>
    </div>
    
    @include("customTools.compats.includes.compatsList", [ 'compats' => $compats ])
    @include("customTools.compats.includes.productsByCompat", [ 'options' => $options ])
    @include("customTools.compats.includes.modals.newRelationShip")
    @include("customTools.compats.includes.modals.editImages")
    
    @include("customTools.compats.includes.css")
    @include("customTools.compats.includes.js")

@endsection