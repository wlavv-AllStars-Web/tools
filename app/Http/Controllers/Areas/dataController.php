<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\modules\dashboard\dashboard;
use Throwable;

class dataController extends Controller
{
    public $breadcrumbs = [];
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('data'), 'url' => route('data.index')];
    }

    public function index(){
        $data = [
            'counters'      => dashboard::calculateAndGetCountersOfTab('data'),
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => []
        ];

        return View::make('areas/data/index')->with($data);
    }

    public function syncAsdImages(): RedirectResponse
    {
        try {
            Artisan::call('asd-images:sync');

            $output = trim(Artisan::output());

            return redirect()
                ->route('data.index')
                ->with('success', $output !== '' ? $output : 'ASD images verification completed.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('data.index')
                ->with('error', 'ASD images verification failed: ' . $e->getMessage());
        }
    }
}
