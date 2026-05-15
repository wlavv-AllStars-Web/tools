<?php

namespace App\Http\Controllers\CustomTools\Tasks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\modules\tasks\task;
use App\Models\modules\tasks\taskFile;

class taskFileController extends Controller
{
    public function upload(Request $request, int $id)
    {
        $task = task::findOrFail($id);
        $this->authorize('view', $task);

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:5120'], // 5MB cada (KB)
        ]);

        $files = $request->file('files'); // array de UploadedFile

        $created = 0;

        foreach ($files as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $path = $file->store("tasks/{$task->id}", 'public');

            taskFile::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'filename' => $file->getClientOriginalName(),
                'filepath' => $path,
                'size' => (int) $file->getSize(),
            ]);

            $created++;
        }

        if ($created === 0) {
            return back()->with('error', 'Nenhum ficheiro válido foi enviado.');
        }

        return back()->with('success', "{$created} ficheiro(s) enviado(s).");
    }

    public function download(int $fileId)
    {
        $file = taskFile::with('task')->findOrFail($fileId);
        $this->authorize('download', $file);

        if (!Storage::disk('public')->exists($file->filepath)) {
            abort(404);
        }

        return Storage::disk('public')->download($file->filepath, $file->filename);
    }
}
