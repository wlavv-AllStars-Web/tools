<?php

namespace App\Console\Commands;

use App\Models\modules\dashboard\dashboard;
use Illuminate\Console\Command;

class TestDashboardPanelsCommand extends Command
{
    protected $signature = 'dashboard:test-panels {tab=data : Dashboard tab to test}';

    protected $description = 'Smoke test dashboard panels by rendering counters and panel content.';

    public function handle(): int
    {
        $tab = (string) $this->argument('tab');
        $panels = dashboard::query()
            ->where('tab', $tab)
            ->orderBy('store')
            ->orderBy('panel')
            ->get();

        if ($panels->isEmpty()) {
            $this->warn("No dashboard panels found for tab [{$tab}].");
            return self::FAILURE;
        }

        $this->info("Testing dashboard tab [{$tab}] with {$panels->count()} panel(s).");

        try {
            dashboard::calculateAndGetCountersOfTab($tab)->render();
            $this->line('Counters header: OK');
        } catch (\Throwable $e) {
            $this->error('Counters header: FAILED');
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $failed = 0;
        $rows = [];
        $emptyPanels = [];

        foreach ($panels as $panel) {
            $startedAt = microtime(true);

            try {
                $result = dashboard::getCountersContentOfTabPanel($tab, $panel->panel);
                $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
                $hasError = (bool) ($result['error'] ?? false);
                $html = (string) ($result['html'] ?? '');
                $counter = (int) ($result['counter'] ?? $panel->counter ?? 0);

                if ($hasError || str_contains($html, 'Panel could not be loaded') || str_contains($html, 'Panel method not found')) {
                    $failed++;
                    $rows[] = [$panel->store, $panel->panel, $counter, 'FAILED', $durationMs . ' ms', $result['message'] ?? 'Panel returned an error'];
                    continue;
                }

                if ($counter < 1) {
                    $emptyPanels[] = "{$panel->store}/{$panel->panel}";
                }

                $rows[] = [$panel->store, $panel->panel, $counter, 'OK', $durationMs . ' ms', ''];
            } catch (\Throwable $e) {
                $failed++;
                $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
                $rows[] = [$panel->store, $panel->panel, (int) ($panel->counter ?? 0), 'FAILED', $durationMs . ' ms', $e->getMessage()];
            }
        }

        $this->table(['Store', 'Panel', 'Counter', 'Status', 'Time', 'Message'], $rows);

        if ($failed > 0) {
            $this->error("Dashboard panel smoke test failed: {$failed}/{$panels->count()} panel(s).");
            return self::FAILURE;
        }

        if (!empty($emptyPanels)) {
            $this->warn('Panels without test rows: ' . implode(', ', $emptyPanels));
        }

        $this->info("Dashboard panel smoke test passed: {$panels->count()}/{$panels->count()} panel(s).");
        return self::SUCCESS;
    }
}
