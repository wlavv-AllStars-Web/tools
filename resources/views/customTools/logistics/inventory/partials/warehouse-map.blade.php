<div class="customPanel warehouse-panel">
<div class="warehouse-navigator {{ isset($planSchedules) ? 'has-plan' : '' }}"
     data-columns-url="{{ route('logistics.tools.inventory.admin.map.columns') }}"
     data-cells-url="{{ route('logistics.tools.inventory.admin.map.cells') }}"
     data-products-url="{{ route('logistics.tools.inventory.admin.map.products') }}"
     data-selectable="{{ !empty($selectable) ? '1' : '0' }}">
    <div class="warehouse-step">
        <div class="warehouse-step-title">1. Linhas</div>
        <div class="warehouse-options warehouse-row-options">
            @foreach($warehouseRows as $row)
                <button type="button" class="warehouse-option warehouse-row-option" data-row="{{ $row['row'] }}">
                    <strong>{{ $row['row'] }}</strong>
                    <span>{{ $row['columns_count'] }} colunas - {{ $row['cells_count'] }} celulas</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="warehouse-step">
        <div class="warehouse-step-title">2. Colunas</div>
        <div class="warehouse-options warehouse-column-options">
            <div class="warehouse-empty">Selecione uma linha.</div>
        </div>
    </div>

    <div class="warehouse-step">
        <div class="warehouse-step-title">3. Celulas</div>
        <div class="warehouse-options warehouse-cell-options">
            <div class="warehouse-empty">Selecione uma coluna.</div>
        </div>
    </div>

    <div class="warehouse-step warehouse-products-step">
        <div class="warehouse-step-title">4. Produtos</div>
        <div class="warehouse-products">
            <div class="warehouse-empty">Selecione uma celula.</div>
        </div>
    </div>

    @isset($planSchedules)
        <div class="warehouse-step warehouse-plan-step">
            <div class="warehouse-step-title">5. Plano de {{ $date }}</div>
            <div class="warehouse-plan-list">
                @forelse($planSchedules as $schedule)
                    @php
                        $prepState = $schedule->preparation_done ? 'done' : ($schedule->total_rows > 0 ? 'progress' : 'pending');
                        $inventoryState = $schedule->inventory_done ? 'done' : ($schedule->counted_rows > 0 ? 'progress' : 'pending');
                        $validationState = $schedule->verification_done ? 'done' : 'pending';
                        $deleteMessage = ($schedule->inventory_done || $schedule->counted_rows > 0)
                            ? 'Esta celula ja esta inventariada ou parcialmente inventariada. Ao remover, os registos de contagem desta celula tambem serao removidos. Continuar?'
                            : 'Remover esta celula?';
                    @endphp
                    <div class="warehouse-plan-row">
                        <strong>{{ $schedule->cell }}</strong>
                        <span class="warehouse-plan-states">
                            <i class="fa-solid fa-circle inventory-state-icon state-{{ $prepState }}" title="Preparacao"></i>
                            <i class="fa-solid fa-circle inventory-state-icon state-{{ $inventoryState }}" title="Inventario"></i>
                            <i class="fa-solid fa-circle-check inventory-state-icon state-{{ $validationState }}" title="Validacao"></i>
                        </span>
                        <span class="warehouse-plan-lines">{{ $schedule->counted_rows }}/{{ $schedule->total_rows }}</span>
                        @if($schedule->verification_done)
                            <button class="btn btn-sm btn-secondary" type="button" disabled title="Inventario validado"><i class="fa-solid fa-lock"></i></button>
                        @else
                            <button class="btn btn-sm btn-danger" type="submit" form="inventory-delete-schedule-{{ $schedule->id }}" onclick="return confirm(@js($deleteMessage));"><i class="fa-solid fa-trash"></i></button>
                        @endif
                    </div>
                @empty
                    <div class="warehouse-empty">Sem celulas planeadas.</div>
                @endforelse
            </div>
        </div>
    @endisset
</div>

<script>
    (function(){
        const root = $('.warehouse-navigator').last();
        const selectable = root.data('selectable') === 1;
        const columnsUrl = root.data('columns-url');
        const cellsUrl = root.data('cells-url');
        const productsUrl = root.data('products-url');
        let selectedRow = '';
        let selectedColumn = '';

        root.on('click', '.warehouse-row-option', function(){
            selectedRow = $(this).data('row');
            selectedColumn = '';
            root.find('.warehouse-row-option').removeClass('active');
            $(this).addClass('active');
            root.find('.warehouse-cell-options').html('<div class="warehouse-empty">Selecione uma coluna.</div>');
            root.find('.warehouse-products').html('<div class="warehouse-empty">Selecione uma celula.</div>');
            root.find('.warehouse-column-options').html('<div class="warehouse-empty">A carregar...</div>');

            $.get(columnsUrl, { row: selectedRow }, function(response){
                const columns = response.columns || [];
                if (!columns.length) {
                    root.find('.warehouse-column-options').html('<div class="warehouse-empty">Sem colunas.</div>');
                    return;
                }

                root.find('.warehouse-column-options').html(columns.map(function(column){
                    return '<button type="button" class="warehouse-option warehouse-column-option" data-column="' + column.column + '">' +
                        '<strong>' + column.column + '</strong>' +
                        '<span>' + column.cells_count + ' celulas</span>' +
                    '</button>';
                }).join(''));
            });
        });

        root.on('click', '.warehouse-column-option', function(){
            selectedColumn = $(this).data('column');
            root.find('.warehouse-column-option').removeClass('active');
            $(this).addClass('active');
            root.find('.warehouse-cell-options').html('<div class="warehouse-empty">A carregar...</div>');
            root.find('.warehouse-products').html('<div class="warehouse-empty">Selecione uma celula.</div>');

            $.get(cellsUrl, { row: selectedRow, column: selectedColumn }, function(response){
                const cells = response.cells || [];
                const unavailable = response.unavailable || [];
                if (!cells.length) {
                    root.find('.warehouse-cell-options').html('<div class="warehouse-empty">Sem celulas.</div>');
                    return;
                }

                root.find('.warehouse-cell-options').html(cells.map(function(cell){
                    const isUnavailable = unavailable.includes(cell.cell);
                    const input = selectable && !isUnavailable
                        ? '<input class="warehouse-cell-checkbox" type="checkbox" name="cells[]" value="' + cell.cell + '" checked>'
                        : '';
                    const pending = selectable && isUnavailable
                        ? '<span class="warehouse-cell-pending">Pendente</span>'
                        : '';

                    return '<label class="warehouse-cell warehouse-cell-option ' + (selectable && !isUnavailable ? 'selected ' : '') + 'age-' + cell.age_status + '" data-cell="' + cell.cell + '">' +
                        input +
                        '<strong>' + cell.cell + '</strong>' +
                        '<span>' + cell.product_count + ' produtos</span>' +
                        '<span>Ultimo: ' + (cell.last_inventory_date || 'Nunca') + '</span>' +
                        pending +
                    '</label>';
                }).join(''));
            });
        });

        root.on('click', '.warehouse-cell-option', function(event){
            if ($(event.target).is('input')) {
                return;
            }

            const cell = $(this).data('cell');
            root.find('.warehouse-cell-option').removeClass('active');
            $(this).addClass('active');
            root.find('.warehouse-products').html('<div class="warehouse-empty">A carregar...</div>');

            $.get(productsUrl, { cell: cell }, function(response){
                const products = response.products || [];
                if (!products.length) {
                    root.find('.warehouse-products').html('<div class="warehouse-empty">Sem produtos.</div>');
                    return;
                }

                root.find('.warehouse-products').html(products.map(function(product){
                    return '<div class="warehouse-product">' +
                        '<strong>' + product.reference + '</strong>' +
                        '<small>Linhas agrupadas: ' + product.variants + '</small>' +
                        '<div class="warehouse-product-grid">' +
                            '<span>Stock<br><b>' + product.current_quantity + '</b></span>' +
                            '<span>Arrive<br><b>' + product.stock_arrive + '</b></span>' +
                            '<span>Encomendas<br><b>' + product.active_orders + '</b></span>' +
                        '</div>' +
                        '<small>Ultimo inventario: ' + (product.last_inventory_date || 'Nunca') + ' - ' + (product.last_inventory_user || '-') + '</small>' +
                    '</div>';
                }).join(''));
            });
        });

        root.on('change', '.warehouse-cell-checkbox', function(){
            $(this).closest('.warehouse-cell-option').toggleClass('selected', this.checked);
        });
    })();
</script>
</div>
