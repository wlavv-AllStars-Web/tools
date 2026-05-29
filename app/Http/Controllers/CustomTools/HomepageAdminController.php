<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Services\homepageEditor\HomepagePublishService;
use App\Services\homepageEditor\HomepageRestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class HomepageAdminController extends Controller
{
    private const DESKTOP_TYPES = [1, 2, 3, 4];
    private const MOBILE_TYPES = [5];
    private const ALLOWED_DESTINATIONS = ['manufacturer', 'category', 'compat', 'video'];

    public function index(Request $request): View
    {
        $mode = $request->get('mode') === 'mobile' ? 'mobile' : 'desktop';
        $lang = $this->normalizeLang($request->get('lang'));

        return view('customTools.homepageEditor.index', $this->buildViewPayload($mode, $lang) + [
            'breadcrumbs' => $this->breadcrumbs('ASM homepage', route('marketing.homepage.index')),
        ]);
    }

    public function edit(int $id): RedirectResponse
    {
        $item = DB::table('homepage_asm_temp')->where('id', $id)->first();
        $mode = ((int) ($item->icon_type ?? 0)) === 5 ? 'mobile' : 'desktop';

        return redirect()->route('marketing.homepage.index', [
            'mode' => $mode,
            'lang' => $this->normalizeLang(request()->get('lang')),
            'slot' => $id,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->merge(['slot_id' => $id]);
        $response = $this->saveSlot($request);
        $payload = $response->getData(true);

        if (!($payload['ok'] ?? false)) {
            return back()->withErrors($payload['errors'] ?? ['Erro ao guardar o bloco.']);
        }

        $item = DB::table('homepage_asm_temp')->where('id', $id)->first();
        $mode = ((int) ($item->icon_type ?? 0)) === 5 ? 'mobile' : 'desktop';

        return redirect()
            ->route('marketing.homepage.index', [
                'mode' => $mode,
                'lang' => $this->normalizeLang($request->get('lang')),
            ])
            ->with('success', 'Bloco atualizado com sucesso.');
    }

    public function preview(Request $request): RedirectResponse
    {
        return redirect()->route('marketing.homepage.index', [
            'mode' => $request->get('mode') === 'mobile' ? 'mobile' : 'desktop',
            'lang' => $this->normalizeLang($request->get('lang')),
        ]);
    }

    public function publish(Request $request, HomepagePublishService $service): RedirectResponse
    {
        $service->publish($request->input('notes'));

        return redirect()
            ->route('marketing.homepage.index', [
                'mode' => $request->get('mode', 'desktop'),
                'lang' => $this->normalizeLang($request->get('lang')),
            ])
            ->with('success', 'Homepage publicada com sucesso. Desktop e Mobile foram publicados em conjunto.');
    }

    public function history(): View
    {
        $logs = DB::table('homepage_asm_publish_logs')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        $breadcrumbs = $this->breadcrumbs('ASM homepage history');

        return view('customTools.homepageEditor.history', compact('logs', 'breadcrumbs'));
    }

    public function restore(int $id, HomepageRestoreService $service): RedirectResponse
    {
        $service->restore($id);

        return redirect()
            ->route('marketing.homepage.index')
            ->with('success', 'Versão restaurada para temporário. Revê e publica quando estiver correto.');
    }

    public function getSlot($id): JsonResponse
    {
        if ($id === 'header' || $id === 'footer') {
            return response()->json([
                'ok' => true,
                'id' => $id,
                'slot_id' => $id,
                'icon_type' => null,
                'destination' => null,
                'type' => null,
                'value_id' => null,
                'info' => null,
                'image_en' => "/homepage/mock/desktop/{$id}_en.png",
                'image_es' => null,
                'image_fr' => null,
            ]);
        }

        $item = DB::table('homepage_asm_temp')->where('id', $id)->first();

        if (!$item) {
            return response()->json(['ok' => false, 'message' => 'Slot não encontrado.'], 404);
        }

        return response()->json([
            'ok' => true,
            'id' => $item->id,
            'slot_id' => $item->slot_id,
            'icon_type' => (int) $item->icon_type,
            'destination' => $item->destination,
            'type' => $item->destination,
            'value_id' => $item->info,
            'info' => $item->info,
            'image_en' => $item->image_en,
            'image_es' => $item->image_es,
            'image_fr' => $item->image_fr,
        ]);
    }

    public function saveSlot(Request $request): JsonResponse
    {
        if (in_array($request->input('slot_id'), ['header', 'footer'], true)) {
            return response()->json(['ok' => true]);
        }

        $validator = Validator::make($request->all(), [
            'slot_id' => ['required', 'integer', 'exists:homepage_asm_temp,id'],
            'type' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_DESTINATIONS)],
            'value_id' => ['nullable', 'string', 'max:50'],
            'youtube_code' => ['nullable', 'string', 'max:50'],
            'image_en' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'image_es' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'image_fr' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'errors' => $validator->errors()->toArray()], 422);
        }

        $type = $request->input('type');
        $info = $type === 'video'
            ? trim((string) $request->input('youtube_code'))
            : trim((string) $request->input('value_id'));

        $data = [
            'destination' => $type,
            'info' => $info !== '' ? $info : '0',
        ];

        foreach (['en', 'es', 'fr'] as $lang) {
            if (!$request->hasFile("image_{$lang}")) {
                continue;
            }

            $file = $request->file("image_{$lang}");
            $extension = strtolower($file->getClientOriginalExtension() ?: 'webp');
            $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $name = now()->format('YmdHis') . '_' . $request->input('slot_id') . '_' . $lang . '_' . $safeName . '.' . $extension;

            $file->move(public_path('uploads/homepage/uploads'), $name);
            $data["image_{$lang}"] = $this->normalizeHomepageUploadPath('/homepage/uploads/' . $name);
        }

        DB::table('homepage_asm_temp')
            ->where('id', $request->integer('slot_id'))
            ->update($data);

        return response()->json(['ok' => true]);
    }

    private function buildViewPayload(string $mode, string $lang = 'en'): array
    {
        $groups = [
            'sliders' => $this->getByType(1),
            'half' => $this->getByType(2),
            'third' => $this->getByType(3),
            'videos' => $this->getByType(4),
            'mobile' => $this->getByType(5),
        ];

        return array_merge($groups, [
            'mode' => $mode,
            'lang' => $lang,
            'imageColumn' => 'image_' . $lang,
            'supportedLanguages' => ['en' => 'EN', 'es' => 'ES', 'fr' => 'FR'],
            'headerImage' => $this->localizedMockImage($mode, 'header', $lang),
            'footerImage' => $this->localizedMockImage($mode, 'footer', $lang),
            'hasChanges' => $this->hasPendingChanges(),
            'manufacturers' => $this->getManufacturers(),
            'categories' => $this->getCategories(),
            'compats' => $this->getCompats(),
        ]);
    }

    private function getByType(int $type)
    {
        return DB::table('homepage_asm_temp')
            ->where('icon_type', $type)
            ->orderBy('slot_id')
            ->get();
    }

    private function getManufacturers()
    {
        return DB::connection('mysql2')
            ->table('ps_manufacturer')
            ->select('id_manufacturer as id', 'name')
            ->orderBy('name')
            ->get();
    }

    private function getCategories()
    {
        return DB::connection('mysql2')
            ->table('ps_category_lang')
            ->select('id_category as id', 'name')
            ->where('id_lang', 1)
            ->orderBy('name')
            ->get();
    }

    private function getCompats()
    {
        return DB::table('compats')
            ->orderBy('brand_position')
            ->orderBy('model_position')
            ->orderBy('type_position')
            ->orderBy('version_position')
            ->get()
            ->map(function ($row) {
                $opts = DB::table('compats_options')
                    ->whereIn('id_option', [
                        $row->id_brand,
                        $row->id_model,
                        $row->id_type,
                        $row->id_version,
                    ])
                    ->pluck('name', 'id_option');

                return (object) [
                    'id' => $row->id_compat,
                    'name' => trim(($opts[$row->id_brand] ?? '') . ' | ' . ($opts[$row->id_model] ?? '') . ' | ' . ($opts[$row->id_type] ?? '') . ' | ' . ($opts[$row->id_version] ?? ''), ' |'),
                ];
            });
    }


    private function normalizeLang(?string $lang): string
    {
        return in_array($lang, ['en', 'es', 'fr'], true) ? $lang : 'en';
    }

    private function normalizeHomepageUploadPath(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        $path = preg_replace('#^https?://resources\.allstars-group\.com#', '', $path);

        return str_replace('/uploads/homepage/uploads/', '/homepage/uploads/', $path);
    }

    private function localizedMockImage(string $mode, string $zone, string $lang): string
    {
        $mode = $mode === 'mobile' ? 'mobile' : 'desktop';
        $lang = $this->normalizeLang($lang);

        $localized = public_path("uploads/homepage/mock/{$mode}/{$zone}_{$lang}.png");

        if (file_exists($localized)) {
            return "/uploads/homepage/mock/{$mode}/{$zone}_{$lang}.png";
        }

        $fallback = public_path("uploads/homepage/mock/{$mode}/{$zone}_en.png");

        if (file_exists($fallback)) {
            return "/uploads/homepage/mock/{$mode}/{$zone}_en.png";
        }

        return "/uploads/homepage/mock/{$mode}/{$zone}_{$lang}.png";
    }

    private function hasPendingChanges(): bool
    {
        if (!Schema::hasTable('homepage_asm_online')) {
            return true;
        }

        $temp = DB::table('homepage_asm_temp')
            ->select('slot_id', 'icon_type', 'destination', 'image_en', 'image_es', 'image_fr', 'info')
            ->orderBy('slot_id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->toJson();

        $online = DB::table('homepage_asm_online')
            ->select('slot_id', 'icon_type', 'destination', 'image_en', 'image_es', 'image_fr', 'info')
            ->orderBy('slot_id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->toJson();

        return $temp !== $online;
    }

    private function breadcrumbs(string $currentName, ?string $currentUrl = null): array
    {
        return [
            ['name' => 'marketing', 'url' => route('marketing.index')],
            ['name' => $currentName, 'url' => $currentUrl, 'no_translation' => 1],
        ];
    }
}
