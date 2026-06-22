<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\asg_events\asg_events;
use App\Models\modules\asg_events\asg_events_image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class asgEventsController extends Controller
{
    private array $languages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'pt' => 'Portuguese',
        'it' => 'Italian',
    ];

    private array $apiNameLanguages = ['en', 'es', 'fr'];

    public function index(Request $request)
    {
        $this->breadcrumbs[] = ['name' => trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = ['name' => 'ASG Events', 'url' => route('asg_events.index')];

        $query = asg_events::query()
            ->with('images')
            ->orderBy('position')
            ->orderBy('id_gallery');

        if ($request->filled('id_shop')) {
            $query->where('id_shop', (int) $request->get('id_shop'));
        }

        if ($request->filled('display')) {
            $query->where('display', (int) $request->get('display'));
        }

        if ($request->filled('gallery_type')) {
            $query->where('gallery_type', (string) $request->get('gallery_type'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->get('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('name_en', 'like', '%' . $search . '%')
                    ->orWhere('name_es', 'like', '%' . $search . '%')
                    ->orWhere('name_fr', 'like', '%' . $search . '%');
            });
        }

        $events = $query->paginate(36)->withQueryString();

        return view('customTools.asg_events.index', [
            'breadcrumbs' => $this->breadcrumbs,
            'events' => $events,
        ]);
    }

    public function api(Request $request)
    {
        return $this->apiList($request);
    }

    public function apiList(Request $request)
    {
        $data = $this->apiEventsQuery($request)
            ->get()
            ->map(fn ($event) => $this->eventListPayload($event));

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function apiShow(Request $request, int $id)
    {
        $event = $this->apiEventsQuery($request)
            ->with('images')
            ->where('id_gallery', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->eventDetailPayload($event),
        ]);
    }

    public function create()
    {
        $this->breadcrumbs[] = ['name' => trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = ['name' => 'ASG Events', 'url' => route('asg_events.index')];

        return view('customTools.asg_events.form', [
            'event' => new asg_events(),
            'languages' => $this->languages,
            'mode' => 'create',
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedEventData($request);

        DB::connection('mysql')->transaction(function () use ($request, $data) {
            if (empty($data['position'])) {
                $data['position'] = ((int) asg_events::query()->max('position')) + 1;
            }

            $data['created_at'] = now()->format('Y-m-d H:i:s');
            $data['updated_at'] = now()->format('Y-m-d H:i:s');

            $event = asg_events::query()->create($data);

            $this->handleImages($request, $event);
        });

        return redirect()
            ->route('asg_events.index')
            ->with('success', 'Evento criado com sucesso.');
    }

    public function edit($id)
    {
        $this->breadcrumbs[] = ['name' => trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = ['name' => 'ASG Events', 'url' => route('asg_events.index')];

        $event = asg_events::query()->findOrFail($id);

        return view('customTools.asg_events.form', [
            'event' => $event,
            'languages' => $this->languages,
            'mode' => 'edit',
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $event = asg_events::query()->findOrFail($id);
        $data = $this->validatedEventData($request);

        DB::connection('mysql')->transaction(function () use ($request, $event, $data) {
            $data['updated_at'] = now()->format('Y-m-d H:i:s');

            $event->fill($data);
            $event->save();

            $this->handleImages($request, $event);
        });

        return redirect()
            ->route('asg_events.edit', $event->id_gallery)
            ->with('success', 'Evento atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $event = asg_events::query()->findOrFail($id);

        DB::connection('mysql')->transaction(function () use ($event) {
            asg_events_image::query()
                ->where('id_gallery', $event->id_gallery)
                ->delete();

            $event->delete();
        });

        return redirect()
            ->route('asg_events.index')
            ->with('success', 'Evento removido com sucesso.');
    }

    private function validatedEventData(Request $request): array
    {
        $rules = [
            'id_shop' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'string', 'max:50'],
            'gallery_type' => ['required', 'string', 'in:internal,flickr'],
            'flickr_url' => ['nullable', 'required_if:gallery_type,flickr', 'url', 'max:500'],
            'display' => ['nullable', 'integer', 'in:0,1'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];

        foreach (array_keys($this->languages) as $lang) {
            $rules['name_' . $lang] = ['nullable', 'string', 'max:255'];
        }

        $data = $request->validate($rules);

        $data['display'] = (int) ($data['display'] ?? 0);
        $data['position'] = (int) ($data['position'] ?? 0);
        $data['flickr_url'] = $data['gallery_type'] === 'flickr' ? ($data['flickr_url'] ?? null) : null;

        foreach (array_keys($this->languages) as $lang) {
            $data['name_' . $lang] = $data['name_' . $lang] ?? $data['name'];
        }

        return $data;
    }

    private function handleImages(Request $request, asg_events $event): void
    {
        $folder = $this->imageFolderName($event->name);
        $relativeDir = 'modules/asg_events/views/imgs/' . $folder;
        $absoluteDir = public_path($relativeDir);

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0775, true);
        }

        $updates = [];

        if ($request->hasFile('cover_desktop')) {
            $updates['cover_desktop'] = $this->storeUploadedFile(
                $request->file('cover_desktop'),
                $absoluteDir,
                $relativeDir,
                'cover_desktop'
            );
        }

        if ($request->hasFile('cover_mobile')) {
            $updates['cover_mobile'] = $this->storeUploadedFile(
                $request->file('cover_mobile'),
                $absoluteDir,
                $relativeDir,
                'cover_mobile'
            );
        }

        $existingGallery = $request->input('existing_images', []);
        $existingGallery = is_array($existingGallery) ? array_values(array_filter($existingGallery)) : [];
        $newGallery = [];

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                $newGallery[] = $this->storeUploadedFile(
                    $file,
                    $absoluteDir,
                    $relativeDir,
                    str_pad((string) (count($existingGallery) + $index + 1), 2, '0', STR_PAD_LEFT)
                );
            }
        }

        if (!empty($updates)) {
            $event->fill($updates);
            $event->save();
        }

        $this->syncGalleryImages($event, array_values(array_unique(array_merge($existingGallery, $newGallery))));
    }

    private function syncGalleryImages(asg_events $event, array $images): void
    {
        $images = array_values(array_filter($images));

        asg_events_image::query()
            ->where('id_gallery', $event->id_gallery)
            ->whereNotIn('image', $images ?: [''])
            ->delete();

        foreach ($images as $index => $image) {
            asg_events_image::query()->updateOrCreate(
                [
                    'id_gallery' => $event->id_gallery,
                    'image' => $image,
                ],
                [
                    'position' => $index + 1,
                    'created_at' => now()->format('Y-m-d H:i:s'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                ]
            );
        }
    }

    private function storeUploadedFile($file, string $absoluteDir, string $relativeDir, string $prefix): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'webp');
        $filename = Str::slug($prefix, '_') . '_' . date('YmdHis') . '_' . Str::random(5) . '.' . $extension;

        $file->move($absoluteDir, $filename);

        return trim($relativeDir . '/' . $filename, '/');
    }

    private function imageFolderName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));
        $parts = array_slice($parts ?: ['event'], 0, 3);

        return Str::slug(implode('_', $parts), '_') ?: 'event';
    }

    private function apiEventsQuery(Request $request)
    {
        $query = asg_events::query()
            ->where('display', 1)
            ->orderBy('position')
            ->orderBy('id_gallery');

        if ($request->filled('id_shop')) {
            $query->where('id_shop', (int) $request->get('id_shop'));
        }

        return $query;
    }

    private function eventListPayload(asg_events $event): array
    {
        return [
            'id_gallery' => (int) $event->id_gallery,
            'id_asg_event' => (int) $event->id_gallery,
            'id_shop' => (int) $event->id_shop,
            'names' => $this->translatedNames($event),
            'event_date' => $event->event_date,
            'gallery_type' => $event->gallery_type,
            'cover_desktop' => $this->assetOrNull($event->cover_desktop),
            'cover_mobile' => $this->assetOrNull($event->cover_mobile),
            'detail_url' => route('api.gallery.events.show', $event->id_gallery),
            'flickr_url' => $event->is_external ? $event->flickr_url : null,
            'position' => (int) $event->position,
        ];
    }

    private function eventDetailPayload(asg_events $event): array
    {
        return [
            'id_gallery' => (int) $event->id_gallery,
            'id_asg_event' => (int) $event->id_gallery,
            'names' => $this->translatedNames($event),
            'name' => $event->name,
            'event_date' => $event->event_date,
            'gallery_type' => $event->gallery_type,
            'flickr_url' => $event->is_external ? $event->flickr_url : null,
            'images' => $event->is_external ? [] : collect($event->images_array)
                ->map(fn ($image) => $this->assetOrNull($image))
                ->filter()
                ->values()
                ->all(),
        ];
    }

    private function translatedNames(asg_events $event): array
    {
        $names = [];

        foreach ($this->apiNameLanguages as $lang) {
            $names[$lang] = $event->{'name_' . $lang} ?: $event->name;
        }

        return $names;
    }

    private function assetOrNull(?string $path): ?string
    {
        return $path ? asset($path) : null;
    }
}
