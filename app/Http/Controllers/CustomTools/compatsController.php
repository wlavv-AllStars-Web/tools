<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;
use App\Models\modules\compats\compats;
use App\Models\modules\compats\compats_options;
use App\Models\modules\compats\compats_product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class compatsController extends Controller
{
    public $actions = [];
    public $breadcrumbs = [];

    private const DEFAULT_STORE = 2;

    public function __construct()
    {
        $indexUrl = request()->routeIs('admin.tools.compats.*')
            ? route('admin.tools.compats.index')
            : route('compats.index');

        $this->breadcrumbs[] = ['name' => 'administration', 'url' => route('administration.index')];
        $this->breadcrumbs[] = ['name' => 'Compats', 'url' => $indexUrl, 'no_translation' => 1];
    }

    public function index()
    {
        $store = self::DEFAULT_STORE;

        return View::make('customTools/compats/index')->with([
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'options' => compats_options::getByType(0, 1),
            'compats' => compats::getAllCompats($store),
        ]);
    }

    public function getOptions(Request $request)
    {
        $type = (int) $request->input('type', 0);
        $idOption = (int) $request->input('id_option', 0);

        if ($type < 5) {
            $options = compats_options::getByType($idOption, $type);
            return view('customTools/compats/includes/options', ['type' => $type, 'options' => $options])->render();
        }

        $products = compats_product::getProductIds((object) $request->all());
        return view('customTools/compats/includes/products', ['products' => $products])->render();
    }

    public function getOptionsForModal(Request $request)
    {
        $type = (int) $request->input('type', 0);
        $options = compats_options::getOptionsOfByType($type);

        return view('customTools/compats/includes/optionsForModal', [
            'type' => $type,
            'options' => $options,
        ])->render();
    }

    public function createCompatibilities(Request $request)
    {
        compats_product::insertNestedCompat((object) $request->all());

        return response()->json(['success' => true]);
    }

    public function saveNewRelationship(Request $request)
    {
        compats_options::newOption((object) $request->all());

        return response()->json(['success' => true]);
    }

    public function updateTag(Request $request)
    {
        compats_options::where('id_option', (int) $request->input('id_option'))
            ->update([
                'slug' => str_replace(' ', '', (string) $request->input('newTag')),
                'name' => (string) $request->input('newTag'),
            ]);

        return response()->json(['success' => true]);
    }

    public function setData(Request $request)
    {
        $allowedFields = [
            'row',
            'position',
            'brand_position',
            'model_position',
            'type_position',
            'version_position',
            'id_brand',
            'id_model',
            'id_type',
            'id_version',
            'store',
        ];

        $field = (string) $request->input('type');

        if (!in_array($field, $allowedFields, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid field.'], 422);
        }

        compats::where('id_compat', (int) $request->input('id_compat'))
            ->update([
                $field => $request->input('value'),
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    public function editImage(Request $request)
    {
        compats_options::updateImage((object) $request->all());

        return response()->json(['success' => true]);
    }

    public function removeCompat(Request $request)
    {
        $idCompat = (int) $request->input('id');

        compats_product::where('id_compat', $idCompat)->delete();
        compats::where('id_compat', $idCompat)->delete();

        return response()->json(['success' => true]);
    }

    public function updateMenu()
    {
        $store = self::DEFAULT_STORE;
        $rows = compats::getAllCompats($store);
        $brands = [];

        foreach ($rows as $item) {
            $brandId = (int) $item->id_brand;
            $modelId = (int) $item->id_model;
            $typeId = (int) $item->id_type;
            $versionId = (int) $item->id_version;

            if (!isset($brands[$brandId])) {
                $brands[$brandId] = (object) [
                    'id' => $brandId,
                    'id_compat' => $item->id_compat,
                    'name' => $item->brand->name ?? '',
                    'position' => (int) ($item->brand_position ?? 0),
                    'models' => [],
                ];
            } else {
                $brands[$brandId]->position = min($brands[$brandId]->position, (int) ($item->brand_position ?? 0));
            }

            if (!isset($brands[$brandId]->models[$modelId])) {
                $brands[$brandId]->models[$modelId] = (object) [
                    'id' => $modelId,
                    'id_brand' => $brandId,
                    'id_compat' => $item->id_compat,
                    'name' => $item->model->name ?? '',
                    'position' => (int) ($item->model_position ?? 0),
                    'cover_compat_id' => $item->id_compat,
                    'types' => [],
                ];
            } else {
                $brands[$brandId]->models[$modelId]->position = min(
                    $brands[$brandId]->models[$modelId]->position,
                    (int) ($item->model_position ?? 0)
                );
            }

            if (!isset($brands[$brandId]->models[$modelId]->types[$typeId])) {
                $brands[$brandId]->models[$modelId]->types[$typeId] = (object) [
                    'id' => $typeId,
                    'id_brand' => $brandId,
                    'id_model' => $modelId,
                    'id_compat' => $item->id_compat,
                    'name' => $item->type->name ?? '',
                    'position' => (int) ($item->type_position ?? 0),
                    'cover_compat_id' => $item->id_compat,
                    'versions' => [],
                    'versions_count' => 0,
                ];
            } else {
                $brands[$brandId]->models[$modelId]->types[$typeId]->position = min(
                    $brands[$brandId]->models[$modelId]->types[$typeId]->position,
                    (int) ($item->type_position ?? 0)
                );
            }

            if (!isset($brands[$brandId]->models[$modelId]->types[$typeId]->versions[$versionId])) {
                $brands[$brandId]->models[$modelId]->types[$typeId]->versions[$versionId] = (object) [
                    'id' => $versionId,
                    'id_brand' => $brandId,
                    'id_model' => $modelId,
                    'id_type' => $typeId,
                    'id_compat' => $item->id_compat,
                    'name' => $item->version->name ?? '',
                    'position' => (int) ($item->version_position ?? 0),
                ];
            } else {
                $brands[$brandId]->models[$modelId]->types[$typeId]->versions[$versionId]->position = min(
                    $brands[$brandId]->models[$modelId]->types[$typeId]->versions[$versionId]->position,
                    (int) ($item->version_position ?? 0)
                );
            }
        }

        uasort($brands, function ($left, $right) {
            return [$left->position, $left->name, $left->id] <=> [$right->position, $right->name, $right->id];
        });

        foreach ($brands as $brand) {
            uasort($brand->models, function ($left, $right) {
                return [$left->position, $left->name, $left->id] <=> [$right->position, $right->name, $right->id];
            });

            foreach ($brand->models as $model) {
                uasort($model->types, function ($left, $right) {
                    return [$left->position, $left->name, $left->id] <=> [$right->position, $right->name, $right->id];
                });

                foreach ($model->types as $type) {
                    uasort($type->versions, function ($left, $right) {
                        return [$left->position, $left->name, $left->id] <=> [$right->position, $right->name, $right->id];
                    });

                    $type->versions = array_values($type->versions);
                    $type->versions_count = count($type->versions);
                }

                $model->types = array_values($model->types);
            }

            $brand->models = array_values($brand->models);
        }

        $brands = array_values($brands);

        $this->breadcrumbs[] = [
            'name' => trans('compats.updateMenu'),
            'url' => request()->routeIs('admin.tools.compats.*')
                ? route('admin.tools.compats.update_menu')
                : route('compats.updateMenu'),
            'no_translation' => 1,
        ];

        return View::make('customTools/compats/menu')->with([
            'breadcrumbs' => $this->breadcrumbs,
            'structure' => $brands,
        ]);
    }

    public function setOrder(Request $request)
    {
        $items = (array) $request->input('dataInfo', []);

        foreach ($items as $element) {
            $type = (string) ($element['type'] ?? '');
            $position = (int) ($element['position'] ?? $element['row'] ?? 0);
            $idCompat = (int) ($element['id_compat'] ?? 0);
            $idBrand = (int) ($element['id_brand'] ?? 0);
            $idModel = (int) ($element['id_model'] ?? 0);
            $idType = (int) ($element['id_type'] ?? 0);
            $idVersion = (int) ($element['id_version'] ?? 0);

            $store = self::DEFAULT_STORE;
            if ($idCompat > 0) {
                $sourceCompat = compats::find($idCompat);
                if ($sourceCompat) {
                    $store = (int) $sourceCompat->store;
                }
            }

            switch ($type) {
                case 'brand':
                    if ($idBrand < 1) {
                        break;
                    }

                    compats::where('store', $store)
                        ->where('id_brand', $idBrand)
                        ->update([
                            'brand_position' => $position,
                            'updated_at' => now(),
                        ]);
                    break;

                case 'model':
                    if ($idBrand < 1 || $idModel < 1) {
                        break;
                    }

                    compats::where('store', $store)
                        ->where('id_brand', $idBrand)
                        ->where('id_model', $idModel)
                        ->update([
                            'model_position' => $position,
                            'updated_at' => now(),
                        ]);
                    break;

                case 'type':
                    if ($idBrand < 1 || $idModel < 1 || $idType < 1) {
                        break;
                    }

                    compats::where('store', $store)
                        ->where('id_brand', $idBrand)
                        ->where('id_model', $idModel)
                        ->where('id_type', $idType)
                        ->update([
                            'type_position' => $position,
                            'updated_at' => now(),
                        ]);
                    break;

                case 'version':
                    if ($idBrand < 1 || $idModel < 1 || $idType < 1 || $idVersion < 1) {
                        break;
                    }

                    compats::where('store', $store)
                        ->where('id_brand', $idBrand)
                        ->where('id_model', $idModel)
                        ->where('id_type', $idType)
                        ->where('id_version', $idVersion)
                        ->update([
                            'version_position' => $position,
                            'updated_at' => now(),
                        ]);
                    break;
            }
        }

        return response()->json(['success' => true]);
    }
}
