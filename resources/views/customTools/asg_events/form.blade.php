@extends('layouts.app')

@section('content')
@php
    $isEdit = $mode === 'edit';
    $action = $isEdit ? route('asg_events.update', $event->id_gallery) : route('asg_events.store');
    $galleryImages = $event->images_array ?? [];
@endphp

<div class="container-fluid py-4 asg-page asg-form-page">

    @include('customTools.asg_events.partials.styles')

    @if(session('success'))
        <div class="alert alert-success asg-alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger asg-alert">
            <strong>Existem erros no formulario.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="asg-event-form" method="POST" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="asg-layout">
            <aside class="asg-sidebar">
                <div class="asg-card asg-sticky">
                    <div class="asg-card-title">Navegacao</div>

                    <a href="#section-main" class="asg-nav-link">Dados principais</a>
                    <a href="#section-names" class="asg-nav-link">Nomes por idioma</a>
                    <a href="#section-media" class="asg-nav-link">Galeria</a>

                    <hr>

                    <div class="asg-summary">
                        <div>
                            <span>Estado</span>
                            <strong>{{ (int) old('display', $event->display ?? 1) === 1 ? 'Visivel' : 'Oculto' }}</strong>
                        </div>
                        <div>
                            <span>Tipo</span>
                            <strong>{{ old('gallery_type', $event->gallery_type ?? 'internal') === 'flickr' ? 'Flickr' : 'Interna' }}</strong>
                        </div>
                        <div>
                            <span>Imagens</span>
                            <strong>{{ count($galleryImages) }}</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 mt-3">Guardar alteracoes</button>

                    @if($isEdit)
                        <button type="button"
                                class="btn btn-outline-danger w-100 mt-2"
                                onclick="if(confirm('Remover este evento?')) document.getElementById('delete-event-form').submit();">
                            Remover evento
                        </button>
                    @endif
                </div>
            </aside>

            <main class="asg-content">
                <section id="section-main" class="asg-card mb-4">
                    <div class="row g-3">
                        <div class="col-md-2" style="display: none;">
                            <label class="form-label">ID Shop</label>
                            <input type="number" name="id_shop" class="form-control" value="{{ old('id_shop', $event->id_shop ?? 2) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nome interno</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $event->name) }}" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Data/Ano</label>
                            <input type="text" name="event_date" class="form-control" value="{{ old('event_date', $event->event_date) }}" placeholder="2026">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Tipo de galeria</label>
                            <select name="gallery_type" class="form-select">
                                <option value="internal" @selected(old('gallery_type', $event->gallery_type ?? 'internal') === 'internal')>Interna</option>
                                <option value="flickr" @selected(old('gallery_type', $event->gallery_type ?? 'internal') === 'flickr')>Flickr</option>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">Visivel</label>
                            <select name="display" class="form-select">
                                <option value="1" @selected((int) old('display', $event->display ?? 1) === 1)>Sim</option>
                                <option value="0" @selected((int) old('display', $event->display ?? 1) === 0)>Nao</option>
                            </select>
                        </div>

                        <div class="col-md-1">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control" value="{{ old('position', $event->position ?? 0) }}">
                        </div>

                        <div class="col-12" data-flickr-box>
                            <label class="form-label">Link Flickr</label>
                            <input type="url" name="flickr_url" class="form-control" value="{{ old('flickr_url', $event->flickr_url) }}" placeholder="https://www.flickr.com/photos/...">
                        </div>
                    </div>
                </section>

                <section id="section-names" class="asg-card mb-4">
                    @php
                        $flags = [
                            'en' => 'EN',
                            'es' => 'ES',
                            'fr' => 'FR',
                            'pt' => 'PT',
                            'it' => 'IT',
                        ];
                    @endphp

                    <ul class="nav nav-pills asg-lang-tabs mb-3" role="tablist">
                        @foreach($languages as $lang => $label)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link @if($loop->first) active @endif"
                                        type="button"
                                        data-bs-toggle="tab"
                                        data-bs-target="#lang-{{ $lang }}">
                                    <span class="asg-lang-code">{{ $flags[$lang] ?? strtoupper($lang) }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content">
                        @foreach($languages as $lang => $label)
                            <div class="tab-pane fade @if($loop->first) show active @endif" id="lang-{{ $lang }}">
                                <label class="form-label">Nome do evento - {{ $label }}</label>
                                <input type="text" name="name_{{ $lang }}" class="form-control" value="{{ old('name_' . $lang, $event->{'name_' . $lang}) }}">
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

                                @if(!empty($event->cover_desktop))
                                    <div class="asg-preview mt-3">
                                        <img src="{{ asset($event->cover_desktop) }}" alt="Capa desktop">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="asg-upload-box">
                                <label class="form-label">Capa mobile</label>
                                <input type="file" name="cover_mobile" class="form-control" accept="image/*">

                                @if(!empty($event->cover_mobile))
                                    <div class="asg-preview mt-3">
                                        <img src="{{ asset($event->cover_mobile) }}" alt="Capa mobile">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12" data-internal-gallery-box>
                            <div class="asg-upload-box">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Galeria interna</label>
                                    <span class="badge bg-light text-dark">{{ count($galleryImages) }} existentes</span>
                                </div>

                                <input type="file" name="gallery_images[]" class="form-control" accept="image/*" multiple>

                                <div class="asg-gallery mt-3" id="gallery-existing" data-sortable-gallery>
                                    @foreach($galleryImages as $image)
                                        <div class="asg-gallery-item" draggable="true">
                                            <div class="asg-gallery-order" data-gallery-order>{{ $loop->iteration }}</div>
                                            <div class="asg-gallery-drag" title="Arrastar para ordenar">
                                                <i class="fa-solid fa-up-down-left-right"></i>
                                            </div>
                                            <img src="{{ asset($image) }}" alt="Gallery image">
                                            <input type="hidden" name="existing_images[]" value="{{ $image }}">
                                            <div class="asg-gallery-footer">
                                                <div class="asg-gallery-path">{{ basename($image) }}</div>
                                                <button type="button" class="asg-gallery-remove" data-remove-gallery-item title="Remover">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </form>

    @if($isEdit)
        <form id="delete-event-form" method="POST" action="{{ route('asg_events.destroy', $event->id_gallery) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>

@include('customTools.asg_events.partials.scripts')
@endsection
