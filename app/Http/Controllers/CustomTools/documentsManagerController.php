<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;

use App\Models\prestashop\suppliers;
use App\Models\modules\documents_manager\documents_manager;

class documentsManagerController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('messages.Documents Manager'), 'url' => route('documentsManager.index')];
        $this->actions[]     = [ 'name' => 'ADD DOCUMENT', 'icon' => '<i class="f-left fa-regular fa-folder"></i>', 'url' => route('documentsManager.addDocument'), 'class' => "btn btn-success"];
    }

    public function index(){
        
        $data = [ 
            'actions'    => $this->actions, 
            'breadcrumbs'=> $this->breadcrumbs, 
            'filters'  => documents_manager::getFilterStructure() 
        ];
        
        return View::make('customTools/documentsManager/index')->with( $data );
    }

    public function listDocuments($category, $element){
        
        $this->breadcrumbs[] = [ 'name' =>  $category, 'url' => route('documentsManager.index'), 'no_translation' => 1];

        $searchCategory = $category;

        $data = [ 
            'actions'    => $this->actions, 
            'breadcrumbs'=> $this->breadcrumbs, 
            'searchCategory' => $searchCategory,
            'documents'  => documents_manager::all($category, $element) 
        ];
        
        return View::make('customTools/documentsManager/list')->with( $data );
    }

    public function addDocument(){
        
        $this->breadcrumbs[] = [ 'name' =>  trans('messages.Add document'), 'url' => route('documentsManager.addDocument')];
        
        $data= [
            'breadcrumbs'=> $this->breadcrumbs,   
            'htmlAfterUpload' => '',
            'suppliers' => self::getSuppliers(),
            'carriers' => self::getCarriers(),
            'services' => self::getServices(),
            'carriersDocuments' => self::getDocumentsType(),
        ];
        return View::make('customTools/documentsManager/add')->with($data);
    }

    private function getSuppliers(){ 
        return suppliers::orderBy('name', 'ASC')->pluck('name', 'id_supplier');
    }

    private function getCarriers(){  
        return [ 'AbreuLogistics', 'AMTransitários', 'DPD', 'GARLAND', 'GLS', 'LusoCargo', 'MAERSK', 'NACEX', 'NIPPON', 'RHENUS', 'SCHENKER', 'TNT | FEDEX', 'Torrestir', 'UPS', 'Warelog', 'Despachantes' ]; 
    }
    
    
    
    /** DOCUMENTS **/
    private function getDocumentsType(){
        return [ 'Invoice', 'Credit Note' ];
    }

    private function getServices(){
        return [ 
            '<option value="All Stars Web Solutions">All Stars Web Solutions</option>',
            '<option value="Euro Rider">Euro Rider</option>',
            '<option value="TranscarPremium">TranscarPremium</option>',
            '<optgroup label="All Stars Distribution">',
                '<option value="All Stars Distribution.amazon">Amazon</option>',
                '<option value="All Stars Distribution.Animais">Ani+</option>',
                '<option value="All Stars Distribution.Web Solutions">Web Solution</option>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Consumíveis">',
                    '<option value="All Stars Distribution.Consumíveis.Cartucho">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cartucho</option>',
                    '<option value="All Stars Distribution.Consumíveis.Chinos">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Chinos</option>',
                    '<option value="All Stars Distribution.Consumíveis.Diversos">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Diversos</option>',
                    '<option value="All Stars Distribution.Consumíveis.CTT">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CTT</option>',
                    '<option value="All Stars Distribution.Consumíveis.Marketing">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Marketing</option>',
                    '<option value="All Stars Distribution.Consumíveis.Soumad">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Soumad</option>',
                    '<option value="All Stars Distribution.Consumíveis.Supermercado">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Supermercado</option>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Limpeza">',
                    '<option value="All Stars Distribution.Limpeza.Clearnort">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Clearnort</option>',
                    '<option value="All Stars Distribution.Limpeza.PrimeClean">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;PrimeClean</option>',
                    '<option value="All Stars Distribution.Limpeza.Venafil">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Venafil</option>',
                    '<option value="All Stars Distribution.Limpeza.Outros">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Outros</option>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Veículos">',
                    '<option value="All Stars Distribution.Veículos.Combustivel">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Combustível</option>',
                    '<option value="All Stars Distribution.Veículos.BMW">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;BMW</option>',
                    '<option value="All Stars Distribution.Veículos.Inspeção">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Inspeção</option>',
                    '<option value="All Stars Distribution.Veículos.Diversos">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Diversos</option>',
                    '<option value="All Stars Distribution.Veículos.Leasing">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Leasing</option>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Logística">',
                    '<option value="All Stars Distribution.Logística.100metros">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;100metros</option>',
                    '<option value="All Stars Distribution.Logística.MVM">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;MVM</option>',
                    '<option value="All Stars Distribution.Logística.Raja">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Raja</option>',
                    '<option value="All Stars Distribution.Logística.Sealed Air">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sealed Air</option>',
                    '<option value="All Stars Distribution.Logística.Smurfit">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Smurfit</option>',
                    '<option value="All Stars Distribution.Logística.Outros">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Outros</option>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Pavilhão">',
                    '<option value="All Stars Distribution.Pavilhão.Renda">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Renda</option>',
                    '<option value="All Stars Distribution.Pavilhão.Gastos">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Gastos</option>',
                    '<option value="All Stars Distribution.Pavilhão.Valença">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Valença</option>',
                    '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RH">',
                        '<option value="All Stars Distribution.Pavilhão.RH.WorkView">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WorkView</option>',
                        '<option value="All Stars Distribution.Pavilhão.RH.Contabilidade">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Contabilidade</option>',
                    '</optgroup>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Refeição">',
                    '<option value="All Stars Distribution.Refeição.Restaurantes">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Restaurantes</option>',
                    '<option value="All Stars Distribution.Refeição.Outros">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Outros</option>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Seguros">',
                    '<option value="All Stars Distribution.Seguros.Veículos">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Veículos</option>',
                    '<option value="All Stars Distribution.Seguros.Edifícios">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Edifícios</option>',
                    '<option value="All Stars Distribution.Seguros.RH">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;RH</option>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Software">',
                    '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Prestação serviços">',
                        '<option value="All Stars Distribution.Software.Prestação serviços.Websp">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Websp</option>',
                        '<option value="All Stars Distribution.Software.Prestação serviços.Vodafone">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Vodafone</option>',
                        '<option value="All Stars Distribution.Software.Prestação serviços.ASWS">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ASWS</option>',
                        '<option value="All Stars Distribution.Software.Prestação serviços.Cloudflare">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Cloudflare</option>',
                    '</optgroup>',
                    '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Programa">',
                        '<option value="All Stars Distribution.Software.Programa.Adobe">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Adobe</option>',
                        '<option value="All Stars Distribution.Software.Programa.Microsoft">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Microsoft</option>',
                        '<option value="All Stars Distribution.Software.Programa.Tesla">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tesla</option>',
                    '</optgroup>',
                    '<option value="All Stars Distribution.Software.Outros">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Outros</option>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Viagem">',
                    '<option value="All Stars Distribution.Viagem.Via Verde">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Via Verde</option>',
                    '<option value="All Stars Distribution.Viagem.Viagem">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Viagem</option>',
                    '<option value="All Stars Distribution.Viagem.Outros">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Outros</option>',
                '</optgroup>',
                '<optgroup label="&nbsp;&nbsp;&nbsp;&nbsp;Estado">',
                    '<option value="All Stars Distribution.Estado.Recibos verdes">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Recibos verdes</option>',
                    '<option value="All Stars Distribution.Estado.Veículos">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Veículos</option>',
                    '<option value="All Stars Distribution.Estado.Mercadoria">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mercadoria</option>',
                '</optgroup>',
            '</optgroup>',
        ];        
    }
    
    public function store(Request $request){ return documents_manager::saveData( $request->all() ); }
    
    public function destroy(Request $request){ return documents_manager::destroyData( $request->all() ); }

    public function search(Request $request){ 
        $documents = documents_manager::search( $request->all() );
        return view('customTools/documentsManager/listRow', compact('documents'))->render();
    }

    public function loadFile(Request $request){ 
        $document = documents_manager::loadDocumentData( $request->id_document);
        return view('customTools/documentsManager/showDocumentData', compact('document'))->render();
    }

    public function listSearch(Request $request){ 
        
        $documents = documents_manager::listSearchData( $request->all() );
        
        $searchName = $request->name;
        $searchNumber = $request->number;
        $searchCategory = $request->category;
        $searchDate = $request->date;

        return view('customTools/documentsManager/listSearch', compact('documents', 'searchName', 'searchNumber', 'searchCategory', 'searchDate'))->render();
    }
    
    
}