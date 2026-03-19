<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\Controller;

use App\Models\prestashop\orders;
use App\Models\prestashop\order_details;
use App\Models\prestashop\product;
use App\Models\prestashop\product_attribute;

use App\Models\modules\picking\picking;


class pickingController extends Controller
{
    public function index() {
        
        /**  Produtos removidos **/
        
        picking::add();
        return View::make('customTools/picking/index')->with( [ 'orders' => picking::getOrders() ] );
    }

    public function rowDone(Request $request) {
        return picking::rowDone( (object)$request->all() );
    }

    public function getEAN(Request $request) {
        return picking::getEAN( (object)$request->all() );
    }
}