@php
    $safeIndex = is_numeric($index) ? (int) $index : 0;

    $idProduct = old("products.$categoryKey.$safeIndex.id_product", $product->id_product ?? '');
    $productName = old("products.$categoryKey.$safeIndex.name", $product->name ?? '');
    $productLink = old("products.$categoryKey.$safeIndex.link", $product->link ?? '');
    $idLang = old("products.$categoryKey.$safeIndex.id_lang", $product->id_lang ?? 1);
    $position = old("products.$categoryKey.$safeIndex.position", $product->position ?? ($safeIndex + 1));
@endphp

<div class="asg-product-row product-row">
    <input type="number"
           name="products[{{ $categoryKey }}][{{ $safeIndex }}][position]"
           value="{{ $position }}"
           class="form-control form-control-sm"
           data-product-position>

    <input type="number"
           name="products[{{ $categoryKey }}][{{ $safeIndex }}][id_product]"
           value="{{ $idProduct }}"
           class="form-control form-control-sm"
           placeholder="ID">

    <input type="text"
           name="products[{{ $categoryKey }}][{{ $safeIndex }}][name]"
           value="{{ $productName }}"
           class="form-control form-control-sm"
           placeholder="Nome do produto">

    <input type="number"
           name="products[{{ $categoryKey }}][{{ $safeIndex }}][id_lang]"
           value="{{ $idLang }}"
           class="form-control form-control-sm"
           placeholder="Lang">

    <div class="asg-product-actions">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-move-product-up title="Subir">↑</button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-move-product-down title="Descer">↓</button>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-product-row title="Remover">×</button>
    </div>
</div>