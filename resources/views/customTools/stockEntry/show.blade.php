@extends('layouts.app')
@section('content')

    @include("customTools.stockEntry.includes.js")

    <div class="navbar navbar-light customPanel" style="max-width: 500px;">
        <div style="display: inline-block;width: 100%;">
            <div style="width: calc( 100% - 100px ); float: left;">   
                <input class="customInput20 text-center" type="text" name="search" id="search" placeholder="Insert tag to search"> 
            </div>
            <div style="width: 50px; float: left;text-align: right;padding: 0 10px;"> 
                <button class="btn btn-dark"><i class="fa-solid fa-barcode" onclick="ajaxCall('getProductByEAN')"></i></button> 
            </div>
            <div style="width: 50px; float: left;text-align: right;"> 
                <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass" onclick="ajaxCall('getProductByRef')"></i></button> 
            </div>
        </div>
        <div style="width: 100%;display: none;" id="mainContainer">
            <div id="container_select_order" style="margin-top: 20px;"></div>
            <input type="hidden" value="0" id="selected_order" name="selected_order">
            <hr>
            <div id="entryStockContainer" style="width: 100%;display: none;">
                
                <div style="text-align:center;"> 
                    <span style="padding: 5px 0; font-size: 18px;">ORDER REFERENCE: </span> 
                    <span id="tag_order_reference" style="padding: 5px; font-size: 18px;font-weight: bold;color: dodgerblue;"></span>
                </div>
                
                <div style="text-align:center;"> 
                    <span style="padding: 5px 0; font-size: 18px;">PRODUCT REFERENCE: </span> 
                    <span id="tag_product_reference" style="padding: 5px; font-size: 18px;font-weight: bold;color: red;"></span>
                </div>
                <input type="hidden" id="id_bms_procurement_purchase_order_product" name="id_bms_procurement_purchase_order_product" value="0">
                    
                <div style="width: 100%;margin-top: 10px;">
                    <a class="btn btn-grey" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample" style="width: 100%;font-weight: bolder; font-size: 18px;background-color: #ddd;border: 1px solid #bbb;color: #666">
                    <span style="float: left;">INFO</span>
                    <span id="span_partial"     style="font-size: 14px; float: right;background-color: #956f55;    color: #fff; border-radius: 50px;padding: 2px 10px 2px 10px;margin: 0 5px;">0</span>
                    <span id="span_backorder"   style="font-size: 14px; float: right;background-color: #f78e1f;    color: #fff; border-radius: 50px;padding: 2px 10px 2px 10px;margin: 0 5px;">0</span>
                    <span id="span_preparation" style="font-size: 14px; float: right;background-color: dodgerblue; color: #fff; border-radius: 50px;padding: 2px 10px 2px 10px;margin: 0 5px;">0</span>
                    <span id="span_new"         style="font-size: 14px; float: right;background-color: pink;       color: pink; border-radius: 50px;padding: 2px 10px 2px 10px;margin: 0 5px;border: 1px solid #FF7CA8">0</span>
                    </a>
                    <div class="collapse" id="collapseExample" style="margin-top: 10px;">
                        <div class="card card-body" style="background-color: #eee">
                            <table style="width: 400px;margin: 0 auto;">
                                <tr>
                                    <td style="width: 100px;text-align: right;">NEW:</td>
                                    <td style="text-align: left;" id="tag_new"></td>
                                </tr>
                                <tr>
                                    <td style="width: 100px;text-align: right;">LOCATION:</td>
                                    <td style="text-align: left;" id="tag_location"></td>
                                </tr>
                                <tr>
                                    <td style="width: 100px;text-align: right;">BACKORDER:</td>
                                    <td style="text-align: left;" id="container_backorders"></td>
                                </tr>
                                <tr>
                                    <td style="width: 100px;text-align: right;">PARTIAL:</td>
                                    <td style="text-align: left;" id="container_partials"></td>
                                </tr>
                                <tr>
                                    <td style="width: 100px;text-align: right;">PREPARATIONS:</td>
                                    <td style="text-align: left;" id="container_preparations"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div style="display: inline-block;width: 100%;" id="mearues_set">
                <div class="text-center" style="padding: 5px;margin: 5px 0;">
                        <span style="font-size: 18px;color: #777;">WEIGHT / MEASUREMENTS</span> <br>
                        <span style="font-weight: bolder; font-size: 24px;color: orange;margin: 10px 0;" id="tag_weight">- Kg</span> <br>
                        <span style="font-size: 18px;color: #777;" id="tag_measures">- X - X - ( cm )</span>
                    </div>
                    </div>
                <div style="display: none;width: 100%;" id="set_measures">
                    <hr>
                    <button class="btn btn-grey" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample1" aria-expanded="false" aria-controls="collapseExample1" style="width: 100%;font-weight: bolder; font-size: 18px;color: red;margin-top: 30px 0;background-color: #ddd;">WEIGHT / MEASUREMENTS</button>
                    <div class="collapse" id="collapseExample1" style="margin-top: 10px;">
                        <div class="card card-body" style="background-color: #eee">
                            <form name="measurementsForm" id="measurementsForm" method="POST" action="#">
                                {{ csrf_field() }}
                                @method('PUT')

                                <input type="hidden" id="idProductMeasurementsForm" name="id_product" value="0">
                                <input type="hidden" id="idProductAttributeMeasurementsForm" name="id_product_attribute" value="0">
                                <input type="hidden" id="referenceMeasurementsForm" name="reference" value="0">
                                
                                <table style="max-width: 100%;margin: 0 auto;">
                                    <tr style="height: 40px;">
                                        <td style="width: 40%;text-align: right;">MULTIBOX:</td>
                                        <td> <input type="text" id="multiboxMeasurementsForm" name="multibox" value="1" style="margin-left: 10px;height: 25px;width: 25px;"></td>
                                    </tr>
                                    <tr style="height: 40px;">
                                        <td style="width: 40%;text-align: right;">NON-STANDARD:</td>
                                        <td> <input type="checkbox" id="fcMeasurementsForm" name="fc" value="1" style="margin-left: 10px;height: 25px;width: 25px;"></td>
                                    </tr>
                                    <tr style="height: 40px;">
                                        <td style="width: 40%;text-align: right;">WEIGHT ( Kg ):</td>
                                        <td> <input type="text" id="weightMeasurementsForm" name="weight" value="" placeholder="weight" style="margin-left: 10px;width: 65px;font-size: 18px;"></td>
                                    </tr>
                                    <tr style="height: 40px;">
                                        <td style="width: 40%;text-align: right;">MEASUREMENTS ( cm ):</td>
                                        <td> 
                                            <input type="text" id="widthMeasurementsForm" name="width" value="" placeholder="width" style="margin-left: 10px;width: 65px; float: left;font-size: 18px;">
                                            <span style="margin: 10px; float: left;"> X </span>
                                            <input type="text" id="heightMeasurementsForm" name="height" value="" placeholder="height" style="width: 65px; float: left;font-size: 18px;">
                                            <span style="margin: 10px; float: left;"> X </span>
                                            <input type="text" id="depthMeasurementsForm" name="depth" value="" placeholder="depth" style="width: 65px; float: left;font-size: 18px;">
                                        </td>
                                    </tr>
                                    <tr style="height: 40px; text-align: center;">
                                        <td colspan="2"> 
                                            <button class="btn btn-info" type="button" onclick="saveMeasurementForm()" style="width: 100%; margin: 0 auto; text-transform: uppercase; font-weight: bolder; font-size: 18px;">SAVE</button>
                                        </td>
                                    </tr>
                                </table>   
                            </form>                                 
                        </div>
                    </div>
                </div>
                <hr>
                <div style="display: inline-block;width: 100%;text-align: center;">
                    <table style="width: 100%;">
                        <tr style="height: 35px;">
                            <td colspan="4">
                                <input type="text" name="stock_entry" id="stock_entry" placeholder="Scan barcode" class="customInput20 text-center" onkeyup="addQuantity()">
                            </td>
                        </tr>
                        <tr style="height: 60px;">
                            <td style="width: 25%">
                                <div>ORDERED</div>
                                <div id="tag_ordered">0</div>
                            </td>
                            <td style="width: 25%">
                                <div>BILLED</div>
                                <div id="tag_billed">0</div>
                            </td>
                            <td style="width: 25%">
                                <div>EXPECTING</div>
                                <div id="tag_expecting">0</div>
                            </td>
                            <td style="width: 25%">
                                <div>
                                    <div style="margin-top: 10px;">RECEIVED</div>
                                    <input type="hidden" id="tag_invoiced" placeholder="0">
                                    <input type="text" class="customInput20" name="received" id="tag_received" placeholder="0" readonly="true" style="height: 30px;text-align:center;font-size: 16px;padding: 0;margin: 0;color: #000;">
                                </div>
                            </td>
                        </tr>
                        <tr style="height: 35px;" id="saveEntryContainer">
                            <td colspan="4"><button type="button" onclick="saveStockEntry()" class="btn btn-success" style="width: calc( 250px);font-size: 18px;margin: 10px 0 0 0;">SAVE STOCK ENTRY</button></td>
                        </tr>
                    </table>
                </div>
                <div style="width: 100%;">
                    <hr>
                    <button class="btn btn-warning" type="button" style="width: 48%; text-transform: uppercase; font-size: 18px; float: left; color: #FFF;" onclick="updateEAN()">UPDATE EAN13</button>
                    <button class="btn btn-info"    type="button" style="width: 48%; text-transform: uppercase; font-size: 18px; float: right;color: #FFF;" onclick="report()">REPORT</button>
                </div>
                <div style="width: 100%; display: none;" id="reportsContainer">
                    <hr>
                    <div id="containerReportButton">
                        <div>
                            <div style="text-align: center;">NOTES</div>    
                            <textarea id="reportMessage" cols="6" name="reportMessage" style="margin: 0 5px;width: calc( 100% - 10px );height: 100px;border-radius: 5px;border: 1px solid #ddd;resize: none;"></textarea>
                        </div>
                        <button class="btn btn-secondary" type="button" style="text-transform: uppercase; width: calc( 50% - 10px ); float: left; margin: 5px;" onclick="sendReport('DAMAGED PRODUCT')">DAMAGED PRODUCT</button>
                        <button class="btn btn-secondary" type="button" style="text-transform: uppercase; width: calc( 50% - 10px ); float: left; margin: 5px;" onclick="sendReport('BOX SIZE DIFFERENT')">BOX SIZE DIFFERENT</button>
                        <button class="btn btn-secondary" type="button" style="text-transform: uppercase; width: calc( 50% - 10px ); float: left; margin: 5px;" onclick="sendReport('EAN-13 HAVE CHANGED')">EAN-13 HAVE CHANGED</button>
                        <button class="btn btn-secondary" type="button" style="text-transform: uppercase; width: calc( 50% - 10px ); float: left; margin: 5px;" onclick="sendReport('REFERENCE HAVE CHANGED')">REFERENCE HAVE CHANGED</button>
                    </div>
                    <div id="containerReportResponse"> </div>
                </div>
            </div>
        </div>
        <div style="display: none;width: 100%;" id="mainContainerEmpty">
            <div class="alert alert-danger" style="margin: 20px 10px;">NO DATA FOUND!</div>
        </div>
    </div>

@endsection