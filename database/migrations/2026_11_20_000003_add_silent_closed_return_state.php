<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const STATE_ID = 15;

    public function up(): void
    {
        $connection = DB::connection('mysql2');
        $prefix = env('DB2_DB_prefix', 'ps_');
        $stateTable = $prefix . 'order_return_state';
        $stateLangTable = $prefix . 'order_return_state_lang';

        if (!$connection->table($stateTable)
            ->where('id_order_return_state', self::STATE_ID)
            ->exists()) {
            $connection->table($stateTable)->insert([
                'id_order_return_state' => self::STATE_ID,
                'color' => '#6c757d',
            ]);
        }

        foreach ([
            2 => 'Return closed - no customer response',
            4 => 'Devolución cerrada - sin respuesta del cliente',
            5 => 'Retour clôturé - sans réponse du client',
        ] as $languageId => $name) {
            $connection->table($stateLangTable)->updateOrInsert(
                [
                    'id_order_return_state' => self::STATE_ID,
                    'id_lang' => $languageId,
                ],
                ['name' => $name]
            );
        }
    }

    public function down(): void
    {
        $connection = DB::connection('mysql2');
        $prefix = env('DB2_DB_prefix', 'ps_');

        // Keep the state if it has already been selected by a return.
        if ($connection->table($prefix . 'order_return')
            ->where('state', self::STATE_ID)
            ->exists()) {
            return;
        }

        $connection->table($prefix . 'order_return_state_lang')
            ->where('id_order_return_state', self::STATE_ID)
            ->delete();

        $connection->table($prefix . 'order_return_state')
            ->where('id_order_return_state', self::STATE_ID)
            ->delete();
    }
};
