@extends('layouts.app')
@section('content')

    @include("customTools.stockEntry.js")

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
        <hr>
        <div style="width: 100%;" id="mainContainer">
            <div style="width: 100%;">
                <a class="btn btn-grey" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample" style="width: 100%;font-weight: bolder; font-size: 18px;background-color: #ddd;border: 1px solid #bbb;color: #666">
                <span style="float: left;">INFO</span>
                <span id="span_partial"     style="font-size: 14px; float: right;background-color: #956f55;    color: #fff; border-radius: 50px;padding: 2px 10px 2px 10px;margin: 0 5px;">3</span>
                <span id="span_backorder"   style="font-size: 14px; float: right;background-color: #f78e1f;    color: #fff; border-radius: 50px;padding: 2px 10px 2px 10px;margin: 0 5px;">2</span>
                <span id="span_preparation" style="font-size: 14px; float: right;background-color: dodgerblue; color: #fff; border-radius: 50px;padding: 2px 10px 2px 10px;margin: 0 5px;">1</span>
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
                    <span style="font-weight: bolder; font-size: 24px;color: orange;margin: 10px 0;" id="tag_weight">2.5Kg</span> <br>
                    <span style="font-size: 18px;color: #777;" id="tag_measures">47 X 82 X 30 ( cm )</span>
                </div>
                </div>
            <div style="display: none;width: 100%;" id="set_measures">
                <button class="btn btn-grey" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample1" aria-expanded="false" aria-controls="collapseExample1" style="width: 100%;font-weight: bolder; font-size: 18px;color: red;margin-top: 30px 0;background-color: #ccc;">WEIGHT / MEASUREMENTS</button>
                <div class="collapse" id="collapseExample1" style="margin-top: 10px;">
                    <div class="card card-body" style="background-color: #eee">
                    <table style="max-width: 100%;margin: 0 auto;">
                        <tr style="height: 40px;">
                            <td style="width: 40%;text-align: right;">MULTIBOX:</td>
                            <td> <input type="checkbox" name="multibox" value="0" style="margin-left: 10px;height: 25px;width: 25px;"></td>
                        </tr>
                        <tr style="height: 40px;">
                            <td style="width: 40%;text-align: right;">NON-STANDARD:</td>
                            <td> <input type="checkbox" name="fc" value="0" style="margin-left: 10px;height: 25px;width: 25px;"></td>
                        </tr>
                        <tr style="height: 40px;">
                            <td style="width: 40%;text-align: right;">WEIGHT ( Kg ):</td>
                            <td> <input type="text" name="weight" value="" placeholder="weight" style="margin-left: 10px;width: 65px;font-size: 18px;"></td>
                        </tr>
                        <tr style="height: 40px;">
                            <td style="width: 40%;text-align: right;">MEASUREMENTS ( cm ):</td>
                            <td> 
                                <input type="text" name="width" value="" placeholder="width" style="margin-left: 10px;width: 65px; float: left;font-size: 18px;">
                                <span style="margin: 10px; float: left;"> X </span>
                                <input type="text" name="height" value="" placeholder="height" style="width: 65px; float: left;font-size: 18px;">
                                <span style="margin: 10px; float: left;"> X </span>
                                <input type="text" name="depth" value="" placeholder="depth" style="width: 65px; float: left;font-size: 18px;">
                            </td>
                        </tr>
                        <tr style="height: 40px; text-align: center;">
                            <td colspan="2"> 
                                <button class="btn btn-info" type="button" style="width: 100%; margin: 0 auto; text-transform: uppercase; font-weight: bolder; font-size: 18px;">SAVE</button>
                            </td>
                        </tr>
                    </table>
                    </div>
                </div>
            </div>
            <hr>
            <div style="display: inline-block;width: 100%;text-align: center;">
                <table style="width: 100%;">
                    <tr style="height: 35px;">
                        <td colspan="2">
                            <input type="text" name="stock_entry" id="stock_entry" placeholder="Scan barcode" class="customInput20 text-center">
                        </td>
                    </tr>
                    <tr style="height: 40px;">
                        <td class="tagLeft">ORDERED:</td>
                        <td class="tagRight" id="tag_ordered">0</td>
                    </tr>
                    <tr style="height: 40px;">
                        <td class="tagLeft">BILLED:</td>
                        <td class="tagRight" id="tag_billed">0</td>
                    </tr>
                    <tr style="height: 40px;">
                        <td class="tagLeft">EXPECTING:</td>
                        <td class="tagRight" id="tag_expecting">0</td>
                    </tr>
                    <tr style="height: 40px;">
                        <td class="tagLeft">RECEIVED:</td>
                        <td class="tagRight">
                            <input type="hidden" id="tag_invoiced" placeholder="0" style="height: 20px;font-size: 18px;width: 100px;">
                            <input type="text" class="customInput20" name="received" id="tag_received" placeholder="0" readonly="true">
                        </td>
                    </tr>
                    <tr style="height: 35px;" id="saveEntryContainer">
                        <td colspan="2"><button type="button" onclick="saveStockEntry()" class="btn btn-success" style="width: calc( 250px);font-size: 18px;margin: 10px 0 0 0;">SAVE STOCK ENTRY</button></td>
                    </tr>
                </table>
            </div>
            <hr>
            <div style="width: 100%;">
                <button class="btn btn-warning" type="button" style="width: 48%; text-transform: uppercase; font-size: 18px; float: left; color: #FFF;" onclick="updateEAN()">UPDATE EAN13</button>
                <button class="btn btn-info"    type="button" style="width: 48%; text-transform: uppercase; font-size: 18px; float: right;color: #FFF;" onclick="report()">REPORT</button>
            </div>
        </div>
        <div style="display: none;width: 100%;" id="mainContainerEmpty">
            <div class="alert alert-danger" style="margin: 20px 10px;">NO DATA FOUND!</div>
        </div>
    </div>

@endsection