<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Http\Controllers\Controller;
use App\Models\modules\productIssues\productIssues;

class productIssuesController extends Controller
{
    public $breadcrumbs = [];

    public function index(){
        
        $this->breadcrumbs[] = ['name' => 'sales', 'url' => route('sales.index')];
        $this->breadcrumbs[] = ['name' => 'Product issues', 'url' => route('productIssues.index'), 'no_translation' => 1];
        
        $data = [
            'breadcrumbs'=> $this->breadcrumbs,
            'productIssues' => productIssues::getProductIssues(),
            'htmlAfterUpload' => '',
        ];
        
        return View::make('customTools/productIssues/index')->with($data);
    }
    
    public function store(Request $request) {
        productIssues::saveData($request->all());
        return redirect()->route('productIssues.index');
    }

    public function edit($id) {
        
        $this->breadcrumbs[] = ['name' => 'sales', 'url' => route('sales.index')];
        $this->breadcrumbs[] = ['name' => 'Product issues', 'url' => route('productIssues.index'), 'no_translation' => 1];
        
        $data = [
            'htmlAfterUpload' => '',
            'breadcrumbs'=> $this->breadcrumbs,
            'issue'     => productIssues::getIssue($id)
        ];

        return View::make('customTools/productIssues/includes/edit')->with($data);
    }

    public function update(Request $request) {
        productIssues::updateData($request->all());
        return redirect()->route('productIssues.index');
    }
}
