<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Http\Controllers\Controller;

use App\Models\prestashop\orders;

use App\Models\modules\picking\picking;


class pickingController extends Controller
{
    public function index() {
        picking::add();
        return View::make('customTools/picking/index')->with( [ 'orders' => picking::getOrders() ] );
    }

    public function rowDone(Request $request) {
        return picking::rowDone( (object)$request->all() );
    }

    public function saveContainer(Request $request) {
        return picking::saveContainer( (object)$request->all() );
    }

    public function getEAN(Request $request) {
        return picking::getEAN( (object)$request->all() );
    }
}
