<?php

namespace App\Services\homepageEditor;

use Illuminate\Support\Facades\DB;

class HomepageRestoreService
{
    public function restore(int $publishId): void
    {
        DB::transaction(function () use ($publishId) {
            $items = DB::table('homepage_asm_history')
                ->where('publish_id', $publishId)
                ->select('slot_id', 'icon_type', 'destination', 'image_en', 'image_es', 'image_fr', 'info')
                ->orderBy('slot_id')
                ->get();

            DB::table('homepage_asm_temp')->delete();

            foreach ($items as $item) {
                DB::table('homepage_asm_temp')->insert([
                    'slot_id' => $item->slot_id,
                    'icon_type' => $item->icon_type,
                    'destination' => $item->destination,
                    'image_en' => $item->image_en,
                    'image_es' => $item->image_es,
                    'image_fr' => $item->image_fr,
                    'info' => $item->info,
                ]);
            }
        });
    }
}