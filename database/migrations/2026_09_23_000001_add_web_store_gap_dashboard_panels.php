<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('dashboard')) {
            return;
        }

        $panels = [
            [
                'store' => 'ASM',
                'panel' => 'asm_not_in_asd',
                'name' => 'ASM products missing in ASD',
                'function' => 'product::dashboard_asm_not_in_asd',
                'description' => 'Products assigned to ASM but not to ASD.',
            ],
            [
                'store' => 'ASD',
                'panel' => 'asd_not_in_asm',
                'name' => 'ASD products missing in ASM',
                'function' => 'product::dashboard_asd_not_in_asm',
                'description' => 'Products assigned to ASD but not to ASM.',
            ],
        ];

        foreach ($panels as $panel) {
            $identity = [
                'tab' => 'web',
                'store' => $panel['store'],
                'panel' => $panel['panel'],
            ];

            $values = [
                'name' => $panel['name'],
                'function' => $panel['function'],
                'description' => $panel['description'],
                'updated_at' => now(),
            ];

            if (DB::table('dashboard')->where($identity)->exists()) {
                DB::table('dashboard')->where($identity)->update($values);
                continue;
            }

            DB::table('dashboard')->insert(array_merge($identity, $values, [
                'counter' => 0,
                'created_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('dashboard')) {
            return;
        }

        DB::table('dashboard')
            ->where('tab', 'web')
            ->whereIn('panel', ['asm_not_in_asd', 'asd_not_in_asm'])
            ->delete();
    }
};
