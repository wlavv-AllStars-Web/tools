@extends('layouts.app')
@section('content')

    <div class="row">
        <div class="col-lg-6">
            <div class="navbar navbar-light customPanel" style="display: flow-root;">
                <div style="text-align: center;font-weight: bolder;">
                    <div style="float: left; width: 50%;" onclick="$('.container_all').css('display', 'none'); $('.container_1').css('display', 'table-row')">
                        <div style="margin: 10px;" class="alert alert-success">
                            <h2 style="font-weight: bolder;">{{$counters->valid}} - VALID</h2>
                        </div>
                    </div>
                    <div style="float: left; width: 50%" onclick="$('.container_all').css('display', 'none'); $('.container_0').css('display', 'table-row')">
                        <div style="margin: 10px;" class="alert alert-danger">
                            <h2 style="font-weight: bolder;">{{$counters->invalid}} - INVALID</h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="navbar navbar-light customPanel" style="margin-top: 10px;">
                <table class="table table-striped" style="text-align: center;">
                    <tr>
                        <td>ID Customer</td>
                        <td>Country code</td>
                        <td>VAT NUMBER</td>
                        <td>Attempts</td>
                        <td>Valid ?</td>
                    </tr>
                    @foreach($verified AS $row)
                        <tr class="container_{{$row->valid}} container_all">
                            <td>{{$row->id_customer}}</td>
                            <td>{{$row->country_code}}</td>
                            <td>{{$row->vat_number}}</td>
                            <td>{{$row->attempts}}</td>
                            <td>@if($row->valid) <i style="color: green;" class="fa-solid fa-check"></i>  @else <i style="color: red;" class="fa-solid fa-xmark"></i> @endif</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>

@endsection