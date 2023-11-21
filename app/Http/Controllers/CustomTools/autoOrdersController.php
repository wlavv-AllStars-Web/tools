<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\modules\auto_orders\auto_orders;

class autoOrdersController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('customer.index')];
    }

    public function show(string $id){ 

        $this->breadcrumbs[] = [ 'name' => 'Entry form',   'url' => route('autoOrders.index', $id)];
        $this->actions[]     = [ ];

        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs
        ];

        return View::make('customTools/stockEntry/show')->with($data);
    }

    public function index() { 

        $this->breadcrumbs[] = [ 'name' => 'Entry form',   'url' => route('autoOrders.index')];
        $this->actions[]     = [ ];

        $auto_orders = self::getAutoOrders('https://www.allstarsmotorsport.fr/custom/api/autoOrders/list.php');

        $main_orders = auto_orders::checkAutoOrders($auto_orders);

        $exportLink = 'example';

        $data = [
            'actions'     => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'export_all'  => $exportLink,
            'auto_orders' => $main_orders
        ];

        return View::make('customTools/autoOrders/index')->with($data);
    }

    public function edit(string $id){        echo "autoOrdersController EDIT";    exit; }
    public function create(){                echo "autoOrdersController CREATE";  exit; }
    public function store(Request $request){ echo "autoOrdersController STORE";   exit; }

    public function getAutoOrders( $url ){

        $data = [];
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);
        
        if($response->getStatusCode() == 200) $data = json_decode($response->getBody(), true);

        return $data;
    }
}