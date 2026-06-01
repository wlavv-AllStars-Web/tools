<style>
    .inventory-wrap{width:100%;max-width:none;margin:0;padding:14px;}
    .inventory-header{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:14px;flex-wrap:wrap;}
    .inventory-title{font-size:28px;font-weight:800;margin:0;color:#1f2937;}
    .inventory-actions{display:flex;gap:8px;flex-wrap:wrap;}
    .inventory-command-bar{display:grid;grid-template-columns:minmax(360px,1.25fr) minmax(230px,.55fr) minmax(420px,1.35fr);gap:12px;align-items:stretch;margin-bottom:14px;width:100%;padding:12px;}
    .inventory-action-tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(112px,1fr));gap:8px;}
    .inventory-action-tile{min-height:86px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#1f2937;text-decoration:none;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;font-weight:800;text-align:center;padding:10px;}
    .inventory-action-tile:hover{text-decoration:none;color:#111827;border-color:#0d6efd;background:#f8fafc;}
    .inventory-action-tile i{font-size:28px;line-height:1;}
    .inventory-action-tile span{font-size:13px;line-height:1.15;}
    .inventory-action-tile.primary{background:#0d6efd;color:#fff;border-color:#0d6efd;}
    .inventory-action-tile.map{border-color:#93c5fd;color:#1d4ed8;}
    .inventory-action-tile.admin{border-color:#9ca3af;color:#111827;}
    .inventory-action-tile.verify{border-color:#86efac;color:#166534;}
    .inventory-action-tile.report{border-color:#cbd5e1;color:#475569;}
    .inventory-date-selector{border:1px solid #ddd;background:#fff;border-radius:6px;padding:10px;display:flex;flex-direction:column;justify-content:center;gap:7px;}
    .inventory-date-selector .form-label{font-size:12px;text-transform:uppercase;color:#333;font-weight:900;margin:0;text-align:center;}
    .inventory-date-row{display:flex;align-items:center;}
    .inventory-date-row .form-control{min-width:0;height:42px;text-align:center;font-weight:800;}
    .inventory-date-selector .btn{height:40px;font-weight:800;}
    .inventory-date-static{height:42px;border:1px solid #ced4da;border-radius:4px;display:flex;align-items:center;justify-content:center;font-weight:800;background:#f8f9fa;}
    .inventory-top-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;}
    .inventory-kpis{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:8px;margin-bottom:14px;}
    .inventory-kpi{position:relative;border:1px solid #ddd;background:#fff;border-radius:6px;padding:9px 10px 9px 42px;text-align:left;min-height:68px;display:flex;flex-direction:column;justify-content:center;overflow:hidden;transition:transform .15s ease,border-color .15s ease,box-shadow .15s ease;}
    .inventory-kpi:hover{transform:translateY(-2px);box-shadow:0 8px 18px rgba(15,23,42,.08);}
    .inventory-kpi i{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:24px;height:24px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;}
    .inventory-kpi span{display:block;font-size:10px;text-transform:uppercase;color:#666;font-weight:800;line-height:1.1;}
    .inventory-kpi strong{display:block;font-size:25px;color:#111;line-height:1.05;margin-top:3px;}
    .inventory-kpi.kpi-cells{border-color:#bfdbfe;background:#eff6ff;}
    .inventory-kpi.kpi-cells i{background:#2563eb;}
    .inventory-kpi.kpi-prepared{border-color:#fed7aa;background:#fff7ed;}
    .inventory-kpi.kpi-prepared i{background:#ea580c;}
    .inventory-kpi.kpi-done{border-color:#bbf7d0;background:#f0fdf4;}
    .inventory-kpi.kpi-done i{background:#16a34a;}
    .inventory-kpi.kpi-verified{border-color:#a7f3d0;background:#ecfdf5;}
    .inventory-kpi.kpi-verified i{background:#059669;}
    .inventory-kpi.kpi-rows{border-color:#e9d5ff;background:#faf5ff;}
    .inventory-kpi.kpi-rows i{background:#7c3aed;}
    .inventory-kpi.kpi-diff{border-color:#fecaca;background:#fef2f2;}
    .inventory-kpi.kpi-diff i{background:#dc2626;}
    .inventory-card{border:1px solid #ddd;background:#fff;border-radius:6px;margin-bottom:12px;overflow:hidden;}
    .inventory-card-header{padding:10px 12px;background:#f5f5f5;border-bottom:1px solid #ddd;font-weight:800;display:flex;justify-content:space-between;align-items:center;gap:8px;}
    .inventory-collapsible-header{cursor:pointer;}
    .inventory-collapse-icon{margin-right:6px;transition:transform .15s ease;}
    .inventory-collapse-icon.open{transform:rotate(180deg);}
    .inventory-card-body{padding:12px;}
    .inventory-workflow-columns{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;align-items:start;}
    .inventory-workflow-columns.two-columns{grid-template-columns:repeat(2,minmax(0,1fr));}
    .inventory-workflow-columns .inventory-card{margin-bottom:0;min-height:240px;}
    .inventory-column-list{display:grid;grid-template-columns:1fr;gap:10px;}
    .inventory-housing-groups{display:grid;grid-template-columns:1fr;gap:8px;}
    .inventory-housing-group{border:1px solid #d1d5db;border-radius:6px;background:#fff;overflow:hidden;}
    .inventory-housing-group summary{cursor:pointer;list-style:none;display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 12px;background:#f8fafc;font-weight:800;}
    .inventory-housing-group summary::-webkit-details-marker{display:none;}
    .inventory-housing-group summary:after{content:"\\f078";font-family:"Font Awesome 6 Free";font-weight:900;color:#64748b;font-size:12px;transition:transform .15s ease;}
    .inventory-housing-group[open] summary:after{transform:rotate(180deg);}
    .inventory-housing-group summary strong{font-size:18px;color:#111827;}
    .inventory-housing-group summary span{margin-left:auto;font-size:12px;color:#64748b;text-align:right;}
    .inventory-housing-cells{display:grid;grid-template-columns:1fr;gap:8px;padding:10px;border-top:1px solid #e5e7eb;}
    .inventory-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;}
    .inventory-validation-cells{display:flex;gap:10px;overflow-x:auto;overflow-y:hidden;white-space:nowrap;padding:2px 2px 8px;max-width:100%;}
    .inventory-validation-cells .inventory-cell{flex:0 0 180px;}
    .inventory-cell{display:block;border:1px solid #bbb;border-radius:6px;padding:12px;text-align:center;color:#222;text-decoration:none;background:#fff;}
    .inventory-cell:hover{text-decoration:none;color:#111;border-color:#0d6efd;}
    .inventory-cell.active{border-color:#0d6efd;box-shadow:0 0 0 2px rgba(13,110,253,.16);background:#eff6ff;}
    .inventory-cell .code{font-size:24px;font-weight:800;display:block;}
    .inventory-cell .meta{font-size:12px;color:#666;display:block;margin-top:4px;}
    .inventory-status{display:inline-flex;align-items:center;justify-content:center;min-width:82px;border-radius:999px;padding:4px 8px;font-size:12px;font-weight:800;}
    .inventory-status.todo{background:#fee2e2;color:#991b1b;}
    .inventory-status.progress{background:#fef3c7;color:#92400e;}
    .inventory-status.done{background:#dcfce7;color:#166534;}
    .inventory-status.verify{background:#dbeafe;color:#1d4ed8;}
    .inventory-form-row{display:flex;gap:8px;align-items:end;flex-wrap:wrap;}
    .inventory-form-row .form-control{min-width:180px;}
    .inventory-table{width:100%;border-collapse:collapse;background:#fff;}
    .inventory-table th,.inventory-table td{border:1px solid #ddd;padding:8px;vertical-align:middle;text-align:center;}
    .inventory-table th{background:#f5f5f5;}
    .inventory-table .number{width:110px;text-align:center;}
    .inventory-copy-reference{border:0;background:transparent;color:#111;font-weight:800;padding:2px 4px;cursor:pointer;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .inventory-copy-reference:hover{color:#0d6efd;text-decoration:underline;}
    .inventory-copy-reference.copied{color:#16a34a;}
    .inventory-sales-states{min-width:96px;}
    .inventory-sales-state-grid{display:flex;justify-content:center;gap:5px;}
    .inventory-sales-state{border:1px solid transparent;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;text-align:center;line-height:1;}
    .inventory-sales-state strong{display:block;font-size:12px;color:#fff;line-height:1;}
    .inventory-sales-state.state-paid{background:#16a34a;border-color:#15803d;}
    .inventory-sales-state.state-preparation{background:dodgerblue;border-color:#1d4ed8;}
    .inventory-sales-state.state-backorder{background:#f59e0b;border-color:#d97706;}
    .inventory-sales-state.state-info{background:#6b7280;border-color:#4b5563;}
    .inventory-row-diff{background:#fee2e2;color:#7f1d1d;}
    .inventory-row-ok{background:#dcfce7;color:#14532d;}
    .inventory-mobile-input{height:46px;font-size:22px;text-align:center;font-weight:800;}
    .warehouse-panel{padding:12px;margin-bottom:12px;}
    .warehouse-map{display:flex;flex-direction:column;gap:14px;}
    .warehouse-column{border:1px solid #d1d5db;border-radius:6px;background:#fff;overflow:hidden;}
    .warehouse-column-title{background:#111827;color:#fff;padding:9px 12px;font-weight:800;}
    .warehouse-row{padding:10px 12px;border-top:1px solid #e5e7eb;}
    .warehouse-row-title{font-weight:800;color:#374151;margin-bottom:8px;}
    .warehouse-cells{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;}
    .warehouse-cell{border:1px solid #cbd5e1;border-radius:6px;padding:8px;background:#f8fafc;text-align:center;cursor:pointer;}
    .warehouse-cell input{margin-right:4px;}
    .warehouse-cell strong{display:block;font-size:16px;color:#111827;}
    .warehouse-cell span{font-size:12px;color:#475569;}
    .warehouse-cell.age-warning{background:#fffbeb;border-color:#f59e0b;}
    .warehouse-cell.age-danger{background:#fef2f2;border-color:#ef4444;}
    .warehouse-cell.age-ok{background:#f8fafc;border-color:#86efac;}
    .warehouse-cell-pending{display:inline-block;margin-top:5px;padding:2px 7px;border-radius:999px;background:#fee2e2;color:#991b1b!important;font-weight:800;}
    .warehouse-navigator{display:grid;grid-template-columns:.8fr .8fr 1fr 1.8fr;gap:12px;}
    .warehouse-navigator.has-plan{grid-template-columns:.55fr .55fr .75fr 1.45fr 1.25fr;}
    .warehouse-legend{grid-column:1/-1;display:flex;gap:14px;flex-wrap:wrap;align-items:center;color:#475569;font-size:13px;}
    .warehouse-legend i{display:inline-block;width:14px;height:14px;border-radius:3px;margin-right:4px;vertical-align:-2px;border:1px solid #cbd5e1;}
    .legend-ok{background:#f8fafc;border-color:#86efac!important;}
    .legend-warning{background:#fffbeb;border-color:#f59e0b!important;}
    .legend-danger{background:#fef2f2;border-color:#ef4444!important;}
    .warehouse-step{border:1px solid #d1d5db;border-radius:6px;background:#fff;min-height:220px;overflow:hidden;}
    .warehouse-step-title{background:#111827;color:#fff;padding:10px 12px;font-weight:800;}
    .warehouse-options{display:grid;grid-template-columns:1fr;gap:8px;padding:10px;}
    .warehouse-option{border:1px solid #cbd5e1;border-radius:6px;background:#f8fafc;padding:10px;text-align:center;cursor:pointer;}
    .warehouse-option.active{border-color:#0d6efd;background:#dbeafe;}
    .warehouse-option strong{display:block;font-size:18px;color:#111827;}
    .warehouse-option span{display:block;font-size:12px;color:#475569;}
    .warehouse-empty{color:#64748b;padding:12px;text-align:center;grid-column:1/-1;}
    .warehouse-cell.active{border-color:#0d6efd;box-shadow:0 0 0 2px rgba(13,110,253,.16);}
    .warehouse-products{padding:10px;display:flex;flex-direction:column;gap:8px;}
    .warehouse-product{border:1px solid #d1d5db;border-radius:6px;background:#fff;padding:9px;}
    .warehouse-product strong{display:block;font-size:15px;color:#111827;margin-bottom:6px;word-break:break-word;}
    .warehouse-product-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;text-align:center;margin-bottom:6px;}
    .warehouse-product-grid span{border:1px solid #e5e7eb;border-radius:5px;background:#f8fafc;padding:5px;font-size:11px;color:#475569;}
    .warehouse-product-grid b{font-size:16px;color:#111827;}
    .warehouse-product small{display:block;color:#64748b;font-size:12px;}
    .warehouse-plan-list{padding:10px;display:flex;flex-direction:column;gap:8px;}
    .warehouse-plan-row{display:grid;grid-template-columns:minmax(72px,1fr) auto auto auto;gap:8px;align-items:center;border:1px solid #d1d5db;border-radius:6px;background:#fff;padding:8px;}
    .warehouse-plan-row strong{font-size:15px;color:#111827;}
    .warehouse-plan-states{display:flex;gap:6px;align-items:center;}
    .warehouse-plan-lines{font-size:12px;font-weight:800;color:#475569;text-align:center;min-width:42px;}
    .inventory-state-icon{font-size:14px;}
    .inventory-state-icon.state-pending{color:#f59e0b;}
    .inventory-state-icon.state-progress{color:dodgerblue;}
    .inventory-state-icon.state-done{color:#16a34a;}
    @media (max-width:760px){
        .inventory-wrap{padding:10px 6px;}
        .inventory-command-bar{grid-template-columns:1fr;}
        .inventory-action-tiles{grid-template-columns:repeat(2,minmax(0,1fr));}
        .inventory-action-tile{min-height:82px;}
        .inventory-top-kpis{grid-template-columns:repeat(2,minmax(0,1fr));}
        .inventory-workflow-columns,.inventory-workflow-columns.two-columns{grid-template-columns:1fr;}
        .inventory-header{justify-content:center;text-align:center;}
        .inventory-actions{justify-content:center;}
        .inventory-kpis{grid-template-columns:repeat(2,minmax(0,1fr));}
        .inventory-title{font-size:26px;}
        .inventory-table th,.inventory-table td{font-size:13px;padding:6px;}
        .inventory-table .hide-mobile{display:none;}
        .warehouse-navigator,.warehouse-navigator.has-plan{grid-template-columns:1fr;}
    }
</style>
