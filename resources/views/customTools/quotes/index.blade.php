@extends('layouts.app')
@section('content')
    <div class="navbar navbar-light customPanel categorList listSelector" style="text-align:center;">
        <div style="overflow-x:auto;">
            <table class="table" style="width:100%; margin-bottom:0;">
                <thead>
                    <tr style="text-align:center;">
                        <th style="min-width:140px;">Brand</th>
                        <th style="min-width:140px;">Referencia</th>
                        <th style="min-width:200px;">Notas (frontoffice)</th>
                        <th style="min-width:110px;">Price</th>
                        <th style="min-width:140px;">Lead</th>
                        <th style="min-width:200px;">Notas (backoffice)</th>
                        <th style="min-width:140px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="text-align:center; vertical-align:middle;">
                        <td><input style="width:100%; text-align:center;" type="text" id="new_brand" placeholder="Brand"></td>
                        <td><input style="width:100%; text-align:center;" type="text" id="new_referencia" placeholder="Referencia"></td>
                        <td><input style="width:100%; text-align:center;" type="text" id="new_notas_front" placeholder="Notas FO"></td>
                        <td><input style="width:100%; text-align:center;" type="number" id="new_price" step="0.01" placeholder="0.00"></td>
                        <td><input style="width:100%; text-align:center;" type="text" id="new_lead" placeholder="Lead"></td>
                        <td><input style="width:100%; text-align:center;" type="text" id="new_notas_back" placeholder="Notas BO"></td>
                        <td style="white-space:nowrap; text-align:center;">
                            <button id="btnCreate" class="btn btn-sm btn-success" type="button"> <i class="fa-solid fa-plus"></i>  </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="navbar navbar-light customPanel categorList listSelector" style="text-align:center;">
        <div style="overflow-x:auto;">
            <table id="quotesTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>Brand</th>
                        <th>Referencia</th>
                        <th>Notas (frontoffice)</th>
                        <th>Price exVAT</th>
                        <th>Lead</th>
                        <th>Notas (backoffice)</th>
                        <th class="actions-col">Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('customTools.quotes.includes.js')
@endsection
