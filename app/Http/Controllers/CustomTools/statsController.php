<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\prestashop\order_payment;

use App\Models\prestashop\orders;

use App\Models\prestashop\CurrencyVariation;

use Illuminate\Support\Facades\View;

class statsController extends Controller
{
    private static array $monthlyObjectives2026 = [
        1  => 626742.50,
        2  => 668217.05,
        3  => 751077.31,
        4  => 704223.34,
        5  => 919476.54,
        6  => 776166.63,
        7  => 796304.06,
        8  => 588787.74,
        9  => 777729.58,
        10 => 829700.34,
        11 => 897809.57,
        12 => 732557.54,
    ];
    
    private static $expectedEvolution = 1.15;

    private static float $objective2026 = 9068792.19;

    public function daily_stats()
    {
        $byMonth = order_payment::objectiveByMonth();

        $asdActual = order_payment::getASDActual();
        $asdHomologo = order_payment::getASDHomologo();

        $months = [
            0  => self::getMonthlyValue($byMonth[1],  'January',   null, 1),
            1  => self::getMonthlyValue($byMonth[2],  'February',  null, 2),
            2  => self::getMonthlyValue($byMonth[3],  'March',     null, 3),
            3  => self::getMonthlyValue($byMonth[4],  'April',     null, 4),
            4  => self::getMonthlyValue($byMonth[5],  'May',       null, 5),
            5  => self::getMonthlyValue($byMonth[6],  'June',      null, 6),
            6  => self::getMonthlyValue($byMonth[7],  'July',      null, 7),
            7  => self::getMonthlyValue($byMonth[8],  'August',    null, 8),
            8  => self::getMonthlyValue($byMonth[9],  'September', null, 9),
            9  => self::getMonthlyValue($byMonth[10], 'October',   null, 10),
            10 => self::getMonthlyValue($byMonth[11], 'November',  null, 11),
            11 => self::getMonthlyValue($byMonth[12], 'December',  null, 12),
            12 => self::getMonthlyValueTotal($byMonth, 'Total'),
        ];

        $counters = order_payment::getCounters();
        $goals = order_payment::projection(1);

        // Use the configured monthly objective for the daily target. The
        // projection total is based on last year's homologous sales.
        $currentMonthObjective = self::$monthlyObjectives2026[(int) date('n')] ?? 0;
        $goals->totalMonthObjectivoValue = $currentMonthObjective;
        $goals->totalMonthObjectivo = number_format($currentMonthObjective, 2, ',', ' ')
            . ' ' . html_entity_decode('&euro;');

        $asdValue = $asdActual->actual ?? 0;

        $actualUntilToday = $counters->getActual + $asdValue;
        $lastYearUntilToday = $counters->getAcumuladoAnoAnteriorHomologo
            + ($asdHomologo->actual ?? 0);
        $objectiveUntilTodayValue = self::getObjectiveUntilToday();

        $side = (object)[
            'until_today' => number_format($actualUntilToday, 2, ',', ' ') . ' €',
            'accumulated_last_year_until_now' => number_format($lastYearUntilToday, 2, ',', ' ') . ' ' . html_entity_decode('&euro;'),
            'difference' => number_format(($actualUntilToday - $lastYearUntilToday), 2, ',', ' ') . ' ' . html_entity_decode('&euro;'),
            'objective_until_today' => number_format($objectiveUntilTodayValue, 2, ',', ' ') . ' €',
            'difference_objective_until_today' => number_format(($actualUntilToday - $objectiveUntilTodayValue), 2, ',', ' ') . ' €',
            'objective' => number_format(self::$objective2026, 2, ',', ' ') . ' €',
            'missing_to_objective' => number_format((self::$objective2026 - $actualUntilToday), 2, ',', ' ') . ' €',
        ];

        return response()->json([
            'html' => view('areas/dashboard/includes/daily_stats', compact('months', 'side', 'goals'))->render()
        ]);
    }

    private function getMonthlyValue($data, $name, $data_extra = null, $monthNumber = null)
    {
        $current = ($data->current ?? 0) + ($data_extra->current ?? 0);
        $lastYear = ($data->lastYear ?? 0) + ($data_extra->lastYear ?? 0);

        $objective = self::$monthlyObjectives2026[$monthNumber] ?? 0;
        $difference = $current - $objective;

        return (object)[
            'name' => $name,
            'accomplished' => number_format($current, 2, ',', ' ') . ' €',
            'last_year' => number_format($lastYear, 2, ',', ' ') . ' €',
            'objective' => number_format($objective, 2, ',', ' ') . ' €',
            'difference' => number_format($difference, 2, ',', ' ') . ' €',
        ];
    }

    private function getMonthlyValueTotal($data, $name, $data_extra = null)
    {
        $total_current = 0;
        $total_lastYear = 0;
    
        foreach ($data as $key => $month) {
            $total_current += ($data[$key]->current ?? 0) + ($data_extra[$key]->current ?? 0);
            $total_lastYear += ($data[$key]->lastYear ?? 0) + ($data_extra[$key]->lastYear ?? 0);
        }
    
        return (object)[
            'name' => $name,
            'accomplished' => number_format($total_current, 2, ',', ' ') . ' €',
            'last_year' => number_format($total_lastYear, 2, ',', ' ') . ' €',
            'objective' => number_format(self::$objective2026, 2, ',', ' ') . ' €',
            'difference' => number_format(($total_current - self::$objective2026), 2, ',', ' ') . ' €',
        ];
    }

    private static function getObjectiveUntilToday(): float
    {
        $today = Carbon::now();

        $currentMonth = $today->month;
        $currentDay = $today->day;
        $daysInMonth = $today->daysInMonth;

        $objective = 0;

        foreach (self::$monthlyObjectives2026 as $month => $monthObjective) {

            if ($month < $currentMonth) {
                $objective += $monthObjective;
                continue;
            }

            if ($month === $currentMonth) {
                $objective += ($monthObjective / $daysInMonth) * $currentDay;
                break;
            }
        }

        return $objective;
    }
    
    public function kpi()
    {
        return $this->renderKpi('areas/dashboard/dashboard');
    }

    public function adminKpi()
    {
        abort_unless(in_array((int) auth()->id(), [2, 94, 43], true), 403);

        return $this->renderKpi('areas/dashboard/dashboard-admin');
    }

    private function renderKpi($view)
    {
        $asd = orders::getCounters(3, self::$expectedEvolution);
        $asm = orders::getCounters(2, self::$expectedEvolution);
        $er  = orders::getCounters(1, self::$expectedEvolution);
        $em  = orders::getCounters(4, self::$expectedEvolution);
    
        $yesterday_forcast  = ($asm->yesterday_forcast  + $asd->yesterday_forcast  + $er->yesterday_forcast  + $em->yesterday_forcast);
        $yesterday_realized = ($asm->yesterday_realized + $asd->yesterday_realized + $er->yesterday_realized + $em->yesterday_realized);
    
        $today_forcast = ($asm->today_forcast + $asd->today_forcast + $er->today_forcast + $em->today_forcast);
        $today_realized = ($asm->today_realized + $asd->today_realized + $er->today_realized + $em->today_realized);
    
        $currency_rates = CurrencyVariation::orderBy('id', 'DESC')->first();
    
        $currentMonth = (int) date('n');
        $currentDay   = (int) date('j');
        $daysInMonth  = (int) date('t');
    
        $monthGoal = self::$monthlyObjectives2026[$currentMonth] ?? 0;
        $annualGoal = self::$objective2026;
    
        $dailyGoal = $daysInMonth > 0
            ? $monthGoal / $daysInMonth
            : 0;

        // The KPI forecast represents the configured daily objective, using
        // the same source and calculation as the Daily dashboard.
        $today_forcast = $dailyGoal;
        $yesterday_forcast = $dailyGoal;
    
        $objectiveUntilToday = $dailyGoal * $currentDay;
    
        $realised_until_today = order_payment::getActualCurrentMonthForShops([1, 2, 3, 4]);
    
        $status = $objectiveUntilToday > 0
            ? ($realised_until_today / $objectiveUntilToday) * 100
            : 0;
    
        $status_color = 'red';
    
        if ($status > 99.99) {
            $status_color = 'darkgreen';
        }
    
        if ($status > 104.99) {
            $status_color = 'dodgerblue';
        }
    
        $goal_until_today = $objectiveUntilToday > 0
            ? (($realised_until_today - $objectiveUntilToday) / $objectiveUntilToday) * 100
            : 0;
    
        $monthProgress = $monthGoal > 0
            ? ($realised_until_today / $monthGoal) * 100
            : 0;
            
        $data = [
            'awaiting' =>   ($asm->awaiting    + $asd->awaiting    + $er->awaiting     + $em->awaiting),
            'packing' =>    ($asm->packing     + $asd->packing     + $er->packing      + $em->packing),
            'shipped' =>    ($asm->shipped     + $asd->shipped     + $er->shipped      + $em->shipped),
            'warranty' =>   ($asm->warranty    + $asd->warranty    + $er->warranty     + $em->warranty),
            'backorders' => ($asm->backorders  + $asd->backorders  + $er->backorders   + $em->backorders),
            'partial' =>    ($asm->partial     + $asd->partial     + $er->partial      + $em->partial),
            'pending' =>    ($asm->pending     + $asd->pending     + $er->pending      + $em->pending),
    
            'group_result' => number_format($today_realized, 2, ',', ' ') . ' €',
    
            'realised_until_today' => $realised_until_today,
            'objective_until_today' => $objectiveUntilToday,
            'acumulado_ano_passado' => 0,
    
            'status_color' => $status_color,
            'status' => $status,
    
            'daillyGoal' => $dailyGoal,
    
            'monthGoal' => $monthGoal,
            'monthGoalValue' => number_format($monthGoal, 2, ',', ' ') . ' €',
            'monthDays' => $daysInMonth,
            'progress' => $monthProgress,
    
            'annualGoal' => $annualGoal,
            'annualGoalValue' => number_format($annualGoal, 2, ',', ' ') . ' €',
    
            'today' => (object)[
                'forcast' => number_format($today_forcast, 2, ',', ' ') . ' €',
                'forcast_value' => $today_forcast,
                'realized' => number_format($today_realized, 2, ',', ' ') . ' €',
                'realized_value' => $today_realized,
                'reached' => ($today_realized > $today_forcast) ? 1 : 0,
                'goal_until_today' => $goal_until_today,
                'goal_until_today_new' => $goal_until_today,
                'percent' => $objectiveUntilToday > 0
                    ? ($realised_until_today / $objectiveUntilToday)
                    : 0,
            ],
    
            'yesterday' => (object)[
                'forcast' => number_format($yesterday_forcast, 2, ',', ' ') . ' €',
                'forcast_value' => $yesterday_forcast,
                'realized' => number_format($yesterday_realized, 2, ',', ' ') . ' €',
                'realized_value' => $yesterday_realized,
                'reached' => ($yesterday_realized > $yesterday_forcast) ? 1 : 0,
            ],
    
            'realized_asm' => number_format($asm->today_realized, 2, ',', ' ') . ' €',
            'realized_asd' => number_format($asd->today_realized, 2, ',', ' ') . ' €',
            'realized_er'  => number_format($er->today_realized,  2, ',', ' ') . ' €',
            'realized_em'  => number_format($em->today_realized,  2, ',', ' ') . ' €',
    
            'forcast_asm' => number_format($asm->today_forcast, 2, ',', ' ') . ' €',
            'forcast_asd' => number_format($asd->today_forcast, 2, ',', ' ') . ' €',
            'forcast_er'  => number_format($er->today_forcast,  2, ',', ' ') . ' €',
            'forcast_em'  => number_format($em->today_forcast,  2, ',', ' ') . ' €',
    
            'reached_asm' => ($asm->today_realized > $asm->today_forcast) ? 1 : 0,
            'reached_asd' => ($asd->today_realized > $asd->today_forcast) ? 1 : 0,
            'reached_er'  => ($er->today_realized  > $er->today_forcast)  ? 1 : 0,
            'reached_em'  => ($em->today_realized  > $em->today_forcast)  ? 1 : 0,
    
            'yuan'   => $currency_rates ? number_format($currency_rates->yuan, 4, ',', ' ') : '0,0000',
            'pound'  => $currency_rates ? number_format($currency_rates->pound, 4, ',', ' ') : '0,0000',
            'dollar' => $currency_rates ? number_format($currency_rates->usd, 4, ',', ' ') : '0,0000',
            'yen'    => $currency_rates ? number_format($currency_rates->yen, 4, ',', ' ') : '0,0000',
        ];
    
        return View::make($view)->with($data);
    }      
    
}
