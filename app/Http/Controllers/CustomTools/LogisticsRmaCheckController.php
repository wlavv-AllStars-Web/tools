<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class LogisticsRmaCheckController extends Controller
{
    private const DEADLINE_DAYS = 15;

    private const ACCEPTED_STATES = [
        'return' => [13],
        'warranty' => [13],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $breadcrumbs = [
            ['name' => trans('Logistics'), 'url' => route('logistics.index')],
            ['name' => 'RMA Check', 'url' => route('logistics.tools.rma_check.index'), 'no_translation' => 1],
        ];

        return View::make('customTools.logistics.rma-check.index', compact('breadcrumbs'));
    }

    public function check(Request $request): JsonResponse
    {
        $code = trim((string) $request->input('return_code'));

        if (strlen($code) < 2) {
            return response()->json([
                'error' => 1,
                'error_message' => 'Por favor verifique o codigo de devolucao lido.',
                'html' => '',
            ], 422);
        }

        $return = $this->findOrderReturn($code);

        if (!$return) {
            return response()->json([
                'error' => 1,
                'error_message' => 'A devolucao/garantia com o codigo lido nao existe. Por favor verifique.',
                'html' => '',
            ], 404);
        }

        $acceptedAt = $this->acceptedAt($return);

        if (!$acceptedAt) {
            return response()->json([
                'error' => 1,
                'error_message' => 'Nao foi encontrada data de aceitacao no historico desta devolucao/garantia.',
                'html' => view('customTools.logistics.rma-check.result', [
                    'return' => $return,
                    'details' => $this->details((int) $return->id_order_return),
                    'acceptedAt' => null,
                    'deadline' => null,
                    'daysElapsed' => null,
                    'isInsideDeadline' => false,
                    'deadlineDays' => self::DEADLINE_DAYS,
                ])->render(),
            ]);
        }

        $deadline = $acceptedAt->copy()->addDays(self::DEADLINE_DAYS);
        $isInsideDeadline = now()->lt($deadline);

        return response()->json([
            'error' => $isInsideDeadline ? 0 : 1,
            'error_message' => $isInsideDeadline ? '' : 'A devolucao encontra-se fora do prazo esperado. Por favor verifique.',
            'html' => view('customTools.logistics.rma-check.result', [
                'return' => $return,
                'details' => $this->details((int) $return->id_order_return),
                'acceptedAt' => $acceptedAt,
                'deadline' => $deadline,
                'daysElapsed' => $acceptedAt->diffInDays(now()),
                'isInsideDeadline' => $isInsideDeadline,
                'deadlineDays' => self::DEADLINE_DAYS,
            ])->render(),
        ]);
    }

    private function findOrderReturn(string $code): ?object
    {
        $orderReturnTable = $this->psTable('order_return');
        $ordersTable = $this->psTable('orders');
        $customerTable = $this->psTable('customer');
        $stateLangTable = $this->psTable('order_return_state_lang');

        $query = DB::connection('mysql2')
            ->table($orderReturnTable . ' as r')
            ->leftJoin($ordersTable . ' as o', 'o.id_order', '=', 'r.id_order')
            ->leftJoin($customerTable . ' as c', 'c.id_customer', '=', 'r.id_customer')
            ->leftJoin($stateLangTable . ' as sl', function ($join) {
                $join->on('sl.id_order_return_state', '=', 'r.state')
                    ->where('sl.id_lang', '=', 2);
            })
            ->select(
                'r.id_order_return',
                'r.id_order',
                'r.id_customer',
                'r.process',
                'r.state',
                'r.date_add',
                'o.reference as order_reference',
                DB::raw('TRIM(CONCAT(COALESCE(c.firstname, ""), " ", COALESCE(c.lastname, ""))) as customer_name'),
                'sl.name as state_name'
            );

        if (ctype_digit($code)) {
            $return = (clone $query)->where('r.id_order_return', (int) $code)->first();

            if ($return) {
                return $return;
            }
        }

        $idOrderReturn = $this->findOrderReturnIdByRequestReference($code);

        if (!$idOrderReturn) {
            return null;
        }

        return (clone $query)
            ->where('r.id_order_return', $idOrderReturn)
            ->first();
    }

    private function findOrderReturnIdByRequestReference(string $code): ?int
    {
        $detailTable = $this->psTable('order_return_detail');

        if (!Schema::connection('mysql2')->hasColumn($this->unqualifiedTable($detailTable), 'request_reference')) {
            return null;
        }

        $idOrderReturn = DB::connection('mysql2')
            ->table($detailTable)
            ->where('request_reference', $code)
            ->value('id_order_return');

        return $idOrderReturn ? (int) $idOrderReturn : null;
    }

    private function acceptedAt(object $return): ?Carbon
    {
        $states = self::ACCEPTED_STATES[$return->process] ?? [];

        if (empty($states)) {
            return null;
        }

        $history = DB::connection('mysql2')
            ->table($this->psTable('order_return_history'))
            ->where('id_order_return', (int) $return->id_order_return)
            ->whereIn('id_order_return_state', $states)
            ->orderBy('date_add')
            ->first();

        return $history && !empty($history->date_add)
            ? Carbon::parse($history->date_add)
            : null;
    }

    private function details(int $idOrderReturn)
    {
        return DB::connection('mysql2')
            ->table($this->psTable('order_return_detail') . ' as rd')
            ->leftJoin($this->psTable('order_detail') . ' as od', 'od.id_order_detail', '=', 'rd.id_order_detail')
            ->where('rd.id_order_return', $idOrderReturn)
            ->select(
                'rd.product_quantity',
                'rd.request_reference',
                'rd.refund_method',
                'rd.problem_description',
                'od.product_reference',
                'od.product_name'
            )
            ->get();
    }

    private function psTable(string $table): string
    {
        return (string) env('DB2_DB_prefix', 'ps_') . $table;
    }

    private function unqualifiedTable(string $table): string
    {
        return str_contains($table, '.') ? substr($table, strrpos($table, '.') + 1) : $table;
    }
}
