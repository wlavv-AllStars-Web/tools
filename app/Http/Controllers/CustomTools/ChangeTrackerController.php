<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\modules\changesTracker\PsChangeFile;
use App\Models\modules\changesTracker\PsChangeError;
use App\Models\modules\changesTracker\PsChangeProject;

class ChangeTrackerController extends Controller
{
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $indexUrl = request()->routeIs('web.tools.changes.*')
            ? route('web.tools.changes.index')
            : route('customTools.changesTracker.index');

        $this->breadcrumbs[] = [ 'name' =>  trans('web'), 'url' => route('web.index')];
        $this->breadcrumbs[] = [ 'name' => 'Changes', 'url' => $indexUrl, 'no_translation' => 1];
    }
    
    public function index(Request $request)
    {
        $query = PsChangeProject::query()
            ->withCount(['files', 'errors'])
            ->orderByDesc('change_date')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('requested_by', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->paginate(20)->withQueryString();

        return view('customTools.changesTracker.index', [
            'breadcrumbs'=> $this->breadcrumbs,
            'projects' => $projects,
            'filters' => [
                'search' => (string) $request->get('search', ''),
                'area' => (string) $request->get('area', ''),
                'status' => (string) $request->get('status', ''),
            ],
        ]);
    }

    public function create()
    {
        return view('customTools.changesTracker.create', [ 'breadcrumbs'=> $this->breadcrumbs ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requested_by' => ['required', 'string', 'max:255'],
            'change_date' => ['required', 'date'],
            'area' => ['required', 'in:frontoffice,backoffice,both'],
            'status' => ['required', 'in:planned,in_progress,done,on_hold,cancelled'],
            'backup_files.*' => ['nullable', 'file', 'max:51200'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $project = PsChangeProject::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'requested_by' => $data['requested_by'],
                'change_date' => $data['change_date'],
                'area' => $data['area'],
                'status' => $data['status'],
            ]);

            if ($request->hasFile('backup_files')) {
                foreach ($request->file('backup_files') as $file) {
                    if (!$file) {
                        continue;
                    }

                    $storedPath = $file->store('customTools/changesTracker/' . $project->id, 'public');

                    PsChangeFile::create([
                        'project_id' => $project->id,
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $storedPath,
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('customTools.changesTracker.index')
            ->with('success', 'Alteração criada com sucesso.');
    }

    public function show(int $id)
    {
        $project = PsChangeProject::with(['files', 'errors'])->findOrFail($id);

        return view('customTools.changesTracker.show', [
            'breadcrumbs'=> $this->breadcrumbs,
            'project' => $project,
        ]);
    }

    public function edit(int $id)
    {
        $project = PsChangeProject::with(['files', 'errors'])->findOrFail($id);

        return view('customTools.changesTracker.edit', [
            'breadcrumbs'=> $this->breadcrumbs,
            'project' => $project,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $project = PsChangeProject::with('files')->findOrFail($id);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'requested_by' => ['required', 'string', 'max:255'],
            'change_date' => ['required', 'date'],
            'area' => ['required', 'in:frontoffice,backoffice,both'],
            'status' => ['required', 'in:planned,in_progress,done,on_hold,cancelled'],
            'backup_files.*' => ['nullable', 'file', 'max:51200'],
        ]);

        DB::transaction(function () use ($request, $project, $data) {
            $project->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'requested_by' => $data['requested_by'],
                'change_date' => $data['change_date'],
                'area' => $data['area'],
                'status' => $data['status'],
            ]);

            if ($request->hasFile('backup_files')) {
                foreach ($request->file('backup_files') as $file) {
                    if (!$file) {
                        continue;
                    }

                    $storedPath = $file->store('customTools/changesTracker/' . $project->id, 'public');

                    PsChangeFile::create([
                        'project_id' => $project->id,
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $storedPath,
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('customTools.changesTracker.show', $project->id)
            ->with('success', 'Alteração atualizada com sucesso.');
    }

    public function storeError(Request $request, int $projectId)
    {
        $project = PsChangeProject::findOrFail($projectId);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'resolution' => ['nullable', 'string'],
            'status' => ['required', 'in:open,in_analysis,resolved,closed'],
            'detected_at' => ['nullable', 'date'],
            'resolved_at' => ['nullable', 'date'],
        ]);

        $project->errors()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'resolution' => $data['resolution'] ?? null,
            'status' => $data['status'],
            'detected_at' => $data['detected_at'] ?? now(),
            'resolved_at' => $data['resolved_at'] ?? null,
        ]);

        return redirect()
            ->route('customTools.changesTracker.show', $project->id)
            ->with('success', 'Erro registado com sucesso.');
    }

    public function updateError(Request $request, int $projectId, int $errorId)
    {
        $project = PsChangeProject::findOrFail($projectId);
        $error = PsChangeError::where('project_id', $project->id)->findOrFail($errorId);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'resolution' => ['nullable', 'string'],
            'status' => ['required', 'in:open,in_analysis,resolved,closed'],
            'detected_at' => ['nullable', 'date'],
            'resolved_at' => ['nullable', 'date'],
        ]);

        $error->update($data);

        return redirect()
            ->route('customTools.changesTracker.show', $project->id)
            ->with('success', 'Erro atualizado com sucesso.');
    }

    public function deleteFile(int $projectId, int $fileId)
    {
        $project = PsChangeProject::findOrFail($projectId);
        $file = PsChangeFile::where('project_id', $project->id)->findOrFail($fileId);

        if ($file->stored_path) {
            Storage::disk('public')->delete($file->stored_path);
        }

        $file->delete();

        return redirect()
            ->route('customTools.changesTracker.edit', $project->id)
            ->with('success', 'Ficheiro removido com sucesso.');
    }

    public function downloadFile(int $projectId, int $fileId)
    {
        $project = PsChangeProject::findOrFail($projectId);
        $file = PsChangeFile::where('project_id', $project->id)->findOrFail($fileId);

        if (!$file->stored_path || !Storage::disk('public')->exists($file->stored_path)) {
            abort(404, 'Backup file not found.');
        }

        return Storage::disk('public')->download($file->stored_path, $file->original_name);
    }
}
