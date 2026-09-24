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

        $identity = [
            'tab' => 'finance',
            'store' => 'ASM',
            'panel' => 'moloni_vat_not_valid',
        ];

        $values = [
            'name' => 'MOLONI VATS NOT VALID',
            'function' => 'dashboard::moloniVatNotValid',
            'description' => 'Moloni VAT validations whose status is different from valid.',
            'updated_at' => now(),
        ];

        if (DB::table('dashboard')->where($identity)->exists()) {
            DB::table('dashboard')->where($identity)->update($values);
            return;
        }

        DB::table('dashboard')->insert(array_merge($identity, $values, [
            'counter' => 0,
            'created_at' => now(),
        ]));
    }

    public function down(): void
    {
        if (!Schema::hasTable('dashboard')) {
            return;
        }

        DB::table('dashboard')
            ->where('tab', 'finance')
            ->where('store', 'ASM')
            ->where('panel', 'moloni_vat_not_valid')
            ->delete();
    }
};