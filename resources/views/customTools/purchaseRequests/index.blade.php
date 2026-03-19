@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="navbar navbar-light customPanel">
            <div class="row">
                @php
                    $statusCounts = [
                        'new' => $requests->where('status', 'new')->count(),
                        'waiting_supplier' => $requests->where('status', 'waiting_supplier')->count(),
                        'quoted' => $requests->where('status', 'quoted')->count(),
                        'client_notified' => $requests->where('status', 'client_notified')->count(),
                        'closed' => $requests->where('status', 'closed')->count(),
                    ];
                @endphp

                <div class="col-lg-1 d-flex flex-wrap gap-2">
                    <div class="rfq-btn rfq-create-success" style="background: #198754;" onclick="window.location='{{ route('quote.create') }}'" role="button" tabindex="0">
                        <div class="rfq-create-icon">+</div>
                        <div class="rfq-label">Request</div>
                    </div>
                </div>
                <div class="col-lg-11 d-flex flex-wrap gap-2">
                    <div class="rfq-btn rfq-primary" onclick="showList('new', this)" role="button" tabindex="0">
                        <div class="rfq-count">{{ $statusCounts['new'] }}</div>
                        <div class="rfq-label">New request</div>
                    </div>

                    <div class="rfq-btn rfq-secondary" onclick="showList('waiting_supplier', this)" role="button" tabindex="0">
                        <div class="rfq-count">{{ $statusCounts['waiting_supplier'] }}</div>
                        <div class="rfq-label">In Progress</div>
                    </div>

                    <div class="rfq-btn rfq-info" onclick="showList('quoted', this)" role="button" tabindex="0">
                        <div class="rfq-count">{{ $statusCounts['quoted'] }}</div>
                        <div class="rfq-label">Supplier Quoted</div>
                    </div>

                    <div class="rfq-btn rfq-warning" onclick="showList('client_notified', this)" role="button" tabindex="0">
                        <div class="rfq-count">{{ $statusCounts['client_notified'] }}</div>
                        <div class="rfq-label">Client Notified</div>
                    </div>

                    <div class="rfq-btn rfq-success" onclick="showList('closed', this)" role="button" tabindex="0">
                        <div class="rfq-count">{{ $statusCounts['closed'] }}</div>
                        <div class="rfq-label">Closed requests</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="navbar navbar-light customPanel text-center;">
            @php
                $groups = $requests->groupBy('status');
            @endphp

            <div id="list-new" class="status-list text-center">
                @include('customTools.purchaseRequests.partials.table', ['items' => $groups['new'] ?? []])
            </div>

            <div id="list-waiting_supplier" class="status-list text-center d-none">
                @include('customTools.purchaseRequests.partials.table', ['items' => $groups['waiting_supplier'] ?? []])
            </div>

            <div id="list-quoted" class="status-list text-center d-none">
                @include('customTools.purchaseRequests.partials.table', ['items' => $groups['quoted'] ?? []])
            </div>

            <div id="list-client_notified" class="status-list text-center d-none">
                @include('customTools.purchaseRequests.partials.table', ['items' => $groups['client_notified'] ?? []])
            </div>

            <div id="list-closed" class="status-list text-center d-none">
                @include('customTools.purchaseRequests.partials.table', ['items' => $groups['closed'] ?? []])
            </div>
        </div>
    </div>
</div>

<style>

/* Botão de criação - success */
.rfq-create-success {
    background-color: #198754;       /* btn-success */
    border: 1px solid #198754;
    color: #ffffff;
}

/* Remove barra de estado */
.rfq-create-success::before {
    display: none;
}

/* Ícone */
.rfq-create-success .rfq-create-icon {
    font-size: 2.2rem;
    font-weight: 600;
    margin-bottom: 6px;
    line-height: 1;
}

/* Texto */
.rfq-create-success .rfq-label,
.rfq-create-success .rfq-subtext {
    color: #ffffff;
}

/* Subtexto mais suave */
.rfq-create-success .rfq-subtext {
    opacity: 0.85;
    font-size: 0.8rem;
}

/* Hover – igual ao btn-success */
.rfq-create-success:hover {
    background-color: #157347;
    border-color: #146c43;
}

/* Active */
.rfq-create-success:active {
    background-color: #146c43;
    border-color: #13653f;
}


/* Active */
.rfq-create:active {
    background-color: #dee2e6;
}


/* Card-button base */
.rfq-btn {
    flex: 1;
    min-width: 100px;
    padding: 18px;
    background: #ffffff;
    border-radius: 8px;
    cursor: pointer;
    border: 1px solid #dee2e6;
    box-shadow: 0 1px 2px rgba(0,0,0,.08);
    transition: all 0.15s ease-in-out;
    text-align: center;
    position: relative;
    user-select: none;
}

/* Barra superior (estado) */
.rfq-btn::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    height: 4px;
    width: 100%;
    border-radius: 8px 8px 0 0;
}

/* Hover = feedback de botão */
.rfq-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,.15);
}

/* Active (press) */
.rfq-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(0,0,0,.2);
}

/* Conteúdo */
.rfq-count {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 4px;
    line-height: 1.1;
}

.rfq-label {
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #6c757d;
}

/* Estados (barra superior) */
.rfq-primary::before { background: #0d6efd; }
.rfq-secondary::before { background: #6c757d; }
.rfq-info::before { background: #0dcaf0; }
.rfq-warning::before { background: #ffc107; }
.rfq-success::before { background: #198754; }

/* Cor do número por estado (suave mas legível) */
.rfq-primary .rfq-count { color: #0d6efd; }
.rfq-secondary .rfq-count { color: #6c757d; }
.rfq-info .rfq-count { color: #0dcaf0; }
.rfq-warning .rfq-count { color: #b08800; }
.rfq-success .rfq-count { color: #198754; }

/* Botão ativo (selecionado) */
.rfq-btn.active {
    background-color: #f1f3f5; /* cinza claro */
    border-color: #adb5bd;
    box-shadow: inset 0 0 0 1px #ced4da;
}

/* Mantém a barra de estado evidente quando ativo */
.rfq-btn.active::before {
    height: 5px;
}

/* Texto um pouco mais escuro quando ativo */
.rfq-btn.active .rfq-label {
    color: #495057;
}

/* Opcional: ativo + hover */
.rfq-btn.active:hover {
    background-color: #e9ecef;
}
</style>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>
let initializedTables = {};

function showList(status, el) {
    // 1) estado ativo do botão
    document.querySelectorAll('.rfq-btn').forEach(btn => btn.classList.remove('active'));
    if (el) el.classList.add('active');

    // 2) mostrar a lista correta
    $('.status-list').addClass('d-none');
    const list = $('#list-' + status);
    list.removeClass('d-none');

    // 3) inicializar DataTable uma vez por estado
    if (!initializedTables[status]) {
        list.find('.datatable').DataTable({
            pageLength: 10,
            order: [[0, 'desc']]
        });
        initializedTables[status] = true;
    }
}

// Acessibilidade básica: Enter/Espaço ativa o "botão"
document.addEventListener('keydown', function (e) {
    const isBtn = e.target && e.target.classList && e.target.classList.contains('rfq-btn');
    if (!isBtn) return;

    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        e.target.click();
    }
});

$(document).ready(function () {
    // Abre "new" e marca o primeiro botão como ativo
    const firstBtn = document.querySelector('.rfq-btn.rfq-primary');
    showList('new', firstBtn);
});
</script>
@endsection
