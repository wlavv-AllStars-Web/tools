@php
    $omsSearchRoute = request()->routeIs('admin.tools.oms.*') ? 'admin.tools.oms.search' : 'erp.oms.search';
@endphp
<div class="oms-global-search">
    <form method="GET" action="{{ route($omsSearchRoute) }}" class="d-flex gap-2 align-items-center">
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search OMS by parent/child reference, EAN or product name" autocomplete="off">
            <button type="submit" class="btn btn-primary px-4">Search</button>
        </div>
    </form>
</div>
<style>
.oms-global-search{margin:10px 0 0;padding:10px;background:#fff;border:1px solid rgba(20,33,61,.10);box-shadow:0 4px 14px rgba(15,23,42,.05)}
.oms-global-search .form-control,.oms-global-search .input-group-text,.oms-global-search .btn{border-radius:5px}
</style>