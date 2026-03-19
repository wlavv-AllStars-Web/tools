<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\asg_tasks\AsgTask;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

use App\Models\modules\logs\logs;

class AsgTasksController extends Controller{ 
    
    private const DEPARTMENTS = [
        1 => 'ACCOUNTING',
        2 => 'ADMIN',
        3 => 'DATA',
        4 => 'LOGISTICS',
        5 => 'MARKETING',
        6 => 'PURCHASE',
        7 => 'SALES',
        8 => 'SHOP',
        9 => 'WEB',
    ];

    private const ADMIN_STATUSES = [ 'NEW', 'DELAYED', 'FAIL', 'OK', 'RE-TASK' ];
    private const USER_STATUSES  = [ 'PENDING', 'DONE!', 'WAITING INFO' ];
    private const ALL_STATUSES   = [ 'NEW','DELAYED','FAIL','OK','RE-TASK', 'PENDING','DONE!','WAITING INFO' ];

    private function isAdmin($user): bool{
        return ($user->role ?? null) === 'admin';
    }

    private function userTeamId($user): int{
        return (int)$user->id_team + 0;
    }
        
    public function index(Request $request)
    {
        $user    = $request->user();
        $isAdmin = $this->isAdmin($user);
    
        // TEAM
        if ($isAdmin) {
            $teamId = (int) $request->query('team', 1);
            if (!array_key_exists($teamId, self::DEPARTMENTS)) $teamId = 1;
        } else {
            $teamId = $this->userTeamId($user);
        }
    
        // YEAR / MONTH (defaults = atuais)
        $year  = (int) $request->query('year', (int) now()->format('Y'));
        $month = (int) $request->query('month', (int) now()->format('n'));
    
        if ($year < 2000 || $year > 2100) $year = (int) now()->format('Y');
        if ($month < 1 || $month > 12)   $month = (int) now()->format('n');
    
        // WEEK (optional)
        $week = $request->query('week', null);
        $week = ($week === '' || $week === null) ? null : (int) $week;
        if ($week !== null && ($week < 1 || $week > 53)) $week = null;
    
        // Intervalo do mês [start, end)
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = (clone $start)->addMonth()->startOfDay();
    
        // Query base (dept + mês)
        $q = AsgTask::query()
            ->where('id_team', $teamId)
            ->whereDate('task_date', '>=', $start->toDateString())
            ->whereDate('task_date', '<',  $end->toDateString());
    
        // Se week selecionada: adiciona filtro por id_week
        if ($week !== null) {
            $q->where('id_week', $week);
        }
    
        $tasks = $q->orderBy('id_week')
            ->orderByDesc('task_date')
            ->orderByDesc('id')
            ->get();
    
        $tasksByWeek = $tasks->groupBy('id_week');
    
        // Weeks disponíveis no mês (para o dropdown)
        $availableWeeks = collect();
        $cursor = (clone $start);
        while ($cursor->lt($end)) {
            $availableWeeks->push((int) $cursor->format('W'));
            $cursor->addWeek();
        }
        $availableWeeks = $availableWeeks->unique()->sortDesc()->values();
    
        // weeksToRender
        if ($week !== null) {
            // Mostra sempre a week selecionada (mesmo vazia) para permitir criar (admin)
            $weeksToRender = collect([$week]);
        } else {
            // Mostra todas as weeks com tasks + (opcional) incluir semana atual se for o mês atual e admin
            $weeksToRender = $tasksByWeek->keys()->sortDesc()->values();
    
            $nowYear  = (int) now()->format('Y');
            $nowMonth = (int) now()->format('n');
            if ($isAdmin && $year === $nowYear && $month === $nowMonth) {
                $weeksToRender = $weeksToRender->merge([(int) now()->format('W')])->unique()->sortDesc()->values();
            }
    
            // Se não houver tasks nenhumas, ainda assim renderiza pelo menos a 1ª week do mês (admin criar)
            if ($weeksToRender->isEmpty()) {
                $weeksToRender = collect([$availableWeeks->first() ?? (int) now()->format('W')]);
            }
        }
    
        return view('customTools.asg_tasks.index', [
            'isAdmin'        => $isAdmin,
            'teamId'         => $teamId,
            'departments'    => self::DEPARTMENTS,
    
            'tasksByWeek'    => $tasksByWeek,
            'weeksToRender'  => $weeksToRender,
    
            'availableWeeks' => $availableWeeks,
            'year'           => $year,
            'month'          => $month,
            'week'           => $week,
    
            'adminStatuses'  => self::ADMIN_STATUSES,
            'userStatuses'   => self::USER_STATUSES,
        ]);
    }

    public function store(Request $request){
        
        $user = $request->user();
    
        if (!$this->isAdmin($user)) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }
    
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'comment'     => ['nullable', 'string', 'max:500'],
            'id_team'     => ['required', 'integer', Rule::in(array_keys(self::DEPARTMENTS))],
            'task_date'   => ['nullable', 'date'],
            'status'      => ['required', 'string', Rule::in(self::ALL_STATUSES)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
    
        $taskDate = !empty($data['task_date'])
            ? Carbon::parse($data['task_date'])
            : now();
    
        $data['task_date']      = $taskDate->toDateString();
        $data['id_week']        = (int) $taskDate->format('W');
        $data['status_changed'] = $user->id;
    
        $task = AsgTask::create($data);
    
        $this->createLog( $request, $user->id, 'CREATE', 'INFO', "Task criada | ID: {$task->id} | Team: {$task->id_team} | Status: {$task->status}" );
    
        return response()->json(['ok' => true, 'task' => $task]);
    }
    
    public function inlineUpdate(Request $request, AsgTask $task){
        
        $user = $request->user();
        $isAdmin = $this->isAdmin($user);
    
        if (!$isAdmin && $task->id_team !== $this->userTeamId($user)) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }
    
        $field = (string) $request->input('field');
        $value = $request->input('value');
    
        $adminEditable = ['title','comment','id_team','task_date','id_week','status','time_allowed'];
        $userEditable  = ['status','description'];
    
        if ($isAdmin) {
            if (!in_array($field, $adminEditable, true)) {
                return response()->json(['ok' => false, 'error' => 'Field not allowed'], 422);
            }
        } else {
            if (!in_array($field, $userEditable, true)) {
                return response()->json(['ok' => false, 'error' => 'Field not allowed'], 422);
            }
    
            if ($field === 'status' && !in_array((string)$value, self::USER_STATUSES, true)) {
                return response()->json(['ok' => false, 'error' => 'Invalid status for user'], 422);
            }
        }
    
        $allowedStatuses = $isAdmin
            ? self::ALL_STATUSES
            : self::USER_STATUSES;
    
        $rulesMap = [
            'title'       => ['required', 'string', 'max:255'],
            'comment'     => ['nullable', 'string', 'max:500'],
            'id_team'     => ['required', 'integer', Rule::in(array_keys(self::DEPARTMENTS))],
            'task_date'   => ['nullable', 'date'],
            'id_week'     => ['required', 'integer', 'min:1', 'max:53'],
            'time_allowed'=> ['required', 'string', 'max:255'],
            'status'      => ['required', 'string', Rule::in($allowedStatuses)],
            'description' => ['nullable', 'string', 'max:5000'],
        ];
    
        if (!isset($rulesMap[$field])) {
            return response()->json(['ok' => false, 'error' => 'Invalid field ' . $field], 422);
        }
    
        $validated = validator(
            ['value' => $value],
            ['value' => $rulesMap[$field]]
        )->validate();
    
        $oldValue = $task->{$field};
    
        $task->{$field} = $validated['value'];
    
        if ($field === 'task_date') {
            $dt = Carbon::parse($validated['value']);
            $task->id_week = (int) $dt->format('W');
        }
    
        if ($field === 'status') $task->status_changed = $user->id;

        $task->save();
    
        $severity = 'INFO';
        if ($field === 'status' && in_array($validated['value'], ['FAIL','DELAYED'])) $severity = 'WARNING';

        $this->createLog( $request, $user->id, 'UPDATE', $severity, "Task ID {$task->id} alterada | Campo: {$field} | De: {$oldValue} | Para: {$validated['value']}" );
    
        return response()->json(['ok' => true]);
    }
    
    private function createLog(Request $request, int $userId, string $action, string $severity, string $description): void{
        
        logs::create([
            'user_id'    => $userId,
            'action'     => $action,
            'module'     => 'TASKS',
            'route'      => $request->path(),
            'method'     => $request->method(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'severity'   => $severity,
            'description'=> $description,
            'created_at' => now(),
        ]);
    }
    
}