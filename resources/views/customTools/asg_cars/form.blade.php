@extends('layouts.app')

@section('content')
@php
    $isEdit = $mode === 'edit';
    $action = $isEdit ? route('asg_cars.update', $car->id_asg_car) : route('asg_cars.store');
    $galleryImages = $car->images_array ?? [];
@endphp

<style>
    .asg-lang-tabs .nav-item{ max-width: 100%; width: auto; }
</style>
<div class="container-fluid py-4 asg-page asg-form-page">

    @include('customTools.asg_cars.partials.styles')

    @if(session('success'))
        <div class="alert alert-success asg-alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger asg-alert">
            <strong>Existem erros no formulário.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form id="asg-car-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="asg-layout">

            <aside class="asg-sidebar">
                <div class="asg-card asg-sticky">
                    <div class="asg-card-title">Navegação</div>

                    <a href="#section-main" class="asg-nav-link">Dados principais</a>
                    <a href="#section-texts" class="asg-nav-link">Textos por idioma</a>
                    <a href="#section-media" class="asg-nav-link">Imagens e vídeo</a>
                    <a href="#section-products" class="asg-nav-link">Produtos</a>

                    <hr>

                    <div class="asg-summary">
                        <div style="display: none;">
                            <span>ID</span>
                            <strong>{{ $car->id_asg_car ?? 'Novo' }}</strong>
                        </div>
                        <div>
                            <span>Estado</span>
                            <strong>{{ (int) old('display', $car->display ?? 1) === 1 ? 'Visível' : 'Oculto' }}</strong>
                        </div>
                        <div>
                            <span>Galeria</span>
                            <strong>{{ count($galleryImages) }} imagens</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mt-3">Guardar alterações</button>

                    @if($isEdit)
                        <button type="button"
                                class="btn btn-outline-danger w-100 mt-2"
                                onclick="if(confirm('Remover este veículo?')) document.getElementById('delete-car-form').submit();">
                            Remover veículo
                        </button>
                    @endif
                </div>
            </aside>

            <main class="asg-content">

                <section id="section-main" class="asg-card mb-4">
                    <div class="row g-3">
                        <div class="col-md-2" style="display: none;">
                            <label class="form-label">ID Shop</label>
                            <input type="number" name="id_shop" class="form-control" value="2" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nome do veículo</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $car->name) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nome para lista de carros</label>
                            <input type="text" name="car_name_galleries" class="form-control" value="{{ old('car_name_galleries', $car->car_name_galleries) }}">
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">Visível</label>
                            <select name="display" class="form-select">
                                <option value="1" @selected((int) old('display', $car->display ?? 1) === 1)>Sim</option>
                                <option value="0" @selected((int) old('display', $car->display ?? 1) === 0)>Não</option>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control" value="{{ old('position', $car->position ?? 0) }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Código YouTube</label>
                            <input type="text" name="youtube_code" class="form-control" value="{{ old('youtube_code', $car->youtube_code) }}" placeholder="Só o código do vídeo. Ex: xYz123">
                        </div>
                    </div>
                </section>

                <section id="section-texts" class="asg-card mb-4">
                
                    @php
                        $flags = [
                            'en' => '🇬🇧',
                            'es' => '🇪🇸',
                            'fr' => '🇫🇷',
                            'pt' => '🇵🇹',
                            'it' => '🇮🇹',
                        ];
                    @endphp
                
                    <ul class="nav nav-pills asg-lang-tabs mb-3" role="tablist">
                        @foreach($languages as $lang => $label)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($loop->first) active @endif"
                                        type="button"
                                        data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $lang }}">
                                    <span class="asg-lang-flag">{{ $flags[$lang] ?? '🌐' }}</span>
                                    <span class="asg-lang-code">{{ strtoupper($lang) }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                
                    <div class="tab-content">
                        @foreach($languages as $lang => $label)
                            <div class="tab-pane fade @if($loop->first) show active @endif" id="lang-{{ $lang }}">
                                <div class="row g-3">
                                    <div class="col-md-7">
                                        <label class="form-label"> Descrição motorização — {{ $flags[$lang] ?? '🌐' }} {{ $label }} </label>
                                        <textarea name="description_{{ $lang }}" rows="1" class="form-control">{{ old('description_' . $lang, $car->{'description_' . $lang}) }}</textarea>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label"> Budget — {{ $flags[$lang] ?? '🌐' }} {{ $label }} </label>
                                        <textarea name="budget_{{ $lang }}" rows="1" class="form-control">{{ old('budget_' . $lang, $car->{'budget_' . $lang}) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section id="section-media" class="asg-card mb-4">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="asg-upload-box">
                                <label class="form-label">Capa desktop</label>
                                <input type="file" name="cover_desktop" class="form-control" accept="image/*">

                                @if(!empty($car->cover_desktop))
                                    <div class="asg-preview mt-3">
                                        <img src="{{ asset($car->cover_desktop) }}" alt="Capa desktop">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="asg-upload-box">
                                <label class="form-label">Capa mobile</label>
                                <input type="file" name="cover_mobile" class="form-control" accept="image/*">

                                @if(!empty($car->cover_mobile))
                                    <div class="asg-preview mt-3">
                                        <img src="{{ asset($car->cover_mobile) }}" alt="Capa mobile">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="asg-upload-box">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Galeria de imagens</label>
                                    <span class="badge bg-light text-dark">{{ count($galleryImages) }} existentes</span>
                                </div>

                                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>

                                <div class="asg-gallery mt-3" id="gallery-existing">
                                    @foreach($galleryImages as $image)
                                        <div class="asg-gallery-item">
                                            <img src="{{ asset($image) }}" alt="Gallery image">
                                            <input type="hidden" name="existing_images[]" value="{{ $image }}">
                                            <button type="button" class="asg-gallery-remove" data-remove-gallery-item title="Remover">×</button>
                                            <div class="asg-gallery-path">{{ basename($image) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="section-products" class="asg-card mb-4">
                    <div class="asg-section-header">
                        <div>
                            <div class="asg-card-title mb-0">Produtos por categoria</div>
                            <div class="text-muted small">Adiciona ID do produto, nome, idioma, link e ordenação.</div>
                        </div>
                    </div>

                    <div class="accordion asg-products-accordion" id="productsAccordion">
                        @foreach($productCategories as $categoryKey => $categoryLabel)
                            @php
                                $categoryProducts = $productsByCategory[$categoryKey] ?? [];
                            @endphp

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-{{ $categoryKey }}">
                                    <button class="accordion-button @if(!$loop->first) collapsed @endif"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse-{{ $categoryKey }}">
                                        <span class="asg-category-title">{{ $categoryLabel }}</span>
                                        <span class="badge bg-secondary ms-2" data-category-count="{{ $categoryKey }}">{{ count($categoryProducts) }}</span>
                                    </button>
                                </h2>

                                <div id="collapse-{{ $categoryKey }}"
                                     class="accordion-collapse collapse @if($loop->first) show @endif"
                                     data-bs-parent="#productsAccordion">
                                    <div class="accordion-body">
                                        <div class="asg-product-head">
                                            <span>Pos.</span>
                                            <span>ID Produto</span>
                                            <span>Nome</span>
                                            <span>Lang</span>
                                            <span>Ações</span>
                                        </div>

                                        <div class="product-rows" id="products-{{ $categoryKey }}">
                                            @forelse($categoryProducts as $index => $product)
                                                @include('customTools.asg_cars.partials.product-row', [
                                                    'categoryKey' => $categoryKey,
                                                    'index' => $index,
                                                    'product' => $product
                                                ])
                                            @empty
                                                @include('customTools.asg_cars.partials.product-row', [
                                                    'categoryKey' => $categoryKey,
                                                    'index' => 0,
                                                    'product' => null
                                                ])
                                            @endforelse
                                        </div>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary mt-3"
                                                data-add-product="{{ $categoryKey }}">
                                            + Adicionar produto {{ strtolower($categoryLabel) }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

            </main>
        </div>
    </form>

    @if($isEdit)
        <form id="delete-car-form" method="POST" action="{{ route('asg_cars.destroy', $car->id_asg_car) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif

</div>

<template id="product-row-template">
    @include('customTools.asg_cars.partials.product-row', [
        'categoryKey' => '__CATEGORY__',
        'index' => '__INDEX__',
        'product' => null
    ])
</template>

@include('customTools.asg_cars.partials.scripts')
@endsection
