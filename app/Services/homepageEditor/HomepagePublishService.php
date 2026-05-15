<?php

namespace App\Services\homepageEditor;

use Illuminate\Support\Facades\DB;

class HomepagePublishService
{
    public function publish(?string $notes = null): int
    {
        return DB::transaction(function () use ($notes) {

            $publishId = DB::table('homepage_asm_publish_logs')->insertGetId([
                'published_by' => auth()->id(),
                'published_at' => now(),
                'notes' => $notes,
            ]);

            $items = DB::table('homepage_asm_temp')
                ->select(
                    'slot_id',
                    'icon_type',
                    'destination',
                    'image_en',
                    'image_es',
                    'image_fr',
                    'info'
                )
                ->orderBy('slot_id')
                ->get();

            // HISTORY
            foreach ($items as $item) {
                DB::table('homepage_asm_history')->insert([
                    'publish_id' => $publishId,
                    'slot_id' => $item->slot_id,
                    'icon_type' => $item->icon_type,
                    'destination' => $item->destination,
                    'image_en' => $item->image_en,
                    'image_es' => $item->image_es,
                    'image_fr' => $item->image_fr,
                    'info' => $item->info,
                ]);
            }

            // ONLINE (sem truncate!)
            DB::table('homepage_asm_online')->delete();

            $onlineRows = $items->map(function ($item) {
                return [
                    'slot_id' => $item->slot_id,
                    'icon_type' => $item->icon_type,
                    'destination' => $item->destination,
                    'image_en' => $item->image_en,
                    'image_es' => $item->image_es,
                    'image_fr' => $item->image_fr,
                    'info' => $item->info,
                ];
            })->values()->toArray();

            if (!empty($onlineRows)) {
                DB::table('homepage_asm_online')->insert($onlineRows);
            }

            return $publishId;
        });
    }
}