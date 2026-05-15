<?php
namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use App\Models\modules\safetyCheck\safetyCheck;

class SafetyCheckController extends Controller
{
    public $actions;
    public $breadcrumbs;

    private array $fields = [
        'estado_cabos' => 'Estado Cabos',
        'estado_geral' => 'Estado Geral',
        'torre' => 'Torre',
        'elevacao' => 'Elevação',
        'direcao' => 'Direção',
        'travao' => 'Travão',
        'travao_emergencia' => 'Travão Emergência',
        'travao_estacionamento' => 'Travão Estacionamento',
        'comandos' => 'Comandos',
        'garfos' => 'Garfos',
        'buzina' => 'Buzina',
    ];
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('Logistics'), 'url' => route('logistics.index')];
    }

    public function index(){

        $this->breadcrumbs[] = [ 'name' =>  trans('customTools.safety.index'), 'url' => route('customTools.safety.index')];

        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => null,
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('customTools/safety-check/index')->with($data);
    }

    public function store(Request $request)
    {
        $exists = SafetyCheck::where('equipment', $request->equipment)->whereDate('created_at', today())->exists();

        if ($exists) return back()->with('error', 'Já existe um registo hoje para este equipamento');
        
        $data = $request->all();
        $data['user_id'] = auth()->id();

        SafetyCheck::create($data);
        return redirect()->route('customTools.safety.index')->with('success', 'Guardado com sucesso');
    }

    public function history(Request $request)
    {
        $this->breadcrumbs[] = [ 'name' =>  trans('customTools.safety.index'), 'url' => route('customTools.safety.index')];

        $query = SafetyCheck::query();
        if ($request->year)  $query->whereYear('created_at', $request->year);
        if ($request->month) $query->whereMonth('created_at', $request->month);
        $checks = $query->latest()->get();

        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => null,
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'checks'        => $checks,
            'fields'        => $this->fields,
        ];
        return View::make('customTools/safety-check/history')->with($data);
    }

    public function exportCsv(Request $request){
        $query = SafetyCheck::query();
    
        if ($request->year)  $query->whereYear('created_at', $request->year);
        if ($request->month) $query->whereMonth('created_at', $request->month);

        $checks = $query->latest()->get();
    
        $filename = "safety_check_" . date('Ymd_His') . ".csv";
    
        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];
    
        $columns = [
            'id',
            'equipment',
            ...array_keys($this->fields),
            'observacoes',
            'user_id',
            'created_at'
        ];
    
        $callback = function () use ($checks, $columns) {
            $file = fopen('php://output', 'w');
    
            fputcsv($file, $columns, ';');
    
            foreach ($checks as $check) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = isset($this->fields[$col]) && !$this->fieldAppliesToEquipment($col, (string) $check->equipment)
                        ? 'N/A'
                        : $check->$col;
                }
                fputcsv($file, $row, ';');
            }
    
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function fieldAppliesToEquipment(string $field, string $equipment): bool
    {
        if (str_contains($equipment, 'plataforma')) {
            return !in_array($field, ['garfos', 'travao_emergencia', 'buzina'], true);
        }

        return true;
    }
}
