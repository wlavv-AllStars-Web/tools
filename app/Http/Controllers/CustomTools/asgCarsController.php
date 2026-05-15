<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\asg_cars\asg_cars;
use App\Models\modules\asg_cars\asg_cars_product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class asgCarsController extends Controller
{
    private array $languages = [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'pt' => 'Portuguese',
        'it' => 'Italian',
    ];

    private array $productCategories = [
        'motor' => 'Motor',
        'chassis' => 'Chassis',
        'interior' => 'Interior',
        'exterior' => 'Exterior',
        'audio' => 'Audio',
    ];

    public function index(Request $request)
    {

        $this->breadcrumbs[] = [ 'name' =>  trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('asg_cars'), 'url' => route('asg_cars.index')];

        $query = asg_cars::query()->withCount('products')->orderBy('position')->orderBy('id_asg_car');

        if ($request->filled('id_shop')) {
            $query->where('id_shop', (int) $request->get('id_shop'));
        }

        if ($request->filled('display')) {
            $query->where('display', (int) $request->get('display'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->get('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('car_name_galleries', 'like', '%' . $search . '%');
            });
        }

        $cars = $query->paginate(36)->withQueryString();
        return view('customTools.asg_cars.index', [ 'breadcrumbs' => $this->breadcrumbs, 'cars' => $cars ]);
    }

    public function create()
    {

        $this->breadcrumbs[] = [ 'name' =>  trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('asg_cars'), 'url' => route('asg_cars.index')];
        
        return view('customTools.asg_cars.form', [
            'car' => new asg_cars(),
            'productsByCategory' => $this->emptyProductsByCategory(),
            'languages' => $this->languages,
            'productCategories' => $this->productCategories,
            'mode' => 'create',
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedCarData($request);

        DB::connection('mysql2')->transaction(function () use ($request, $data) {
            if (empty($data['position'])) {
                $data['position'] = ((int) asg_cars::query()->max('position')) + 1;
            }

            $data['created_at'] = now()->format('Y-m-d H:i:s');

            $car = asg_cars::query()->create($data);

            $this->handleImages($request, $car);
            $this->syncProducts($request, $car);
        });

        return redirect()
            ->route('asg_cars.index')
            ->with('success', 'Veículo criado com sucesso.');
    }

    public function edit($id)
    {

        $this->breadcrumbs[] = [ 'name' =>  trans('marketing'), 'url' => route('marketing.index')];
        $this->breadcrumbs[] = [ 'name' =>  trans('asg_cars'), 'url' => route('asg_cars.index')];
        
        $car = asg_cars::query()->with('products')->findOrFail($id);

        return view('customTools.asg_cars.form', [
            'car' => $car,
            'productsByCategory' => $this->productsByCategory($car),
            'languages' => $this->languages,
            'productCategories' => $this->productCategories,
            'mode' => 'edit',
            'breadcrumbs' => $this->breadcrumbs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $car = asg_cars::query()->findOrFail($id);
        $data = $this->validatedCarData($request);

        DB::connection('mysql2')->transaction(function () use ($request, $car, $data) {
            $car->fill($data);
            $car->save();

            $this->handleImages($request, $car);
            $this->syncProducts($request, $car);
        });

        return redirect()
            ->route('asg_cars.edit', $car->id_asg_car)
            ->with('success', 'Veículo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $car = asg_cars::query()->findOrFail($id);

        DB::connection('mysql2')->transaction(function () use ($car) {
            asg_cars_product::query()
                ->where('id_asg_car', $car->id_asg_car)
                ->delete();

            $car->delete();
        });

        return redirect()
            ->route('asg_cars.index')
            ->with('success', 'Veículo removido com sucesso.');
    }

    private function validatedCarData(Request $request): array
    {
        $rules = [
            'id_shop' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'car_name_galleries' => ['nullable', 'string', 'max:255'],
            'youtube_code' => ['nullable', 'string', 'max:255'],
            'display' => ['nullable', 'integer', 'in:0,1'],
            'position' => ['nullable', 'integer', 'min:0'],
        ];

        foreach (array_keys($this->languages) as $lang) {
            $rules['description_' . $lang] = ['nullable', 'string'];
            $rules['budget_' . $lang] = ['nullable', 'string'];
        }

        $data = $request->validate($rules);

        $data['display'] = (int) ($data['display'] ?? 0);
        $data['position'] = (int) ($data['position'] ?? 0);

        foreach (array_keys($this->languages) as $lang) {
            $data['description_' . $lang] = $data['description_' . $lang] ?? '';
            $data['budget_' . $lang] = $data['budget_' . $lang] ?? '';
        }

        return $data;
    }

    private function handleImages(Request $request, asg_cars $car): void
    {
        $folder = $this->imageFolderName($car->name);
        $relativeDir = 'modules/asg_cars/views/imgs/' . $folder;
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

        $updates['images'] = json_encode(
            array_values(array_unique(array_merge($existingGallery, $newGallery))),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $car->fill($updates);
        $car->save();
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
        $parts = array_slice($parts ?: ['car'], 0, 2);

        return Str::slug(implode('_', $parts), '_') ?: 'car';
    }

    private function syncProducts(Request $request, asg_cars $car): void
    {
        $products = $request->input('products', []);
        $products = is_array($products) ? $products : [];

        asg_cars_product::query()
            ->where('id_asg_car', $car->id_asg_car)
            ->delete();

        foreach ($this->productCategories as $categoryKey => $categoryLabel) {
            $rows = $products[$categoryKey] ?? [];

            if (!is_array($rows)) {
                continue;
            }

            $position = 1;

            foreach ($rows as $row) {
                $idProduct = isset($row['id_product']) ? (int) $row['id_product'] : 0;
                $name = trim((string) ($row['name'] ?? ''));

                if ($idProduct <= 0 && $name === '') {
                    continue;
                }

                asg_cars_product::query()->create([
                    'id_asg_car' => $car->id_asg_car,
                    'name' => $name,
                    'category' => $categoryKey,
                    'id_lang' => (int) ($row['id_lang'] ?? 1),
                    'link' => trim((string) ($row['link'] ?? '')),
                    'id_product' => $idProduct > 0 ? $idProduct : null,
                    'position' => (int) ($row['position'] ?? $position),
                    'created_at' => now()->format('Y-m-d H:i:s'),
                ]);

                $position++;
            }
        }
    }

    private function productsByCategory(asg_cars $car): array
    {
        $grouped = $this->emptyProductsByCategory();

        foreach ($car->products as $product) {
            if (!isset($grouped[$product->category])) {
                $grouped[$product->category] = [];
            }

            $grouped[$product->category][] = $product;
        }

        return $grouped;
    }

    private function emptyProductsByCategory(): array
    {
        $empty = [];

        foreach ($this->productCategories as $key => $label) {
            $empty[$key] = [];
        }

        return $empty;
    }
}
