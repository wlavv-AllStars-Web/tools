@extends('layouts.app')
@section('content')

<div class="navbar navbar-light customPanel sortable-brands">
    @foreach($structure as $brand)
        <div class="brand-block" style="margin: 0 auto;border:1px solid #ddd;float: left; width: calc( 100% / {{count($structure)}});text-align: center;cursor: move;" 
             data-id="{{ $brand->id }}"
             data-row="{{ $brand->row }}"
             data-position="{{ $brand->position }}"
             data-idcompat="{{ $brand->id_compat }}">
             
            <div style="text-align: center;" onclick="openContent({{ $brand->id }})">
                <img class="images" id="image_{{ $brand->id }}" src="https://webtools.all-stars-motorsport.com/uploads/compats/brand/{{ $brand->id }}.png?t=2961" style="width: 50px;background-color: #333;margin-top: 10px;">  
                <div class="model-header">{{ $brand->name }}</div>
                <div style="border: 1px solid #999; height:30px;background-color: #ddd;text-align: center;">
                    <span><i class="fa-solid fa-arrows-up-down-left-right"></i></span>
                </div>
            </div>
        </div>
    @endforeach
</div>


    @foreach($structure as $brand)
    <div class="all_brands" style="display: none;" id="brand_{{$brand->id}}" >
        <div class="brand-block" style="margin: 0 auto;">
            <ul class="sortable-models" style="display: grid;" data-brand-id="{{ $brand->id }}">
                @foreach($brand->models as $model)
                    <li class="model-item navbar navbar-light customPanel " data-id="{{ $model->id }}" data-row="{{$model->position}}" data-position="{{$model->row}}" data-idCompat="{{$model->id_compat}}" style="padding: 0;">
                        <div style="border: 1px solid #999; height:30px;background-color: #ddd;">
                            <span style="float: right;padding: 4px 10px;"><i class="fa-solid fa-arrows-up-down-left-right"></i></span>
                        </div>
                        <div style="padding: 10px 0px;">
                            <ul class="sortable-types" data-model-id="{{ $model->id }}">
                                @foreach($model->types as $type)
                                    <li class="type-item" data-id="{{ $type->id }}" data-idBrand="{{$brand->id}}" data-idModel="{{$model->id}}" data-idType="{{$type->id}}" data-row="{{$type->row}}" data-position="{{$type->position}}" data-idCompat="{{$type->id_compat}}" style="float:left;width: calc( 100% / {{count($model->types)}} ); @if( count($model->types) > 1 ) border: 1px solid #ddd; @endif; padding: 0;">
                                        @if( count($model->types) > 1 )
                                        <div style="border: 1px solid #999; height:30px;background-color: #ddd;">
                                            <span style="float: right;padding: 4px 10px;"><i class="fa-solid fa-arrows-up-down-left-right"></i></span>
                                        </div>
                                        @endif
                                        <ul class="sortable-versions" data-type-id="{{ $type->id }}">
                                            @foreach($type->versions as $version)
                                                <li class="version-item" data-id="{{ $version->id }}" data-row="{{$version->row}}" data-position="{{$version->position}}" data-idCompat="{{$version->id_compat}}" style="float:left;width: calc( 100% / {{count($type->versions)}} ); @if( count($type->versions) > 1 ) border: 1px solid #ddd; @endif; padding: 0; text-align: center;">
                                                    @if( count($type->versions) > 1 )
                                                    <div style="border: 1px solid #999; height:30px;background-color: #ddd;">
                                                        <span style="float: right;padding: 4px 10px;"><i class="fa-solid fa-arrows-up-down-left-right"></i></span>
                                                    </div>
                                                    @endif
                                                    <img class="images_1" id="image_{{$version->id}}" src="https://webtools.all-stars-motorsport.com/uploads/compats/compat/{{$version->id_compat}}.png?t=4880" style="padding: 30px 0px;width: 200px;background: #ccc;margin-top: 10px;border: 1px solid #333;border-radius: 5px;">  
                                                    <div class="model-header">{{ $model->name }} | {{ $type->name }} | {{ $version->name }}</div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endforeach

<meta name="csrf-token" content="{{ csrf_token() }}">


<style>
  ul { list-style: none; padding-left: 0px; }

  .model-item, .type-item, .version-item {
    background: #fff;
    //border: 1px solid #ccc;
    margin-bottom: 6px;
    padding: 6px 12px;
    cursor: move;
  }
  
  .model-header, .type-header {
    font-weight: bold;
    margin-bottom: 4px;
    margin-top: 10px;
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>

    function openContent(id_brand){
        
        $('.all_brands').css('display', 'none');
        $('#brand_' + id_brand).css('display', 'block');
        
        $('.images').css('background-color', '#333');
        $('#image_'+id_brand).css('background-color', '#3da936');
        
    }

    new Sortable(document.querySelector('.sortable-brands'), {
        animation: 150,
        onEnd: function (evt) {
            const newOrder = Array.from(evt.from.children).map((el, i) => ({
                id: el.dataset.id,
                row: i,
                oldRow: el.dataset.row,
                oldPosition: el.dataset.position,
                id_compat: el.dataset.idcompat,
                type: 'brand'
            }));
    
            console.log(newOrder); // Para debugging
    
            $.ajax({
                url: '{{ route("compats.setOrder") }}',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    dataInfo: newOrder
                },
                success: function (response) {
                    console.log('Reordenado com sucesso (marcas):', response);
                },
                error: function (xhr) {
                    console.error('Erro ao reordenar marcas:', xhr.responseText);
                }
            });
        }
    });

    
    // Modelos (dentro de Marca)
    document.querySelectorAll('.sortable-models').forEach(container => {
        new Sortable(container, {
            group: { name: 'models', pull: false, put: false },
            animation: 150,
            onEnd: function (evt) {

                const newOrder = Array.from(evt.from.children).map((el, i) => ({
                    id: el.dataset.id, 
                    row: i, 
                    oldRow: el.dataset.row, 
                    oldPosition: el.dataset.position, 
                    id_compat: el.dataset.idcompat,
                    type: 'model'
                }));
                
                console.log(newOrder);
                
                $.ajax({
                    url: '{{route("compats.setOrder")}}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        dataInfo: newOrder
                    },
                    success: function (response) {
                        console.log('Reordenado com sucesso:', response);
                    },
                    error: function (xhr) {
                        console.error('Erro ao reordenar modelos:', xhr.responseText);
                    }
                });
            }
        });
    });

    // Tipos (dentro de Modelo)
    document.querySelectorAll('.sortable-types').forEach(container => {
        new Sortable(container, {
            group: { name: 'types', pull: false, put: false },
            animation: 150,
            onEnd: function (evt) {

                const newOrder = Array.from(evt.from.children).map((el, i) => ({
                    id: el.dataset.id, 
                    row: i, 
                    oldRow: el.dataset.row, 
                    oldPosition: el.dataset.position, 
                    id_compat: el.dataset.idcompat,
                    type: 'type'
                }));
            
                console.log(newOrder);
                
                $.ajax({
                    url: '{{route("compats.setOrder")}}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        dataInfo: newOrder
                    },
                    success: function (response) {
                        console.log('Reordenado com sucesso:', response);
                    },
                    error: function (xhr) {
                        console.error('Erro ao reordenar modelos:', xhr.responseText);
                    }
                });
            }
        });
    });

    // Versões (dentro de Tipo)
    document.querySelectorAll('.sortable-versions').forEach(container => {
        new Sortable(container, {
            group: { name: 'versions', pull: false, put: false },
            animation: 150,
            onEnd: function (evt) {

                const newOrder = Array.from(evt.from.children).map((el, i) => ({
                    id: el.dataset.id, 
                    row: i, 
                    oldRow: el.dataset.row, 
                    oldPosition: el.dataset.position, 
                    id_compat: el.dataset.idcompat,
                    type: 'version'
                }));
                
                console.log(newOrder);
                
                $.ajax({
                    url: '{{route("compats.setOrder")}}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        dataInfo: newOrder
                    },
                    success: function (response) {
                        console.log('Reordenado com sucesso:', response);
                    },
                    error: function (xhr) {
                        console.error('Erro ao reordenar modelos:', xhr.responseText);
                    }
                });
                /** AJAX ORDER**/
            }
        });
    });
</script>

@endsection