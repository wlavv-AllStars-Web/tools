@extends('layouts.app')
@section('content')

<div class="navbar navbar-light customPanel sortable-brands brand-toolbar">
    @foreach($structure as $brand)
        <div
            class="brand-block"
            data-id="{{ $brand->id }}"
            data-idbrand="{{ $brand->id }}"
            data-position="{{ $brand->position }}"
            data-idcompat="{{ $brand->id_compat }}"
        >
            <button type="button" class="brand-trigger" onclick="openContent({{ $brand->id }})">
                <img class="brand-logo" id="image_{{ $brand->id }}" src="/uploads/compats/brand/{{ $brand->id }}.png?t=2961" alt="{{ $brand->name }}">
                <span class="brand-name">{{ $brand->name }}</span>
            </button>
            <div class="drag-handle"><i class="fa-solid fa-arrows-up-down-left-right"></i></div>
        </div>
    @endforeach
</div>

@foreach($structure as $brand)
    <div class="all_brands" id="brand_{{ $brand->id }}" style="display:none;">
        <div class="brand-panel customPanel">
            <ul class="sortable-models model-list" data-brand-id="{{ $brand->id }}">
                @foreach($brand->models as $model)
                    <li
                        class="model-item"
                        data-id="{{ $model->id }}"
                        data-idbrand="{{ $brand->id }}"
                        data-idmodel="{{ $model->id }}"
                        data-position="{{ $model->position }}"
                        data-idcompat="{{ $model->id_compat }}"
                    >
                        <div class="item-toolbar">
                            <div class="item-title-row">
                                <span class="item-title">{{ $model->name }}</span>
                                <span class="item-meta">{{ count($model->types) }} bloco(s)</span>
                            </div>
                            <span class="drag-handle"><i class="fa-solid fa-arrows-up-down-left-right"></i></span>
                        </div>

                        <ul class="sortable-types type-grid" data-brand-id="{{ $brand->id }}" data-model-id="{{ $model->id }}">
                            @foreach($model->types as $type)
                                <li
                                    class="type-item"
                                    data-id="{{ $type->id }}"
                                    data-idbrand="{{ $brand->id }}"
                                    data-idmodel="{{ $model->id }}"
                                    data-idtype="{{ $type->id }}"
                                    data-position="{{ $type->position }}"
                                    data-idcompat="{{ $type->id_compat }}"
                                >
                                    <div class="type-card">
                                        <div class="item-toolbar type-toolbar">
                                            <div class="item-title-row">
                                                <span class="type-title">{{ $type->name }}</span>
                                                <span class="item-meta">{{ $type->versions_count }} versão(ões)</span>
                                            </div>
                                        </div>

                                        <div class="type-media">
                                            <img
                                                class="type-image"
                                                id="image_type_{{ $type->id }}"
                                                src="/uploads/compats/compat/{{ $type->cover_compat_id }}.png?t=4880"
                                                alt="{{ $model->name }} - {{ $type->name }}"
                                            >
                                        </div>

                                        <div class="type-summary">{{ $model->name }} | {{ $type->name }}</div>

                                        <div class="version-list-wrap">
                                            <div class="version-list-title">Versões</div>
                                            <ul
                                                class="version-list sortable-versions"
                                                data-brand-id="{{ $brand->id }}"
                                                data-model-id="{{ $model->id }}"
                                                data-type-id="{{ $type->id }}"
                                            >
                                                @foreach($type->versions as $version)
                                                    <li
                                                        class="version-pill"
                                                        data-id="{{ $version->id }}"
                                                        data-idbrand="{{ $brand->id }}"
                                                        data-idmodel="{{ $model->id }}"
                                                        data-idtype="{{ $type->id }}"
                                                        data-idversion="{{ $version->id }}"
                                                        data-position="{{ $version->position }}"
                                                        data-idcompat="{{ $version->id_compat }}"
                                                        title="Compat #{{ $version->id_compat }}"
                                                    >
                                                        <span>{{ $version->name }}</span>
                                                        <span class="version-drag-handle">
                                                            <i class="fa-solid fa-arrows-up-down-left-right"></i>
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>

                                        @if(count($model->types) > 1)
                                            <div class="type-drag-handle drag-handle">
                                                <i class="fa-solid fa-arrows-up-down-left-right"></i>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endforeach

<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    ul { list-style: none; padding-left: 0; margin: 0; }

    .brand-toolbar {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        padding: 12px;
    }

    .brand-block,
    .model-item,
    .type-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }

    .brand-trigger {
        width: 100%;
        background: transparent;
        border: 0;
        padding: 14px 10px 10px;
        text-align: center;
    }

    .brand-logo {
        width: 52px;
        height: 52px;
        object-fit: contain;
        background: #333;
        border-radius: 6px;
        padding: 6px;
    }

    .brand-name,
    .item-title,
    .type-title {
        display: block;
        font-weight: 700;
    }

    .brand-name { margin-top: 10px; }

    .drag-handle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 34px;
        min-height: 34px;
        color: #555;
        cursor: move;
    }

    .brand-block > .drag-handle,
    .item-toolbar {
        border-top: 1px solid #ddd;
        background: #f4f4f4;
    }

    .brand-block > .drag-handle {
        width: 100%;
        height: 34px;
    }

    .brand-panel {
        padding: 14px;
        margin-top: 14px;
    }

    .model-list {
        display: grid;
        gap: 16px;
    }

    .item-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
    }

    .item-title-row {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .item-meta {
        font-size: 12px;
        color: #666;
    }

    .type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 14px;
        padding: 14px;
    }

    .type-item {
        min-width: 0;
        user-select: none;
    }

    .type-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .type-toolbar {
        border-top: 0;
        border-bottom: 1px solid #ddd;
    }

    .type-media {
        text-align: center;
        padding: 18px 12px 8px;
    }

    .type-image {
        width: min(100%, 220px);
        height: 140px;
        object-fit: contain;
        background: #ccc;
        border: 1px solid #333;
        border-radius: 8px;
        padding: 10px;
    }

    .type-summary {
        padding: 8px 12px 0;
        font-weight: 700;
        text-align: center;
    }

    .version-list-wrap {
        padding: 12px;
        text-align: center;
        flex: 1 1 auto;
    }

    .version-list-title {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #666;
        margin-bottom: 8px;
    }

    .version-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }

    .version-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #ddd;
        background: #fafafa;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 13px;
        line-height: 1.2;
        cursor: default;
        user-select: none;
    }

    .version-drag-handle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #666;
        cursor: move;
        padding-left: 4px;
    }

    .type-drag-handle {
        width: 100%;
        height: 34px;
        border-top: 1px solid #ddd;
        background: #f4f4f4;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: move;
    }

    .sortable-ghost {
        opacity: 0.4;
    }

    .sortable-chosen {
        transform: scale(1.02);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function openContent(id_brand) {
        $('.all_brands').hide();
        $('#brand_' + id_brand).show();

        $('.brand-logo').css('background-color', '#333');
        $('#image_' + id_brand).css('background-color', '#3da936');
    }

    new Sortable(document.querySelector('.sortable-brands'), {
        animation: 150,
        handle: '.drag-handle',
        onEnd: function (evt) {
            const newOrder = Array.from(evt.from.children).map((el, i) => ({
                id_brand: parseInt(el.dataset.idbrand, 10),
                position: i,
                oldPosition: el.dataset.position,
                id_compat: el.dataset.idcompat,
                type: 'brand'
            }));

            $.ajax({
                url: '{{ route("admin.tools.compats.set_order") }}',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    dataInfo: newOrder
                }
            });
        }
    });

    document.querySelectorAll('.sortable-models').forEach(container => {
        new Sortable(container, {
            group: { name: 'models', pull: false, put: false },
            animation: 150,
            handle: '.item-toolbar .drag-handle',
            onEnd: function (evt) {
                const newOrder = Array.from(evt.from.children).map((el, i) => ({
                    id_brand: parseInt(el.dataset.idbrand, 10),
                    id_model: parseInt(el.dataset.idmodel, 10),
                    position: i,
                    oldPosition: el.dataset.position,
                    id_compat: el.dataset.idcompat,
                    type: 'model'
                }));

                $.ajax({
                    url: '{{ route("admin.tools.compats.set_order") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        dataInfo: newOrder
                    }
                });
            }
        });
    });

    document.querySelectorAll('.sortable-types').forEach(container => {
        new Sortable(container, {
            group: { name: 'types', pull: false, put: false },
            animation: 150,
            handle: '.type-drag-handle',
            onEnd: function (evt) {
                const newOrder = Array.from(evt.from.children).map((el, i) => ({
                    id_brand: parseInt(el.dataset.idbrand, 10),
                    id_model: parseInt(el.dataset.idmodel, 10),
                    id_type: parseInt(el.dataset.idtype, 10),
                    position: i,
                    oldPosition: el.dataset.position,
                    id_compat: el.dataset.idcompat,
                    type: 'type'
                }));

                $.ajax({
                    url: '{{ route("admin.tools.compats.set_order") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        dataInfo: newOrder
                    }
                });
            }
        });
    });

    document.querySelectorAll('.sortable-versions').forEach(container => {
        new Sortable(container, {
            group: { name: 'versions', pull: false, put: false },
            animation: 150,
            handle: '.version-drag-handle',
            onEnd: function (evt) {
                const newOrder = Array.from(evt.from.children).map((el, i) => ({
                    id_brand: parseInt(el.dataset.idbrand, 10),
                    id_model: parseInt(el.dataset.idmodel, 10),
                    id_type: parseInt(el.dataset.idtype, 10),
                    id_version: parseInt(el.dataset.idversion, 10),
                    position: i,
                    oldPosition: el.dataset.position,
                    id_compat: el.dataset.idcompat,
                    type: 'version'
                }));

                $.ajax({
                    url: '{{ route("admin.tools.compats.set_order") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        dataInfo: newOrder
                    }
                });
            }
        });
    });
</script>

@endsection
