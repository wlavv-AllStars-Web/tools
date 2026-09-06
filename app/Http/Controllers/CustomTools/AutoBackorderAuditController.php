<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\AutoBackorderAudit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AutoBackorderAuditController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->string('date')->toString();

        if ($selectedDate === '') {
            $selectedDate = now()->toDateString();
        }

        abort_unless(Carbon::hasFormat($selectedDate, 'Y-m-d'), 422, 'Data inválida.');

        return view('customTools.auto-backorder.audit', [
            'selectedDate' => $selectedDate,
            'audits' => AutoBackorderAudit::query()
                ->whereDate('audit_date', $selectedDate)
                ->orderByDesc('detected_at')
                ->orderByDesc('id')
                ->get(),
            'canRunManually' => in_array((int) auth()->id(), config('auto_backorder.manual_run_user_ids', []), true),
        ]);
    }

    public function runManually(Request $request)
    {
        $allowedUserIds = config('auto_backorder.manual_run_user_ids', []);

        abort_unless(in_array((int) $request->user()->id, $allowedUserIds, true), 403);

        Artisan::call('auto-backorder:audit');

        return redirect()->route('web.tools.auto_backorder.index', ['date' => now()->toDateString()])
            ->with('status', trim(Artisan::output()));
    }
}
