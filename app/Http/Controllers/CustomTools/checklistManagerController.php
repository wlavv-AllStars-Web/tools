<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

use App\Http\Controllers\Controller;

use App\Models\modules\checklist\checklist;
// use App\Models\modules\checklist\todo_list;
use App\Models\modules\checklist\checklist_templates;
use App\Models\modules\checklist\daily_checklist;
use App\Models\User;
use Auth;

// use App\Models\modules\inventory_manager\inventory_manager;
// use App\Models\modules\inventory_manager\inventory_movement;


class checklistManagerController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  trans('messages.Checklist Manager'), 'url' => route('checklist.index')];
        // $this->actions[]     = [ 'name' => 'Remove Item', 'icon' => '<i class="fa-solid fa-trash"></i>', 'url' => route('inventoryManager.remove'), 'class' => "btn btn-error"];
    }

    public function index()
    {
        $query = checklist_templates::with([
            'employee',
            'dailyTasks' => function ($q) {
                $q->where('for_date', today());
            }
        ]);
    
        if (auth()->id() === 2 || auth()->id() === 104) {
            // Super admin? Show all with main_task = true
            $query->whereHas('dailyTasks', function ($q) {
                $q->where('main_task', true);
            });
        } else {
            $user = auth()->user();
            $userDept = $user->department_id;
    
            // Default: only user's department
            $allowedDepartments = [$userDept];
    
            // If user is "permanent", also include department 0
            if ($user->permanent) {
                $allowedDepartments[] = 0;
            }
    
            $query->whereIn('department_id', $allowedDepartments)
                  ->where('active', true)
                  ->whereHas('dailyTasks', function ($q) {
                      $q->where('main_task', true);
                  });
        }

    
        $templates = $query->latest()->paginate(60);
    
        // Always group by employee
        $grouped = $templates->groupBy('department_id');
        
        // dd($grouped);
    
        return view('customTools.checklist.index', compact('grouped', 'templates'));
    }
    
    public function create() {
        // $employees = User::where('admin_id', auth()->id())->orderBy('name')->get();
        $departments = User::whereNotNull('department_id')
                    ->get()
                    ->unique('department_id')
                    ->pluck('department_name', 'department_id')
                    ->toArray();
        
        // dd($departments);
        return view('customTools.checklist.create', compact('departments'));
    }
    
    public function store(Request $request) {
        $data = $request->validate([
            'department_id' => ['required','exists:users,department_id'],
            'title' => ['required','string','max:255'],
            // 'description' => ['nullable','string'],
            'active' => ['sometimes','boolean'],
            // 'priority'    => ['required','in:1,2,3'],
        ]);
        
        $data['admin_id'] = auth()->id();
        $data['main_task'] = 1;
        
        
        $template = checklist_templates::create($data);

        
        daily_checklist::create([
            'for_date'    => today(),
            'template_id' => $template->id,
            'admin_id'    => $template->admin_id,
            'department_id' => $template->department_id,
            'status'      => 'pending',
            // 'notes'       => $request->notes,
            // 'state_priority'    => $request->priority,
            'main_task'   => $data['main_task'],
        ]);
        
        return redirect()->route('checklist.index')->with('ok','Template criada.');
    }
    
    public function edit($taskId, checklist_templates $template) {
        // $this->authorize('manage', $template);
        // $employees = auth()->user()->employees()->orderBy('name')->get();
        
        
        // If $taskId is provided, fetch that daily_task
        $task = $taskId ? $template->dailyTasks()->findOrFail($taskId) : null;
        
        $department = $task ? $task->department_id: null;
        
        
        // dd($employee);
        
        return view('customTools.checklist.edit', compact('template','department','task'));
    }

    public function update(Request $request, checklist_templates $template) {
        // $this->authorize('manage', $template);
        

        $data = $request->validate([
            'department_id' => ['required','exists:users,department_id'],
            'title' => ['required','string','max:255'],
            // 'description' => ['nullable','string'],
            'active' => ['sometimes','boolean'],
            // 'priority'    => ['required','in:1,2,3'],
            'task_id'    => ['required','exists:daily_checklist,id'],
        ]);
        
        
        
        $newData = [
            'admin_id'    => $template->admin_id,
            'department_id' => $data['department_id'],
            'title'       => $data['title'],
            // 'description' => $template->description,
            'active'      => $request->has('active') ? 1 : 0,
            'deleted'     => 0, 
        ];
        
        
        // dd($template);

        $template->update(['deleted' => 1]);
        daily_checklist::where('template_id', $template->id)->update([
            'main_task' => 0
        ]);
        
        
        $newTemplate = checklist_templates::create($newData);
        
        
        // daily_checklist upd
        daily_checklist::create([
            'for_date'       => today(),
            'template_id'    => $newTemplate->id,
            'admin_id'       => $newTemplate->admin_id,
            'department_id'    => $newTemplate->department_id,
            'status'         => 'pending',
            'notes'          => null,
            'state_priority' => 3,
            'main_task'      => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        

        
        return redirect()->route('checklist.index')->with('ok','Template criada.');
    }
    
    public function destroy(checklist_templates $template) {
        // $this->authorize('manage', $template);
        $template->delete();
        return back()->with('ok','Template removida.');
    }


    public function assignEmployees(User $employee)
    {
        // List all admins
        $admins = User::where('role', 'admin')->get();
        $employees = User::where('role', 'employee')->get();
    
        return view('customTools.checklist.assignEmployees', compact('employees', 'admins'));
    }
        
    public function updateEmployeeAdmins(Request $request) {
        $input = $request->input('employee_ids', []);
    
        foreach($input as $adminId => $employeeIds) {
            // Reset previous assignments for this admin
            User::where('admin_id', $adminId)->update(['admin_id' => null]);
    
            // Assign selected employees to this admin
            User::whereIn('id', $employeeIds)->update(['admin_id' => $adminId]);
        }
    
        return back()->with('ok', 'Employees updated for admins.');
    }


}
