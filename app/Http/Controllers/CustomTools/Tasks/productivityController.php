<?php

namespace App\Http\Controllers\CustomTools\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\modules\tasks\task;
use App\Models\modules\team\team;
use App\Models\User;

class productivityController extends Controller
{
    public function monthly(Request $request)
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $year     = (int) $request->input('year', now()->year);
        $monthNum = (int) $request->input('month_num', now()->month);

        $month = sprintf('%04d-%02d', $year, $monthNum);

        $start = Carbon::createFromDate($year, $monthNum, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $teams  = team::orderBy('name')->get();
        $teamId = $request->input('team'); // null => todos os departamentos

        $query = task::query()->whereBetween('task_date', [$start, $end]);
        if ($teamId) $query->where('id_team', (int) $teamId);

        // agora sim: atribuição vem de assigned_user_id
        $tasks = $query->with(['assignedUser:id,name,id_team', 'team'])->get();

        // ---------- STATUS COUNTS + POINTS ----------
        $allStatuses  = task::STATUS_ADMIN;
        $statusCounts = array_fill_keys($allStatuses, 0);

        $points  = 0;
        $done    = 0;
        $fail    = 0;
        $delayed = 0;

        foreach ($tasks as $t) {
            $eff = $t->effectiveStatus();

            if (!isset($statusCounts[$eff])) $statusCounts[$eff] = 0;
            $statusCounts[$eff]++;

            if ($eff === 'done') { $done++; $points += 1; }
            elseif ($eff === 'fail') { $fail++; $points -= 1; }
            elseif ($eff === 'delayed') { $delayed++; }
        }

        $den = $done + $fail + $delayed;
        $productivity = 0;
        
        if( count($tasks) > 0 ) $productivity = round( ( ( $done - $fail ) / count($tasks) ) * 100, 2);

        $stats = [
            'by_status'    => $statusCounts,
            'points'       => $points,
            'productivity' => $productivity,
            'done'         => $done,
            'fail'         => $fail,
            'delayed'      => $delayed,
        ];

        // ---------- USER STATS (só quando há team selecionada) ----------
        $userStats = [];
        $doneShareByUser = [];

        if ($teamId) {
            // todos os users da equipa (inclui manager)
            $teamUsers = User::query()
                ->where('id_team', (int) $teamId)
                ->orderBy('name')
                ->get(['id','name','id_team']);

            // inicializa a zeros
            foreach ($teamUsers as $u) {
                $userStats[$u->name] = ['total'=>0,'done'=>0,'fail'=>0,'delayed'=>0,'other'=>0];
            }

            // bucket opcional
            if (!isset($userStats['Unassigned'])) {
                $userStats['Unassigned'] = ['total'=>0,'done'=>0,'fail'=>0,'delayed'=>0,'other'=>0];
            }

            foreach ($tasks as $t) {
                $eff = $t->effectiveStatus();

                // responsável = assigned_user_id (se existir e for da team)
                $assigned = $t->assignedUser;
                $name = ($assigned && (int)$assigned->id_team === (int)$teamId)
                    ? $assigned->name
                    : 'Unassigned';

                if (!isset($userStats[$name])) {
                    $userStats[$name] = ['total'=>0,'done'=>0,'fail'=>0,'delayed'=>0,'other'=>0];
                }

                $userStats[$name]['total']++;

                if ($eff === 'done') $userStats[$name]['done']++;
                elseif ($eff === 'fail') $userStats[$name]['fail']++;
                elseif ($eff === 'delayed') $userStats[$name]['delayed']++;
                else $userStats[$name]['other']++;
            }

            // doughnut: DONE por user (inclui zeros)
            foreach ($teamUsers as $u) {
                $doneShareByUser[$u->name] = (int)($userStats[$u->name]['done'] ?? 0);
            }

            // se quiseres mostrar Unassigned no doughnut, descomenta:
            // $doneShareByUser['Unassigned'] = (int)($userStats['Unassigned']['done'] ?? 0);
        }

        return view('customTools.tasks.reports.monthly', compact(
            'month', 'teams', 'teamId', 'stats', 'tasks', 'userStats', 'doneShareByUser'
        ));
    }
    
    public function annual(Request $request)
    {
        abort_unless($request->user()?->role === 'admin', 403);
    
        $year   = (int) $request->input('year', now()->year);
        $teams  = team::orderBy('name')->get();
        $teamId = $request->input('team'); // null => todos os departamentos
    
        $startYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
        $endYear   = Carbon::createFromDate($year, 12, 31)->endOfDay();
    
        $query = task::query()->whereBetween('task_date', [$startYear, $endYear]);
        if ($teamId) $query->where('id_team', (int) $teamId);
    
        // mesma abordagem do monthly
        $tasks = $query->with(['assignedUser:id,name,id_team', 'team'])->get();
    
        // ---------- STATUS COUNTS + POINTS (ANO) ----------
        $allStatuses  = task::STATUS_ADMIN;
        $statusCounts = array_fill_keys($allStatuses, 0);
    
        $points  = 0;
        $done    = 0;
        $fail    = 0;
        $delayed = 0;
    
        foreach ($tasks as $t) {
            $eff = $t->effectiveStatus();
    
            if (!isset($statusCounts[$eff])) $statusCounts[$eff] = 0;
            $statusCounts[$eff]++;
    
            if ($eff === 'done') { $done++; $points += 1; }
            elseif ($eff === 'fail') { $fail++; $points -= 1; }
            elseif ($eff === 'delayed') { $delayed++; }
        }
    
        $productivity = 0;
        if (count($tasks) > 0) {
            $productivity = round(((($done - $fail) / count($tasks)) * 100), 2);
        }
    
        $stats = [
            'by_status'    => $statusCounts,
            'points'       => $points,
            'productivity' => $productivity,
            'done'         => $done,
            'fail'         => $fail,
            'delayed'      => $delayed,
        ];
    
        // ---------- MONTHLY (12 meses) ----------
        // prepara buckets
        $monthlyBuckets = [];
        for ($m = 1; $m <= 12; $m++) {
            $mm = str_pad((string)$m, 2, '0', STR_PAD_LEFT);
            $monthlyBuckets[$mm] = [
                'month' => $mm,
                'done' => 0,
                'fail' => 0,
                'delayed' => 0,
                'total' => 0,
                'productivity' => null,
            ];
        }
    
        foreach ($tasks as $t) {
            $mm = Carbon::parse($t->task_date)->format('m');
            $eff = $t->effectiveStatus();
    
            $monthlyBuckets[$mm]['total']++;
    
            if ($eff === 'done') $monthlyBuckets[$mm]['done']++;
            elseif ($eff === 'fail') $monthlyBuckets[$mm]['fail']++;
            elseif ($eff === 'delayed') $monthlyBuckets[$mm]['delayed']++;
        }
    
        $monthly = [];
        foreach ($monthlyBuckets as $mm => $row) {
            if ($row['total'] > 0) {
                $row['productivity'] = round(((($row['done'] - $row['fail']) / $row['total']) * 100), 2);
            } else {
                $row['productivity'] = null;
            }
            unset($row['total']); // não precisas na tabela, mas podes manter se quiseres
            $monthly[] = $row;
        }
    
        $valid = array_values(array_filter($monthly, fn($r) => $r['productivity'] !== null));
        $avg = count($valid)
            ? round(array_sum(array_column($valid, 'productivity')) / count($valid), 2)
            : null;
    
        $userStats = [];
        $doneShareByUser = [];
    
        if ($teamId) {
            $teamUsers = User::query()
                ->where('id_team', (int) $teamId)
                ->orderBy('name')
                ->get(['id','name','id_team']);
    
            foreach ($teamUsers as $u) {
                $userStats[$u->name] = ['total'=>0,'done'=>0,'fail'=>0,'delayed'=>0,'other'=>0];
            }
    
            if (!isset($userStats['Unassigned'])) {
                $userStats['Unassigned'] = ['total'=>0,'done'=>0,'fail'=>0,'delayed'=>0,'other'=>0];
            }
    
            foreach ($tasks as $t) {
                $eff = $t->effectiveStatus();
    
                $assigned = $t->assignedUser;
                $name = ($assigned && (int)$assigned->id_team === (int)$teamId)
                    ? $assigned->name
                    : 'Unassigned';
    
                if (!isset($userStats[$name])) {
                    $userStats[$name] = ['total'=>0,'done'=>0,'fail'=>0,'delayed'=>0,'other'=>0];
                }
    
                $userStats[$name]['total']++;
    
                if ($eff === 'done') $userStats[$name]['done']++;
                elseif ($eff === 'fail') $userStats[$name]['fail']++;
                elseif ($eff === 'delayed') $userStats[$name]['delayed']++;
                else $userStats[$name]['other']++;
            }
    
            // doughnut: DONE por user (inclui zeros)
            foreach ($teamUsers as $u) {
                $doneShareByUser[$u->name] = (int)($userStats[$u->name]['done'] ?? 0);
            }
    
            // se quiseres incluir Unassigned no doughnut:
            // $doneShareByUser['Unassigned'] = (int)($userStats['Unassigned']['done'] ?? 0);
        }
    
        return view('customTools.tasks.reports.annual', compact(
            'year', 'teams', 'teamId', 'monthly', 'avg',
            'stats', 'tasks', 'userStats', 'doneShareByUser'
        ));
    }

}
