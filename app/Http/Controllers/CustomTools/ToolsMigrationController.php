<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Services\ToolsMigration\ToolsDatabaseComparator;
use Illuminate\Support\Facades\View;
use Throwable;

class ToolsMigrationController extends Controller
{
    public function __construct(private ToolsDatabaseComparator $comparator)
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $tables = $this->comparator->tableComparison();

        return View::make('customTools.toolsMigration.index', [
            'tables' => $tables,
            'connections' => $this->comparator->connectionStatus(),
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs('Migration tool', route('web.tools.db_migration.index')),
        ]);
    }

    public function table(string $table)
    {
        try {
            $details = $this->comparator->tableDetails($table);
        } catch (Throwable $exception) {
            return redirect()
                ->route('web.tools.db_migration.index')
                ->with('error', $exception->getMessage());
        }

        return View::make('customTools.toolsMigration.table', [
            'details' => $details,
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs($table, route('web.tools.db_migration.table', $table)),
        ]);
    }

    public function row(string $table, string $id)
    {
        try {
            $diff = $this->comparator->rowDiff($table, $id);
        } catch (Throwable $exception) {
            return redirect()
                ->route('web.tools.db_migration.table', $table)
                ->with('error', $exception->getMessage());
        }

        return View::make('customTools.toolsMigration.row', [
            'diff' => $diff,
            'actions' => [],
            'breadcrumbs' => $this->breadcrumbs($table . ' #' . $id, route('web.tools.db_migration.row', [$table, $id])),
        ]);
    }

    public function sync(string $table)
    {
        try {
            $result = $this->comparator->replaceTableFromOldToNew($table);
        } catch (Throwable $exception) {
            return redirect()
                ->route('web.tools.db_migration.table', $table)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('web.tools.db_migration.table', $table)
            ->with('success', 'Replace completed. New table was cleared and filled with ' . $result['processed'] . ' records from old tools.');
    }

    public function syncRow(string $table, string $id)
    {
        try {
            $this->comparator->replaceRowFromOldToNew($table, $id);
        } catch (Throwable $exception) {
            return redirect()
                ->route('web.tools.db_migration.table', $table)
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('web.tools.db_migration.table', $table)
            ->with('success', 'Record #' . $id . ' replaced from old tools.');
    }

    public function clear(string $table)
    {
        try {
            $result = $this->comparator->clearNewTable($table);
        } catch (Throwable $exception) {
            return redirect()
                ->route('web.tools.db_migration.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('web.tools.db_migration.index')
            ->with('success', 'New table ' . $table . ' cleared. Deleted records: ' . $result['deleted']);
    }

    private function breadcrumbs(string $currentName, string $currentUrl): array
    {
        return [
            ['name' => 'web', 'url' => route('web.index')],
            ['name' => 'Migration tool', 'url' => route('web.tools.db_migration.index'), 'no_translation' => 1],
            ['name' => $currentName, 'url' => $currentUrl, 'no_translation' => 1],
        ];
    }
}
