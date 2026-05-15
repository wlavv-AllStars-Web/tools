@extends('layouts.app')
@section('content')

    @include('customTools.warranties.includes.css')
    @include('customTools.warranties.includes.js')

    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                <div style="display: flex;">
                    <div style="width: 50%; float: left;text-align: center; @if($list == 1) background-color: transparent;  color: #333; @else background-color: dodgerblue;  color: #FFF; @endif padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="window.location.href = '{{route('warranties.index', ['id' => 0] )}}';">OPEN</div>
                    <div style="width: 50%; float: left;text-align: center; @if($list == 0) background-color: transparent;  color: #333; @else background-color: dodgerblue;  color: #FFF; @endif padding: 10px; border: 1px solid #999; font-weight: bolder; cursor: pointer;" onclick="window.location.href = '{{route('warranties.index', ['id' => 1] )}}';">CLOSED</div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
                @include('customTools.warranties.includes.lists.list')
            </div>
        </div>
    </div>    

    <div class="modal fade" id="ajaxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ajaxModalLabel">Carregando...</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center p-4" id="ajaxModalLoader">
                        <div class="spinner-border"></div>
                    </div>
                    <div id="ajaxModalContent" class="d-none"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body d-flex justify-content-center align-items-center">
                    <div id="imagePreviewContent"></div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="ajaxModalSimple" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-body">
                    <form id="returnStatusFormSimple" class="formSubmitSimple" method="POST" action="{{ route('warranties.changeStatus') }}">
                        @csrf
                        <input type="hidden" name="return_type" value="warranty" name="return_type">
                        <input type="hidden" name="returnStatusSelect" value="1" name="return_type">
                        <div id="contentToReplace"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
@endsection