<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\asd_alerts\AsdAlertMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AsdAlertController extends Controller
{
    private array $languages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'pt' => 'Portuguese',
        'it' => 'Italian',
    ];

    private array $importanceTypes = [
        '1' => 'Informative',
        '2' => 'Warning',
        '3' => 'Critical',
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');

        $query = AsdAlertMessage::query()
            ->where('deleted', (int) $showArchived)
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('message_status', (int) $request->get('status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('title_en', 'like', '%' . $search . '%')
                    ->orWhere('message_en', 'like', '%' . $search . '%');
            });
        }

        return View::make('customTools.asdAlerts.index', [
            'alerts' => $query->paginate(25)->withQueryString(),
            'showArchived' => $showArchived,
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('ASD - Alerts', route('admin.tools.asd_alerts.index')),
        ]);
    }

    public function create()
    {
        return View::make('customTools.asdAlerts.form', [
            'alert' => new AsdAlertMessage([
                'message_status' => 1,
                'message_type' => '1',
            ]),
            'languages' => $this->languages,
            'importanceTypes' => $this->importanceTypes,
            'mode' => 'create',
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('New alert', route('admin.tools.asd_alerts.create')),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['creation_date'] = now()->format('Y-m-d H:i:s');
        $data['deleted_date'] = null;
        $data['deleted'] = 0;

        AsdAlertMessage::query()->create($data);

        return redirect()
            ->route('admin.tools.asd_alerts.index')
            ->with('success', 'Alert created successfully.');
    }

    public function edit(AsdAlertMessage $asdAlert)
    {
        return View::make('customTools.asdAlerts.form', [
            'alert' => $asdAlert,
            'languages' => $this->languages,
            'importanceTypes' => $this->importanceTypes,
            'mode' => 'edit',
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('Edit alert #' . $asdAlert->id, route('admin.tools.asd_alerts.edit', $asdAlert)),
        ]);
    }

    public function update(Request $request, AsdAlertMessage $asdAlert)
    {
        $asdAlert->update($this->validatedData($request));

        return redirect()
            ->route('admin.tools.asd_alerts.edit', $asdAlert)
            ->with('success', 'Alert updated successfully.');
    }

    public function destroy(AsdAlertMessage $asdAlert)
    {
        $asdAlert->update([
            'deleted' => 1,
            'deleted_date' => now()->format('Y-m-d H:i:s'),
        ]);

        return redirect()
            ->route('admin.tools.asd_alerts.index')
            ->with('success', 'Alert archived successfully.');
    }

    private function validatedData(Request $request): array
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:250'],
            'message_type' => ['required', 'string', 'in:1,2,3'],
            'message_status' => ['nullable', 'integer', 'in:0,1'],
        ];

        foreach (array_keys($this->languages) as $lang) {
            $rules['title_' . $lang] = ['nullable', 'string', 'max:125'];
            $rules['message_' . $lang] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);
        $data['title'] = $data['title'] ?? '';
        $data['message_status'] = (int) ($data['message_status'] ?? 0);

        foreach (array_keys($this->languages) as $lang) {
            $data['title_' . $lang] = $data['title_' . $lang] ?? '';
            $data['message_' . $lang] = $data['message_' . $lang] ?? '';
        }

        return $data;
    }

    private function breadcrumbs(string $currentName, string $currentUrl): array
    {
        return [
            ['name' => 'administration', 'url' => route('administration.index')],
            ['name' => $currentName, 'url' => $currentUrl, 'no_translation' => 1],
        ];
    }
}
