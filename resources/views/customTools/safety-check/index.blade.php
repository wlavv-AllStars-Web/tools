@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-lg-6 col-12 text-center;">
        
        @if(session('success')) <div style="margin-top: 10px;" class="alert alert-success">{{ session('success') }}</div> @endif
        @if(session('error')) <div style="margin-top: 10px;" class="alert alert-danger">{{ session('error') }}</div> @endif
        <div class="navbar navbar-light customPanel" style="margin-top:10px; padding:15px;">
            <form method="POST" action="{{ route('customTools.safety.store') }}">
                @csrf
                <div style="margin-bottom:10px;text-align: center;">
                    <label><b>Equipamento</b></label>
                    <select name="equipment" id="equipment" class="form-control" onchange="updateFields()" style="text-align: center;">
                        <option value="plataforma_a">Plataforma A</option>
                        <option value="plataforma_b">Plataforma B</option>
                        <option value="empilhador">Empilhador</option>
                    </select>
                </div>
                @php
                $fields = [
                    'estado_cabos'=>'Estado Cabos',
                    'estado_geral'=>'Estado Geral',
                    'torre'=>'Torre',
                    'elevacao'=>'Elevação',
                    'direcao'=>'Direção',
                    'travao'=>'Travão',
                    'travao_emergencia'=>'Travão Emergência',
                    'travao_estacionamento'=>'Travão Estacionamento',
                    'comandos'=>'Comandos',
                    'garfos'=>'Garfos',
                    'buzina'=>'Buzina'
                ];
                @endphp
                @foreach($fields as $name=>$label)
                    <div class="field-box" data-field="{{ $name }}" style="padding:20px; 10px ;">
                        <div style="text-align: center; align-items:center;">
                            <span style="font-size:15px;"><b>{{ $label }}</b></span>
                        </div>
                        <div style="display:flex; gap:10px; margin-top:8px;">
                            <button type="button" class="btn btn-success btn-sm w-100" style="height: 70px;" onclick="setVal('{{ $name }}',1,this)"> OK </button>
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="setVal('{{ $name }}',0,this)"> NOK </button>
                        </div>
                        <input type="hidden" name="{{ $name }}" id="{{ $name }}">
                    </div>
                @endforeach
                
                <div style="margin-top:20px;text-align: center;">
                    <label><b>Observações</b></label> <textarea name="observacoes" class="form-control" rows="3"></textarea>
                </div>

                <div style="margin-top:15px;">
                    <button class="btn btn-primary w-100" style="height:50px; font-size:16px;"> Guardar </button>
                </div>
            </form>
        </div>

        <div style="margin-top:10px;">
            <a href="{{ route('customTools.safety.history') }}" 
               class="btn btn-secondary w-100" 
               style="height:45px;">
                Ver Histórico
            </a>
        </div>

    </div>
</div>

<script>
    function setVal(field, val, el){
        document.getElementById(field).value = val;
        let parent = el.closest('.field-box');
        parent.querySelectorAll('button').forEach(btn => { btn.classList.remove('active-btn'); });
        el.classList.add('active-btn');
    }
    
    function updateFields(){
        let eq = document.getElementById('equipment').value;
    
        document.querySelectorAll('.field-box').forEach(el => {
            let field = el.getAttribute('data-field');
    
            let show = true;
    
            if(eq.includes('plataforma')){
                if(['garfos','travao_emergencia','buzina'].includes(field)) show = false;
            }
            el.style.display = show ? 'block' : 'none';
        });
    }
    
    document.addEventListener('DOMContentLoaded', updateFields);
</script>

<style>
    .active-btn { outline: 3px solid #000 !important; }
</style>

@endsection