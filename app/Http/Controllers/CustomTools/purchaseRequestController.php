<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\modules\purchase_request\purchase_request;

class purchaseRequestController extends Controller
{

    public function index()
    {

        $this->breadcrumbs[] = [ 'name' =>  trans('Web'), 'url' => route('web.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('quote'), 'url' => route('quote.index')];

        $data = [
            'breadcrumbs'=> $this->breadcrumbs,
            'requests' => purchase_request::orderBy('created_at', 'desc')->get(),
            'htmlAfterUpload' => '',
        ];
        
        return View::make('customTools/purchaseRequests/index')->with($data);
    }

    public function create()
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Web'), 'url' => route('web.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('quote'), 'url' => route('quote.index')];
        $data = ['breadcrumbs'=> $this->breadcrumbs ];
        return View::make('customTools/purchaseRequests/create')->with($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_contact' => 'required|string|max:100',
            'request' => 'required|string|max:100',
            'language' => 'in:EN,ES,FR',
            'store' => 'in:ASM,ASD',
        ]);

        purchase_request::create(array_merge(
            ['first_contact_date' => now()->toDateString()],
            $request->all()
        ));

        return redirect()->route('quote.index')->with('success', 'Purchase request created successfully.');
    }

    public function edit(purchase_request $purchaseRequest)
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('Web'), 'url' => route('web.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('quote'), 'url' => route('quote.index')];
        $data = ['breadcrumbs'=> $this->breadcrumbs, 'purchaseRequest' => $purchaseRequest ];
        return View::make('customTools/purchaseRequests/edit')->with($data);
    }

    public function update(Request $request, purchase_request $purchaseRequest)
    {
        $request->validate([
            'customer_contact' => 'required|string|max:100',
            'request' => 'required|string|max:100',
            'language' => 'in:EN,ES,FR',
            'store' => 'in:ASM,ASD',
            'status' => 'in:new,waiting_supplier,quoted,client_notified,closed'
        ]);

        $purchaseRequest->update($request->all());
        return redirect()->route('quote.index')->with('success', 'Purchase request updated successfully.');
    }
}
