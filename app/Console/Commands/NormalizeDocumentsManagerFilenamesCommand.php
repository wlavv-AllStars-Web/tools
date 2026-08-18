<?php

namespace App\Console\Commands;

use App\Models\modules\documents_manager\documents_manager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class NormalizeDocumentsManagerFilenamesCommand extends Command
{
    protected $signature = 'documents:normalize-filenames {--dry-run : Audit only; do not rename files or update records}';

    protected $description = 'Replace |, / and # in Documents Manager filenames, keeping database and physical files aligned.';

    public function handle(): int
    {
        $documents = documents_manager::query()
            ->where(function ($query) {
                $query->where('document', 'like', '%|%')
                    ->orWhere('document', 'like', '%/%')
                    ->orWhere('document', 'like', '%#%');
            })
            ->orderBy('id_document')
            ->get();

        $plan = [];
        $errors = [];

        foreach ($documents as $document) {
            $newName = documents_manager::normalizeDocumentFilename($document->document);
            $oldPath = documents_manager::documentAbsolutePath($document);
            $target = clone $document;
            $target->document = $newName;
            $newPath = documents_manager::documentAbsolutePath($target);

            if (!is_file($oldPath)) {
                $errors[] = "#{$document->id_document}: source file not found ({$oldPath})";
                continue;
            }

            if (is_file($newPath) && realpath($oldPath) !== realpath($newPath)) {
                $errors[] = "#{$document->id_document}: destination already exists ({$newPath})";
                continue;
            }

            $plan[] = compact('document', 'newName', 'oldPath', 'newPath');
        }

        $this->info(sprintf('%d document(s) found; %d ready; %d issue(s).', $documents->count(), count($plan), count($errors)));
        foreach ($errors as $error) {
            $this->error($error);
        }

        if ($errors !== []) {
            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            foreach ($plan as $item) {
                $this->line("#{$item['document']->id_document}: {$item['document']->document} => {$item['newName']}");
            }
            return self::SUCCESS;
        }

        $moved = [];
        try {
            foreach ($plan as $item) {
                if (!rename($item['oldPath'], $item['newPath'])) {
                    throw new RuntimeException("Unable to rename {$item['oldPath']}");
                }
                $moved[] = $item;
            }

            DB::transaction(function () use ($plan) {
                foreach ($plan as $item) {
                    $item['document']->document = $item['newName'];
                    $item['document']->save();
                }
            });
        } catch (\Throwable $exception) {
            foreach (array_reverse($moved) as $item) {
                if (is_file($item['newPath']) && !is_file($item['oldPath'])) {
                    rename($item['newPath'], $item['oldPath']);
                }
            }
            throw $exception;
        }

        $this->info(sprintf('%d document(s) renamed and updated.', count($plan)));
        return self::SUCCESS;
    }
}
