<div id="loadFile" class="document-viewer">
    @php
        $documentPath = \App\Models\modules\documents_manager\documents_manager::documentPublicPath($document);
    @endphp
    <div class="document-viewer__grid">
        <div class="document-viewer__pdf-panel">        
            <div class="navbar navbar-light customPanel document-viewer__pdf-wrap">
                <embed src="{{ $documentPath }}?t={{rand()}}" class="document-viewer__pdf" type="application/pdf">
            </div>
        </div>
        <div class="document-viewer__details-panel">     
            <div class="navbar navbar-light customPanel document-viewer__details">
                <div style="text-align: center; border-bottom: 1px solid #ccc;margin-bottom: 10px;">
                    <h3>DOCUMENT DETAILS</h3>
                </div>
                <table id="documentDetailsTable" style="width: 100%;text-align: left;">
                    @if(strlen((string) $document->name) > 0)
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
                    
                    @if(strlen((string) $document->document_number) > 0)
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
                    
                    @if(strlen((string) $document->notes) > 0)
                    <tr>
                        <td colspan="1"><label class="documentLabel">NOTES:</label></td>
                        <td colspan="4">{{$document->notes}}</td>
                    </tr>
                    @endif

                    @if(strlen((string) $document->document_type) > 0)
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
    .document-viewer {
        width: 100%;
    }
    .document-viewer__grid {
        align-items: start;
        display: grid;
        gap: 12px;
        grid-template-columns: minmax(0, 1fr) 360px;
        width: 100%;
    }
    .document-viewer__pdf-panel,
    .document-viewer__details-panel {
        min-width: 0;
    }
    .document-viewer__pdf-wrap,
    .document-viewer__details {
        text-align: left;
        width: 100%;
    }
    .document-viewer__pdf {
        display: block;
        height: min(78vh, 820px);
        min-height: 620px;
        width: 100%;
    }
    .documentLabel{ font-weight: bolder; text-transform: uppercase; line-height: 2; text-align: right; float: right; margin-right: 10px; }
    .documentLabelManifest{ font-weight: bolder; text-transform: uppercase; line-height: 2; text-align: right; float: right; margin-right: 10px; }
    #documentDetailsTable{ line-height: 2; }
    @media (max-width: 1199px) {
        .document-viewer__grid {
            grid-template-columns: 1fr;
        }
        .document-viewer__pdf {
            height: 70vh;
            min-height: 520px;
        }
    }
</style>
