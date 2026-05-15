<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class order_payment extends PrestashopModel
{
    use HasFactory;

    //protected $primaryKey = 'id_order_invoice';
    protected $fillable = ['name'];

    protected const SHOP_ASM = 2;
    protected const SHOP_ASD = 3;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('order_payment');
    }

    public static function objectiveByMonth()
    {
        $objectivosByMonth = [];

        for ($i = 0; $i < 12; $i++) {
            $dateString = date('Y') . '-' . sprintf('%02d', $i + 1) . '-01';
            $lastDateOfMonth = date('Y-m-t', strtotime($dateString));

            $lastYearDateString = date('Y', strtotime('-1 years')) . '-' . sprintf('%02d', $i + 1) . '-01';
            $lastYearlastDateOfMonth = date('Y-m-t', strtotime($lastYearDateString));

            $row_this_year = self::getTotalsByShops($dateString, $lastDateOfMonth, [self::SHOP_ASM, self::SHOP_ASD]);
            $row_last_year = self::getTotalsByShops($lastYearDateString, $lastYearlastDateOfMonth, [self::SHOP_ASM, self::SHOP_ASD]);

            $objectivosByMonth[$i + 1] = (object) [
                'current' => $row_this_year + 0,
                'lastYear' => $row_last_year + 0,
            ];
        }

        return $objectivosByMonth;
    }

    public static function objectiveByMonthASD()
    {
        $objectivosByMonth = [];

        for ($i = 0; $i < 12; $i++) {
            $dateString = date('Y') . '-' . sprintf('%02d', $i + 1) . '-01';
            $lastDateOfMonth = date('Y-m-t', strtotime($dateString));

            $lastYearDateString = date('Y', strtotime('-1 years')) . '-' . sprintf('%02d', $i + 1) . '-01';
            $lastYearlastDateOfMonth = date('Y-m-t', strtotime($lastYearDateString));

            $row_this_year = self::getTotalsByShop($dateString, $lastDateOfMonth, self::SHOP_ASD);
            $row_last_year = self::getTotalsByShop($lastYearDateString, $lastYearlastDateOfMonth, self::SHOP_ASD);

            $objectivosByMonth[$i + 1] = (object) [
                'current' => $row_this_year + 0,
                'lastYear' => $row_last_year + 0,
            ];
        }

        return $objectivosByMonth;
    }

    public static function getCounters()
    {
        return (object) [
            'getActual' => self::getASMActual(),
            'getActualCurrentMonth' => self::getASMActualCurrentMonth(),
            'getAcumuladoAnoAnterior' => self::getASMAcumuladoAnoAnterior(),
            'getAcumuladoAnoAnteriorHomologo' => self::getASMAcumuladoAnoAnteriorHomologo(),
            'getASMAcumuladoAnoAnteriorHomologoCurrentMonth' => self::getASMAcumuladoAnoAnteriorHomologoCurrentMonth(),
        ];
    }

    private static function getASMActualCurrentMonth()
    {
        $current_year = date('Y-m') . '-01';
        $current_day = date('Y-m-d');

        return self::getTotalsByShop($current_year, $current_day, self::SHOP_ASM);
    }

    private static function getASMActual()
    {
        $current_year = date('Y') . '-01-01';
        $current_day = date('Y-m-d');

        return self::getTotalsByShop($current_year, $current_day, self::SHOP_ASM);
    }

    private static function getASMAcumuladoAnoAnterior()
    {
        $start_last_year = date('Y', strtotime('-1 years')) . '-01-01';
        $end_last_year = date('Y', strtotime('-1 years')) . '-12-31';

        return self::getTotalsByShop($start_last_year, $end_last_year, self::SHOP_ASM);
    }

    private static function getASMAcumuladoAnoAnteriorHomologo()
    {
        $start_last_year = date('Y', strtotime('-1 years')) . '-01-01';
        $current_day_last_year = date('Y', strtotime('-1 years')) . '-' . date('m-d');

        return self::getTotalsByShop($start_last_year, $current_day_last_year, self::SHOP_ASM);
    }

    private static function getASMAcumuladoAnoAnteriorHomologoCurrentMonth()
    {
        $start_last_year = date('Y', strtotime('-1 years')) . '-' . date('m') . '-01';
        $current_day_last_year = date('Y', strtotime('-1 years')) . '-' . date('m-d');

        return self::getTotalsByShop($start_last_year, $current_day_last_year, self::SHOP_ASM);
    }

    private static function getTotalsByShop($start_date, $end_date, $shopId)
    {
        return self::getTotalsByShops($start_date, $end_date, [$shopId]);
    }

    private static function getTotalsByShops($start_date, $end_date, array $shopIds)
    {
        $db = DB::connection('mysql2');

        $orderHistoryTable = self::tableName('order_history');
        $ordersTable = self::tableName('orders');

        $ids_order = $db->table($orderHistoryTable . ' as oh')
            ->join($ordersTable . ' as o', 'o.id_order', '=', 'oh.id_order')
            ->where('oh.date_add', '>', $start_date . ' 00:00:00')
            ->where('oh.date_add', '<', $end_date . ' 23:59:59')
            ->where('oh.id_order_state', 2)
            ->whereIn('o.id_shop', $shopIds)
            ->groupBy('oh.id_order')
            ->pluck('oh.id_order')
            ->toArray();

        $ids_order_refunded = $db->table($orderHistoryTable . ' as oh')
            ->join($ordersTable . ' as o', 'o.id_order', '=', 'oh.id_order')
            ->whereBetween('oh.date_add', [$start_date . ' 00:00:00', $end_date . ' 23:59:59'])
            ->where('oh.id_order_state', 7)
            ->whereIn('o.id_shop', $shopIds)
            ->distinct()
            ->pluck('oh.id_order')
            ->toArray();

        $discount_from_total = 0;
        if (!empty($ids_order_refunded)) {
            $discount_from_total = $db->table($ordersTable . ' as o')
                ->whereIn('o.id_order', $ids_order_refunded)
                ->sum('o.total_paid_tax_excl');
        }

        $total = 0;
        if (!empty($ids_order)) {
            $total = $db->table($ordersTable . ' as o')
                ->whereIn('o.id_order', $ids_order)
                ->whereIn('o.current_state', [2, 3, 4, 5, 15, 16, 28, 30, 31])
                ->sum('o.total_paid_tax_excl');
        }

        if (date('Y', strtotime($start_date)) == date('Y')) {
            return (float) $total - (float) $discount_from_total;
        }

        return (float) $total;
    }

    public static function projection($expectedEvolution)
    {
        $totalMonthObjectivoASM = 0;
        $totalMonthObjectivoASD = 0;
        $totalMonthObjectivo = 0;
        $totalMonthFacturado = 0;
        $totalMonthLastYear = 0;

        $days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        $totaisDiarios = [];

        for ($i = 0; $i < $days; $i++) {
            $day = sprintf('%02d', $i + 1);
            $date = date('Y') . '-' . date('m') . '-' . $day;

            $currentASM = self::getTotalsByShop($date, $date, self::SHOP_ASM);
            $homologueASM = self::getTotalsByShop(
                date('Y', strtotime('-1 years')) . '-' . date('m') . '-' . $day,
                date('Y', strtotime('-1 years')) . '-' . date('m') . '-' . $day,
                self::SHOP_ASM
            );

            $ASD_stream = self::getASDday($day);

            $ASD = (object) [
                'homologue' => (float) ($ASD_stream->value_objectivo ?? 0),
                'current' => (float) ($ASD_stream->value ?? 0),
            ];

            $currentGlobal = $currentASM + $ASD->current;
            $homologueGlobal = $homologueASM + $ASD->homologue;
            $objectiveDay = $homologueGlobal * $expectedEvolution;

            $percentage = ($currentGlobal > 0)
                ? 100 - (($objectiveDay / $currentGlobal) * 100)
                : 0;

            $valorDia = [];
            $valorDia['name'] = $day . '-' . date('m') . '-' . date('Y');
            $valorDia['lastyear'] = number_format($homologueGlobal, 2, ',', ' ') . ' €';
            $valorDia['lastyear_asm'] = number_format($homologueASM, 2, ',', ' ') . ' €';
            $valorDia['lastyear_asd'] = number_format($ASD->homologue, 2, ',', ' ') . ' €';
            $valorDia['objective'] = number_format($objectiveDay, 2, ',', ' ') . ' €';
            $valorDia['invoiced'] = number_format($currentGlobal, 2, ',', ' ') . ' €';
            $valorDia['invoicedValue'] = $currentGlobal;
            $valorDia['accomplished'] = ($currentGlobal > $objectiveDay) ? 1 : 0;
            $valorDia['percentage'] = number_format($percentage, 2, ',', ' ') . ' %';

            $totaisDiarios[] = (object) $valorDia;

            $totalMonthObjectivoASM += $homologueASM / $expectedEvolution;
            $totalMonthObjectivoASD += $ASD->homologue / $expectedEvolution;
            $totalMonthObjectivo += $homologueGlobal;
            $totalMonthFacturado += $currentGlobal;
            $totalMonthLastYear += $homologueGlobal;
        }

        return (object) [
            'dataMonth' => $totaisDiarios,
            'totalMonthObjectivoASM' => number_format($totalMonthObjectivoASM * $expectedEvolution, 2, ',', ' ') . ' €',
            'totalMonthObjectivoASD' => number_format($totalMonthObjectivoASD * $expectedEvolution, 2, ',', ' ') . ' €',
            'totalMonthObjectivo' => number_format($totalMonthObjectivo * $expectedEvolution, 2, ',', ' ') . ' €',
            'totalMonthFacturado' => number_format($totalMonthFacturado, 2, ',', ' ') . ' €',
            'totalMonthObjectivoValue' => $totalMonthObjectivo * $expectedEvolution,
            'totalMonthFacturadoValue' => $totalMonthFacturado,
            'totalMonthLastYear' => number_format($totalMonthLastYear, 2, ',', ' ') . ' €',
        ];
    }

    public static function getASDday($day)
    {
        $date = date('Y') . '-' . date('m') . '-' . sprintf('%02d', $day);
        $homologue_day = date('Y', strtotime('-1 years')) . '-' . date('m') . '-' . sprintf('%02d', $day);

        return (object) [
            'value' => self::getTotalsByShop($date, $date, self::SHOP_ASD),
            'value_objectivo' => self::getTotalsByShop($homologue_day, $homologue_day, self::SHOP_ASD),
        ];
    }

    public static function getASDActual()
    {
        $current_year = date('Y') . '-01-01';
        $current_day = date('Y-m-d');

        return (object) [
            'actual' => self::getTotalsByShop($current_year, $current_day, self::SHOP_ASD),
            'currentMonth' => self::getTotalsByShop(date('Y-m') . '-01', $current_day, self::SHOP_ASD),
        ];
    }

    public static function getASDHomologo()
    {
        $start_last_year = date('Y', strtotime('-1 years')) . '-01-01';
        $current_day_last_year = date('Y', strtotime('-1 years')) . '-' . date('m-d');

        return (object) [
            'actual' => self::getTotalsByShop($start_last_year, $current_day_last_year, self::SHOP_ASD),
            'currentMonth' => self::getTotalsByShop(
                date('Y', strtotime('-1 years')) . '-' . date('m') . '-01',
                $current_day_last_year,
                self::SHOP_ASD
            ),
        ];
    }

    public static function getASMTotals($today)
    {
        if ($today == 1) {
            $homologue_day = date('Y-m-d', strtotime('-1 year'));
            $day = date('Y-m-d');
        } else {
            $homologue_day = date('Y-m-d', strtotime('-1 years -1 day'));
            $day = date('Y-m-d', strtotime('-1 day'));
        }

        $homologue = self::getTotalsByShop($homologue_day, $homologue_day, self::SHOP_ASM);
        $current = self::getTotalsByShop($day, $day, self::SHOP_ASM);

        return (object) [
            'day' => $current,
            'homologue_day' => $homologue,
        ];
    }

    public static function getASDTotals($today)
    {
        if ($today == 1) {
            $homologue_day = date('Y-m-d', strtotime('-1 year'));
            $day = date('Y-m-d');
        } else {
            $homologue_day = date('Y-m-d', strtotime('-1 years -1 day'));
            $day = date('Y-m-d', strtotime('-1 day'));
        }

        $homologue = self::getTotalsByShop($homologue_day, $homologue_day, self::SHOP_ASD);
        $current = self::getTotalsByShop($day, $day, self::SHOP_ASD);

        return (object) [
            'day' => $current,
            'homologue_day' => $homologue,
        ];
    }

    public static function getShopTotals($shopId, $today)
    {
        if ($today == 1) {
            $homologue_day = date('Y-m-d', strtotime('-1 year'));
            $day = date('Y-m-d');
        } else {
            $homologue_day = date('Y-m-d', strtotime('-1 years -1 day'));
            $day = date('Y-m-d', strtotime('-1 day'));
        }

        $homologue = self::getTotalsByShop($homologue_day, $homologue_day, $shopId);
        $current = self::getTotalsByShop($day, $day, $shopId);

        return (object) [
            'day' => $current,
            'homologue_day' => $homologue,
        ];
    }
}