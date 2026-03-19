<div id="loadFile" style="display: flex;position: fixed;">
    <div class="row">
        <div class="col-lg-8">        
            <div class="navbar navbar-light customPanel" style="text-align: left; width: 100%;">
                
                @if( ( strlen( $document->element) > 0 ) && ( $document->element != 'others' ))
                    <embed src="https://webtools.all-stars-motorsport.com/uploads/documents/{{$document->category}}/{{str_replace('.', '/', str_replace(' ', '', $document->element))}}/{{str_replace('/', '|', $document->document)}}?t={{rand()}}" width="750px" height="750px" type="application/pdf" style="margin: 0 auto;">
                @elseif( ( $document->category == 'manifest' ) && ( strlen( $document->element) > 0 ) && ( $document->element == 'others' ))
                    <embed src="https://webtools.all-stars-motorsport.com/uploads/documents/manifest/manifest{{$document->document}}?t={{rand()}}" width="750px" height="750px" type="application/pdf" style="margin: 0 auto;">
                @else
                    <embed src="https://webtools.all-stars-motorsport.com/uploads/documents/{{str_replace('.', '/', $document->category)}}/{{$document->document}}?t={{rand()}}" width="750px" height="750px" type="application/pdf" style="margin: 0 auto;">
                @endif
            </div>
        </div>
        <div class="col-lg-4">     
            <div class="navbar navbar-light customPanel" style="text-align: left; width: 100%;">
                <div style="text-align: center; border-bottom: 1px solid #ccc;margin-bottom: 10px;">
                    <h3>DOCUMENT DETAILS</h3>
                </div>
                <table id="documentDetailsTable" style="width: 100%;text-align: left;">
                    @if(strlen($document->name) > 0)
                    <tr>
                        <td colspan="1"><label class="documentLabel">NAME:</label></td>
                        <td colspan="4">{{$document->name}}</td>
                    </tr>
                    @endif
                    
                    <tr>
                        <td colspan="1" style="width: 120px !important;"><label class="documentLabel">DATE:</label></td>
                        <td colspan="4">{{$document->day}}/{{$document->month}}/{{$document->year}}</td>
                    </tr>
                    
                    <tr>
                        <td colspan="1"><label class="documentLabel">CATEGORY:</label></td>
                        <td colspan="4" style="text-transform: uppercase;">{{$document->category}}</td>
                    </tr>
                    @if( $document->element != 'others')
                    <tr>
                        <td colspan="1"><label class="documentLabel">SUB-CAT.:</label></td>
                        <td colspan="4" style="text-transform: uppercase;">{!!str_replace('.', ' <i class="fa-solid fa-chevron-right" style="color: dodgerblue; font-weight: bolder;"></i> ', $document->element)!!}</td>
                    </tr>
                    @endif
                    
                    @if(strlen($document->document_number) > 0)
                    <tr>
                        <td colspan="1"><label class="documentLabel"># DOCUMENT:</label></td>
                        <td colspan="4" style="text-transform: uppercase;">{{$document->document_number}}</td>
                    </tr>
                    @endif

                    @if($document->total_ex_vat > 0)
                    <tr>
                        <td colspan="1"><label class="documentLabel">TOTAL ExVAT:</label></td>
                        <td colspan="4">{{number_format($document->total_ex_vat, 2, ',', ' ')}} €</td>
                    </tr>
                    @endif

                    @if($document->total_vat > 0)
                    <tr>
                        <td colspan="1"><label class="documentLabel">VAT:</label></td>
                        <td colspan="4">{{number_format($document->total_vat, 2, ',', ' ')}} €</td>
                    </tr>
                    @endif

                    @if($document->total > 0)
                    <tr>
                        <td colspan="1"><label class="documentLabel">TOTAL:</label></td>
                        <td colspan="4">{{number_format($document->total, 2, ',', ' ')}} €</td>
                    </tr>
                    @endif
                    
                    @if(strlen($document->notes) > 0)
                    <tr>
                        <td colspan="1"><label class="documentLabel">NOTES:</label></td>
                        <td colspan="4">{{$document->notes}}</td>
                    </tr>
                    @endif

                    @if(strlen($document->document_type) > 0)
                    <tr>
                        <td colspan="1"><label class="documentLabel">DOC. TYPE:</label></td>
                        <td colspan="4" style="text-transform: uppercase;">{{$document->document_type}}</td>
                    </tr>
                    @endif
                    
                    @if( $document->document_type == 'manifest')
                    <tr>
                        <td colspan="5"><div style="width: 100; height: 20px;"></div> </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="3">
                            <table id="documentDetailsTable" style="width: 100%;text-align: left;">
                                <tr>
                                    <td style="width: 50%;"><label class="documentLabelManifest">DPD: </label></td>
                                    <td>{{$document->DPD + 0}}</td>
                                </tr>
                                <tr>
                                    <td><label class="documentLabelManifest">Mondial Relay: </label></td>
                                    <td>{{$document->GLS + 0}}</td>
                                </tr>
                                <tr>
                                    <td><label class="documentLabelManifest">NACEX: </label></td>
                                    <td>{{$document->NACEX + 0}}</td>
                                </tr>
                                <tr>
                                    <td><label class="documentLabelManifest">TNT: </label></td>
                                    <td>{{$document->TNT + 0}}</td>
                                </tr>
                                <tr>
                                    <td><label class="documentLabelManifest">UPS: </label></td>
                                    <td>{{$document->UPS + 0}}</td>
                                </tr>                            
                            </table>
                        </td>
                    </tr>
                    @endif
                </table>                
            </div>
        </div>
    </div>
</div> 

<style>
    .documentLabel{ font-weight: bolder; text-transform: uppercase; line-height: 2; text-align: right; float: right; margin-right: 10px; }
    .documentLabelManifest{ font-weight: bolder; text-transform: uppercase; line-height: 2; text-align: right; float: right; margin-right: 10px; }
    #documentDetailsTable{ line-height: 2; }
</style>