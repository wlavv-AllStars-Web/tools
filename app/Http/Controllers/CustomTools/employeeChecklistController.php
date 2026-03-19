<?php 
namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\modules\checklist\daily_checklist;
use App\Models\modules\checklist\checklist_statusChange;
use App\Models\modules\checklist\daily_checklist_notes;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class employeeChecklistController extends Controller
{
    public function today() {
        // $tasks = daily_task::with('template')
        //     ->where('employee_id', auth()->id())
        //     ->whereDate('for_date', today())
        //     ->orderBy('created_at','asc')
        //     ->get();
        $user = auth()->user();
        $allowedDepartments = [$user->department_id];
        
        if ($user->permanent) {
            $allowedDepartments[] = 0;
        }
        
        
        $tasks = daily_checklist::query()
            ->select('daily_checklist.*', 'checklist_templates.active')
            ->leftJoin('checklist_templates', 'daily_checklist.template_id', '=', 'checklist_templates.id')
            ->whereIn('daily_checklist.department_id', $allowedDepartments)
            ->where('checklist_templates.deleted', '!=', 1)
            ->whereDate('daily_checklist.for_date', today())
            /**
            ->orderByRaw("CASE WHEN daily_checklist.status = 'done' THEN 1 ELSE 0 END ASC") // non-done first
            ->orderByRaw("
                CASE 
                    WHEN daily_checklist.status != 'done' THEN checklist_templates.title 
                    ELSE '' 
                END ASC
            ")
            **/
            ->orderBy('checklist_templates.position', 'asc')
            ->get();
            
        
        $groupedTasks = $tasks->groupBy('department_id');
        
        $deptNotes = daily_checklist_notes::whereIn('id_department', $allowedDepartments)
            ->whereDate('updated_at', Carbon::today())
            ->pluck('note', 'id_department') // returns [dept_id => note_string]
            ->toArray();
    
        return view('customTools.checklist.today', compact('groupedTasks', 'deptNotes'));
    }
    
    public function updateNote(Request $request)
    {
        $request->validate([
            'note' => 'nullable|string|max:5000',
            'department_id' => 'required|integer|exists:daily_checklist,department_id',
        ]);
    
        $departmentId = $request->department_id;
        $userId = auth()->id();
    
        daily_checklist_notes::updateOrCreate(
            ['id_department' => $departmentId],
            [
                'note' => $request->note,
                'id_user' => $userId,
                'updated_at' => now(),
            ]
        );
    
        return response()->json(['success' => true]);
    }
    

    public function history(Request $request, $departmentId)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        $isToday = $date->isToday();
    
        // Only admins can see other departments — optional safety
        if (!auth()->user()->isAdmin()) {
            $departmentId = auth()->user()->department_id;
        }
    
        // Fetch all employees in this department
        $employees = User::where('department_id', $departmentId)->get();
        
    
        // If no employees, show empty result
        if ($employees->isEmpty()) {
            $paginatedTasks = collect();
            $uniqueTasks = collect();
            $departmentName = 'Departamento não encontrado';
        } else {
            // Get employee IDs
            $employeeIds = $employees->pluck('id');
    
            $query = daily_checklist::with([
                'template',
                'template.statusChanges',
                'employee',
                'changedByUser'
            ])
            ->where('department_id', $departmentId)
            ->when($date, fn($q) => $q->whereDate('for_date', $date));
    
            if ($isToday) {
                $query->whereHas('template', function ($q) {
                    $q->where('deleted', 0);
                });
            }
    
            $paginatedTasks = $query->orderBy('updated_at', 'desc')->paginate(60);
    
            $uniqueTasks = $paginatedTasks->groupBy('template_id')
                ->map(function ($group) {
                    return $group->sortByDesc('updated_at')->first();
                })
                ->values();
                
            
    
            // Get department name (using your accessor)
            $departmentName = $employees->first()->department_name ?? 'Geral';
        }
        
        $deptNotes = daily_checklist_notes::where('id_department', $departmentId);
        
        // Only filter by date if $date is provided
        if ($date) {
            $deptNotes->whereDate('created_at', $date);
        }
        
        $deptNotes = $deptNotes->pluck('note', 'id_department')->toArray();
        
        // dd($date);
    
        // Pass department info instead of employee
        return view('customTools.checklist.history', compact(
            'uniqueTasks',
            'date',
            'departmentName',   
            'paginatedTasks',
            'departmentId',
            'deptNotes'
        ));
    }


    public function updateStatus(Request $request, daily_checklist $task) {
        // abort_unless($task->employee_id === auth()->id(), 403);

        $data = $request->validate([
            'status' => ['required','in:pending,done,not_done'],
            // 'notes'  => ['nullable','string'],
        ]);

        $from = $task->status;
        $task->update($data);
        
        
        $statusChange = checklist_statusChange::where('daily_checklist_id', $task->id)->first();

        if ($statusChange) {
            // Update existing record
            $statusChange->update([
                'changed_by'  => auth()->id(),
                'from_status' => $from,
                'to_status'   => $task->status,
                'changed_at'  => now(),
            ]);
        } else {
            // Create new record
            checklist_statusChange::create([
                'daily_checklist_id' => $task->id,
                'changed_by'         => auth()->id(),
                'from_status'        => $from,
                'to_status'          => $task->status,
                'changed_at'         => now(),
            ]);
        }

        return response()->json(['success' => true, 'status' => $task->status]);
    }
}