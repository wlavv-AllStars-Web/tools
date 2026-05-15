<?php

namespace App\Services\homepageEditor;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomepageFrontendService
{
    public function getStructuredData(string $lang = 'en', bool $useTemp = false): array
    {
        $lang = in_array($lang, ['en', 'es', 'fr'], true) ? $lang : 'en';
        $table = $useTemp ? 'homepage_asm_temp' : 'homepage_asm_online';
        $cacheKey = "homepage:{$table}:{$lang}";

        $resolver = function () use ($table, $lang) {
            $query = DB::table($table)->orderBy('slot_id');

            if ($table === 'homepage_asm_online') {
                $query->where('active', 1);
            }

            $items = $query->get();

            return [
                'lang' => $lang,
                'source' => $table,
                'homepage' => $this->mapGroup($items->where('icon_type', 1), $lang),
                'half' => $this->mapGroup($items->where('icon_type', 2), $lang),
                'third' => $this->mapGroup($items->where('icon_type', 3), $lang),
                'videos' => $this->mapGroup($items->where('icon_type', 4), $lang),
                'mobile' => $this->mapGroup($items->where('icon_type', 5), $lang),
            ];
        };

        if ($useTemp) {
            return $resolver();
        }

        return Cache::remember($cacheKey, 600, $resolver);
    }

    private function mapGroup($items, string $lang): array
    {
        return $items
            ->map(fn ($item) => $this->mapItem($item, $lang))
            ->values()
            ->all();
    }

    private function mapItem($item, string $lang): array
    {
        return [
            'id' => (int) $item->id,
            'slot_id' => (int) $item->slot_id,
            'icon_type' => (int) $item->icon_type,
            'layout' => $this->resolveLayout((int) $item->icon_type),
            'destination' => $item->destination,
            'info' => $item->info,
            'image' => $this->resolveImage($item, $lang),
            'images' => [
                'en' => $item->image_en,
                'es' => $item->image_es,
                'fr' => $item->image_fr,
            ],
            'target' => $this->resolveTarget($item),
        ];
    }

    private function resolveImage($item, string $lang): ?string
    {
        $field = 'image_' . $lang;
        $path = $item->{$field} ?? null;

        if (!$path) {
            foreach (['en', 'es', 'fr'] as $fallback) {
                $fallbackField = 'image_' . $fallback;
                $path = $item->{$fallbackField} ?? null;
                if ($path) {
                    break;
                }
            }
        }

        return $path ?: null;
    }

    private function resolveTarget($item): ?array
    {
        return match ($item->destination) {
            'manufacturer' => $this->resolveManufacturer($item->info),
            'category' => $this->resolveCategory($item->info),
            'compat' => $this->resolveCompat($item->info),
            'video' => $this->resolveVideo($item->info),
            default => null,
        };
    }

    private function resolveManufacturer($id): ?array
    {
        if (!$id || (string) $id === '0') {
            return null;
        }

        $data = DB::connection('mysql2')
            ->table('ps_manufacturer')
            ->where('id_manufacturer', $id)
            ->first();

        return $data ? [
            'type' => 'manufacturer',
            'id' => (int) $data->id_manufacturer,
            'name' => $data->name,
        ] : null;
    }

    private function resolveCategory($id): ?array
    {
        if (!$id || (string) $id === '0') {
            return null;
        }

        $data = DB::connection('mysql2')
            ->table('ps_category_lang')
            ->where('id_category', $id)
            ->where('id_lang', 1)
            ->first();

        return $data ? [
            'type' => 'category',
            'id' => (int) $data->id_category,
            'name' => $data->name,
        ] : null;
    }

    private function resolveCompat($id): ?array
    {
        if (!$id || (string) $id === '0') {
            return null;
        }

        $compat = DB::table('compats')->where('id_compat', $id)->first();

        if (!$compat) {
            return null;
        }

        $opts = DB::table('compats_options')
            ->whereIn('id_option', [$compat->id_brand, $compat->id_model, $compat->id_type, $compat->id_version])
            ->pluck('name', 'id_option');

        return [
            'type' => 'compat',
            'id' => (int) $compat->id_compat,
            'brand' => $opts[$compat->id_brand] ?? null,
            'model' => $opts[$compat->id_model] ?? null,
            'type_label' => $opts[$compat->id_type] ?? null,
            'version' => $opts[$compat->id_version] ?? null,
            'label' => trim(($opts[$compat->id_brand] ?? '') . ' | ' . ($opts[$compat->id_model] ?? '') . ' | ' . ($opts[$compat->id_type] ?? '') . ' | ' . ($opts[$compat->id_version] ?? ''), ' |'),
        ];
    }

    private function resolveVideo($code): ?array
    {
        $code = trim((string) $code);

        if ($code === '' || $code === '0') {
            return null;
        }

        return [
            'type' => 'video',
            'code' => $code,
            'embed_url' => 'https://www.youtube.com/embed/' . $code,
        ];
    }

    private function resolveLayout(int $iconType): string
    {
        return match ($iconType) {
            1 => 'banner',
            2 => 'image_50',
            3 => 'image_33',
            4 => 'video',
            5 => 'mobile',
            default => 'unknown',
        };
    }

    public function clearCache(): void
    {
        foreach (['en', 'es', 'fr'] as $lang) {
            Cache::forget("homepage:homepage_asm_online:{$lang}");
        }
    }
}
