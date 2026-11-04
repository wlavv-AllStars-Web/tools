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
                'tab' => 'logistics',
                'store' => 'ASM',
                'panel' => 'orders_without_shipping',
                'name' => 'ORDER WITHOUT SHIPPING',
                'function' => 'dashboard::ordersWithoutShipping',
                'description' => 'Paid orders without a shipping value or a ship* product.',
            ],
            [
                'tab' => 'sales',
                'store' => 'ASM',
                'panel' => 'asm_not_in_asd',
                'name' => 'ASM products missing in ASD',
                'function' => 'product::dashboard_asm_not_in_asd',
                'description' => 'Products assigned to ASM but not to ASD.',
            ],
            [
                'tab' => 'sales',
                'store' => 'ASD',
                'panel' => 'asd_not_in_asm',
                'name' => 'ASD products missing in ASM',
                'function' => 'product::dashboard_asd_not_in_asm',
                'description' => 'Products assigned to ASD but not to ASM.',
            ],
        ];

        foreach ($panels as $panel) {
            $identity = [
                'tab' => $panel['tab'],
                'store' => $panel['store'],
                'panel' => $panel['panel'],
            ];
            $values = [
                'name' => $panel['name'],
                'function' => $panel['function'],
                'description' => $panel['description'],
                'updated_at' => now(),
            ];

            DB::table('dashboard')->where('panel', $panel['panel'])->update(array_merge($identity, $values));

            if (!DB::table('dashboard')->where($identity)->exists()) {
                DB::table('dashboard')->insert(array_merge($identity, $values, [
                    'counter' => 0,
                    'created_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('dashboard')) {
            return;
        }

        DB::table('dashboard')
            ->where('tab', 'logistics')
            ->where('panel', 'orders_without_shipping')
            ->delete();

        DB::table('dashboard')
            ->where('tab', 'sales')
            ->whereIn('panel', ['asm_not_in_asd', 'asd_not_in_asm'])
            ->update(['tab' => 'web', 'updated_at' => now()]);
    }
};
