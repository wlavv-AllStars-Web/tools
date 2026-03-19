<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\quotes\quotes;
use Illuminate\Http\Request;

class quotesController extends Controller{
    
    public function index(){
        return view('customTools.quotes.index');
    }

    public function data(){
        $quotes = quotes::query()->orderByDesc('id')->get([ 'id', 'referencia', 'brand', 'notas_front', 'price', 'lead', 'notas_back', 'status', 'updated_at' ]);
        return response()->json(['data' => $quotes]);
    }

    public function store(Request $request){
        
        $validated = $request->validate([
            'referencia'   => ['required', 'string', 'max:100'],
            'brand'        => ['required', 'string', 'max:120'],
            'notas_front'  => ['nullable', 'string'],
            'price'        => ['nullable', 'numeric', 'min:0'],
            'lead'         => ['nullable', 'string', 'max:120'],
            'notas_back'   => ['nullable', 'string'],
        ]);

        $quote = quotes::create(array_merge($validated, [ 'status' => 'new' ]));

        return response()->json([ 'message' => 'Criado com sucesso.', 'quote' => $quote ], 201);
    }

    public function update(Request $request, $id){
        
        $quote = quotes::findOrFail($id);

        $validated = $request->validate([
            'referencia'   => ['required', 'string', 'max:100'],
            'brand'        => ['required', 'string', 'max:120'],
            'notas_front'  => ['nullable', 'string'],
            'price'        => ['nullable', 'numeric', 'min:0'],
            'lead'         => ['nullable', 'string', 'max:120'],
            'notas_back'   => ['nullable', 'string'],
        ]);

        $quote->fill($validated);
        $quote->status = 'quoted';
        $quote->save();

        return response()->json([ 'message' => 'Atualizado com sucesso.', 'quote' => $quote ]);
    }

    public function destroy($id){
        
        $quote = quotes::findOrFail($id);
        
        if ($quote->status === 'quoted') return response()->json([ 'message' => 'Não é possível remover um pedido com estado quoted.' ], 422);
        
        $quote->delete();

        return response()->json([ 'message' => 'Removido com sucesso.' ]);
    }
}
