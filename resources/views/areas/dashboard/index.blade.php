@extends('layouts.app')

@section('content')
<div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="col-lg-12">
                        <div class="mobile_panels">
                            <div class="apps_icon_container">
                                <div style="text-align: center">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModulePicking&amp;token=dd4d9d04c5ac4935ca7390e1b56b9fd8', '_blank')">
                                        <img src="/admin/icons/picking.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>PICKING</div>
                                    </a>
                                </div>
                            </div>
                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModule&amp;token=c7e6df2b4c5c998d82da167db8502792&amp;action=find_housing', '_blank')">
                                        <img src="/admin/icons/find_housing.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>FIND HOUSING</div>
                                    </a>
                                </div>
                            </div>

                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModule&amp;token=c7e6df2b4c5c998d82da167db8502792', '_blank')">
                                        <img src="/admin/icons/housing.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>HOUSING</div>
                                    </a>
                                </div>
                            </div>

                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                <a href="{{route('stockEntry.index')}}" title="Stock Check">
                                        <img src="/admin/icons/stock_check.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>STOCK CHECK</div>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a href="{{ route('stockEntry.index') }}" title="Stock entry">
                                        <img src="/admin/icons/stock.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>STOCK ENTRY</div>
                                    </a>
                                </div>
                            </div>
                        
                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModuleStocks&amp;token=811de942c3f68b1ffcb91ba0ef7c0229&amp;action=find_housing', '_blank')">
                                        <img src="/admin/icons/iventory.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>INVENTORY</div>
                                    </a>
                                </div>
                            </div>
                                                    
                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModuleInventoryTracker&amp;token=c174e56e4b7cf40270cc5957304bb90a', '_blank')">
                                        <img src="/admin/icons/shelf_cleaning.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>Shelf Cleaning</div>
                                    </a>
                                </div>
                            </div>
                                                
                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModuleTable&amp;token=77e95209d48fafa9966a1ecf6fa0ae2f&amp;action=returns_web_app', '_blank')">
                                        <img src="/admin/icons/returns.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>RETURNS</div>
                                    </a>
                                </div>
                            </div>
                        
                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModuleTable&amp;token=77e95209d48fafa9966a1ecf6fa0ae2f&amp;action=plataformas_check_web_app', '_blank')">
                                        <img src="/admin/icons/safety_check.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>SAFETY CHECK</div>
                                    </a>
                                </div>
                            </div>
                                                
                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModuleTable&amp;token=77e95209d48fafa9966a1ecf6fa0ae2f&amp;action=check_shippings_web_app', '_blank')">
                                        <img src="/admin/icons/carrier_check.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>CARRIER CHECK</div>
                                    </a>
                                </div>
                            </div>
                        
                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="window.open('/admin77500/index.php?controller=AdminWmModuleToDo&amp;token=ffe4561d0ee714d0d6ac7f5663a02251', '_blank')">
                                        <img src="/admin/icons/doit.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>TO DO</div>
                                    </a>
                                </div>
                            </div>

                            <div class="apps_icon_container">
                                <div style="text-align: center;">
                                    <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <img src="/admin/icons/logout.jpg" style="width: 100%;border-radius: 5px;">
                                        <div>LOGOUT</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection