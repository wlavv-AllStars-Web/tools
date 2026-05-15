<?php

namespace App\Models\modules\compats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class compats extends Model
{
    use HasFactory;

    protected $table = 'compats';
    protected $primaryKey = 'id_compat';
    public $timestamps = false;

    protected $fillable = [
        'store',
        'id_brand',
        'id_model',
        'id_type',
        'id_version',
        'row',
        'position',
        'brand_position',
        'model_position',
        'type_position',
        'version_position',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id_compat' => 'integer',
        'store' => 'integer',
        'id_brand' => 'integer',
        'id_model' => 'integer',
        'id_type' => 'integer',
        'id_version' => 'integer',
        'row' => 'integer',
        'position' => 'integer',
        'brand_position' => 'integer',
        'model_position' => 'integer',
        'type_position' => 'integer',
        'version_position' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(compats_options::class, 'id_brand', 'id_option');
    }

    public function model()
    {
        return $this->belongsTo(compats_options::class, 'id_model', 'id_option');
    }

    public function type()
    {
        return $this->belongsTo(compats_options::class, 'id_type', 'id_option');
    }

    public function version()
    {
        return $this->belongsTo(compats_options::class, 'id_version', 'id_option');
    }

    public function products()
    {
        return $this->hasMany(compats_product::class, 'id_compat', 'id_compat');
    }

    public function scopeForStore($query, $store = null)
    {
        if ($store === null || $store === '') {
            return $query;
        }

        return $query->where($this->table . '.store', (int) $store);
    }

    public static function baseListQuery($store = null)
    {
        return self::query()
            ->with([
                'brand:id_option,name',
                'model:id_option,name',
                'type:id_option,name',
                'version:id_option,name',
            ])
            ->forStore($store)
            ->orderBy('brand_position', 'asc')
            ->orderBy('model_position', 'asc')
            ->orderBy('type_position', 'asc')
            ->orderBy('version_position', 'asc')
            ->orderBy('id_compat', 'asc');
    }

    public static function getRelationshipFromCompat($id_compat)
    {
        return self::where('id_compat', $id_compat)->get();
    }

    public static function getProductCompatDetails($id_product, $store = 0)
    {
        $base = config('allstars.services.webtools.base_url');

        $rows = self::query()
            ->join('compats_product', function ($join) use ($id_product, $store) {
                $join->on('compats.id_compat', '=', 'compats_product.id_compat')
                    ->where('compats_product.id_product', '=', $id_product)
                    ->where('compats_product.store', '=', $store);
            })
            ->with(['brand', 'model', 'type', 'version'])
            ->where('compats.store', $store)
            ->select('compats.*')
            ->orderBy('compats.brand_position', 'ASC')
            ->orderBy('compats.model_position', 'ASC')
            ->orderBy('compats.type_position', 'ASC')
            ->orderBy('compats.version_position', 'ASC')
            ->get();

        $response = [];

        foreach ($rows as $compat) {
            $response[] = [
                'id_compat' => (int) $compat->id_compat,
                'row' => (int) $compat->row,
                'position' => (int) $compat->position,
                'brand_position' => (int) $compat->brand_position,
                'model_position' => (int) $compat->model_position,
                'type_position' => (int) $compat->type_position,
                'version_position' => (int) $compat->version_position,
                'id_brand' => (int) $compat->id_brand,
                'id_model' => (int) $compat->id_model,
                'id_type' => (int) $compat->id_type,
                'id_version' => (int) $compat->id_version,
                'brand' => $compat->brand->name ?? '',
                'model' => $compat->model->name ?? '',
                'type' => $compat->type->name ?? '',
                'version' => $compat->version->name ?? '',
                'brand_logo' => $base . '/compats/brand/' . $compat->id_brand . '.png',
                'brand_hover_logo' => $base . '/compats/brand_hover/' . $compat->id_brand . '.png',
                'cartoon' => $base . '/compats/compat/' . $compat->id_compat . '.png',
            ];
        }

        return $response;
    }

    public static function getCompatInfo($id_compat, $store = 0)
    {
        $compat = compats::with('brand', 'model', 'type', 'version')
            ->where('store', $store)
            ->where('id_compat', $id_compat)
            ->first();

        return [
            'id_compat' => $id_compat,
            'brand' => $compat->brand->name,
            'model' => $compat->model->name,
            'type' => $compat->type->name,
            'version' => $compat->version->name,
            'brand_logo' => config('allstars.services.webtools.base_url') . '/compats/brand/' . $compat->id_brand . '.png',
            'brand_hover_logo' => config('allstars.services.webtools.base_url') . '/compats/brand_hover/' . $compat->id_brand . '.png',
            'cartoon' => config('allstars.services.webtools.base_url') . '/compats/compat/' . $id_compat . '.png'
        ];
    }

    public function getProducts(Request $request)
    {
        self::validateToken($request->token);

        $products = compats_product::getProducts($request->id_compat, $request->store);

        if (count($products) > 0) {
            $data = [
                'status' => 'SUCCESS',
                'message' => count($products) . ' PRODUCTS AVAILABLE',
                'compat' => compats::getCompatInfo($request->id_compat, $request->store),
                'data' => $products
            ];
        } else {
            $data = [
                'status' => 'SUCCESS',
                'message' => 'NO PRODUCTS AVAILABLE',
                'compat' => compats::getCompatInfo($request->id_compat, $request->store),
                'data' => $products
            ];
        }

        echo json_encode($data);
        exit;
    }

    public static function getCompats($id_brand, $store = 0)
    {
        $compats = [];

        $data = compats::where('id_brand', $id_brand)
            ->where('store', $store)
            ->orderBy('model_position', 'asc')
            ->orderBy('type_position', 'asc')
            ->orderBy('version_position', 'asc')
            ->get();

        foreach ($data as $compat) {
            $model = compats_options::find($compat->id_model);
            $type = compats_options::find($compat->id_type);
            $version = compats_options::find($compat->id_version);

            $compats[] = [
                'id_compat' => $compat->id_compat,
                'model' => $model->name ?? null,
                'type' => $type->name ?? null,
                'version' => $version->name ?? null,
                'cartoon' => config('allstars.services.webtools.base_url') . '/compats/compat/' . $compat->id_compat . '.png',
            ];
        }

        return $compats;
    }

    public static function getCompatDetail($id_compat, $store = null)
    {
        return self::baseListQuery($store)
            ->where('id_compat', $id_compat)
            ->first();
    }

    public static function getIdCompat($id_brand, $id_model, $id_type, $id_version, $store = null)
    {
        return self::query()
            ->forStore($store)
            ->where('id_brand', $id_brand)
            ->where('id_model', $id_model)
            ->where('id_type', $id_type)
            ->where('id_version', $id_version)
            ->value('id_compat');
    }

    public static function getAllCompats($store = null)
    {
        return self::baseListQuery($store)->get();
    }

    public static function getAllCompatsBO($store = null)
    {
        return self::baseListQuery($store)
            ->get()
            ->map(function ($compat) {
                return [
                    'id_compat' => $compat->id_compat,
                    'brand' => $compat->brand->name ?? '',
                    'model' => $compat->model->name ?? '',
                    'type' => $compat->type->name ?? '',
                    'version' => $compat->version->name ?? '',
                    'name' => implode(' | ', array_filter([
                        $compat->brand->name ?? null,
                        $compat->model->name ?? null,
                        $compat->type->name ?? null,
                        $compat->version->name ?? null,
                    ])),
                ];
            })
            ->values()
            ->all();
    }

    public static function getAllCompatsFromFilter($brand, $model, $type, $version, $store = null)
    {
        $query = self::query()->forStore($store);

        if ((int) $brand > 0) {
            $query->where('id_brand', (int) $brand);
        }

        if ((int) $model > 0) {
            $query->where('id_model', (int) $model);
        }

        if ((int) $type > 0) {
            $query->where('id_type', (int) $type);
        }

        if ((int) $version > 0) {
            $query->where('id_version', (int) $version);
        }

        return $query
            ->orderBy('brand_position', 'asc')
            ->orderBy('model_position', 'asc')
            ->orderBy('type_position', 'asc')
            ->orderBy('version_position', 'asc')
            ->get();
    }

    public static function createCompat($id_brand, $id_model, $id_type, $id_version, $store = 6)
    {
        $existingId = self::getIdCompat($id_brand, $id_model, $id_type, $id_version, $store);

        if ($existingId) {
            return $existingId;
        }

        $new = new self();
        $new->store = (int) $store;
        $new->id_brand = (int) $id_brand;
        $new->id_model = (int) $id_model;
        $new->id_type = (int) $id_type;
        $new->id_version = (int) $id_version;
        $new->row = 0;
        $new->position = 0;
        $new->brand_position = 0;
        $new->model_position = 0;
        $new->type_position = 0;
        $new->version_position = 0;
        $new->created_at = now();
        $new->updated_at = now();
        $new->save();

        return $new->id_compat;
    }

    public static function getBrands($store = 0)
    {
        $brands = [];

        $data = compats::select(
                'compats.id_brand',
                'compats_options.name',
                DB::raw('MIN(compats.brand_position) as brand_position')
            )
            ->join('compats_options', 'compats.id_brand', '=', 'compats_options.id_option')
            ->where('store', $store)
            ->groupBy('compats.id_brand', 'compats_options.name')
            ->orderBy('brand_position', 'ASC')
            ->orderBy('compats_options.name', 'ASC')
            ->get();

        foreach ($data as $brand) {
            $brands[] = [
                'id_brand' => $brand->id_brand,
                'name' => $brand->name,
                'brand_logo' => config('allstars.services.webtools.base_url') . '/compats/brand/' . $brand->id_brand . '.png',
                'brand_hover_logo' => config('allstars.services.webtools.base_url') . '/compats/brand_hover/' . $brand->id_brand . '.png',
            ];
        }

        return $brands;
    }

    public static function getModels($brand, $store = 0)
    {
        return self::query()
            ->select(
                'compats.id_model',
                'compats_options.name',
                DB::raw('MIN(compats.model_position) as model_position')
            )
            ->join('compats_options', 'compats.id_model', '=', 'compats_options.id_option')
            ->where('compats.id_brand', $brand)
            ->forStore($store)
            ->groupBy('compats.id_model', 'compats_options.name')
            ->orderBy('model_position', 'asc')
            ->orderBy('compats_options.name', 'asc')
            ->get()
            ->map(function ($model) use ($brand) {
                return [
                    'id_brand' => (int) $brand,
                    'id_model' => (int) $model->id_model,
                    'position' => (int) $model->model_position,
                    'name' => $model->name,
                ];
            })
            ->values()
            ->all();
    }

    public static function getTypes($model, $store = 0)
    {
        return self::query()
            ->select(
                'compats.id_brand',
                'compats.id_model',
                'compats.id_type',
                'compats_options.name',
                DB::raw('MIN(compats.type_position) as type_position')
            )
            ->join('compats_options', 'compats.id_type', '=', 'compats_options.id_option')
            ->where('compats.id_model', $model)
            ->forStore($store)
            ->groupBy('compats.id_brand', 'compats.id_model', 'compats.id_type', 'compats_options.name')
            ->orderBy('type_position', 'asc')
            ->orderBy('compats_options.name', 'asc')
            ->get()
            ->map(function ($type) use ($model) {
                return [
                    'id_model' => (int) $model,
                    'id_type' => (int) $type->id_type,
                    'position' => (int) $type->type_position,
                    'name' => $type->name,
                ];
            })
            ->values()
            ->all();
    }

    public static function getVersions($type, $store = 0)
    {
        return self::query()
            ->select(
                'compats.id_brand',
                'compats.id_model',
                'compats.id_type',
                'compats.id_version',
                'compats_options.name',
                DB::raw('MIN(compats.version_position) as version_position')
            )
            ->join('compats_options', 'compats.id_version', '=', 'compats_options.id_option')
            ->where('compats.id_type', $type)
            ->forStore($store)
            ->groupBy(
                'compats.id_brand',
                'compats.id_model',
                'compats.id_type',
                'compats.id_version',
                'compats_options.name'
            )
            ->orderBy('version_position', 'asc')
            ->orderBy('compats_options.name', 'asc')
            ->get()
            ->map(function ($version) use ($type) {
                return [
                    'id_type' => (int) $type,
                    'id_version' => (int) $version->id_version,
                    'position' => (int) $version->version_position,
                    'name' => $version->name,
                ];
            })
            ->values()
            ->all();
    }

    public static function getBObrands($store = 0)
    {
        return self::query()
            ->select(
                'compats.id_brand',
                'compats_options.name',
                DB::raw('MIN(compats.brand_position) as brand_position')
            )
            ->join('compats_options', 'compats.id_brand', '=', 'compats_options.id_option')
            ->forStore($store)
            ->groupBy('compats.id_brand', 'compats_options.name')
            ->orderBy('brand_position', 'asc')
            ->orderBy('compats_options.name', 'asc')
            ->get()
            ->map(fn ($brand) => [
                'id_brand' => (int) $brand->id_brand,
                'name' => $brand->name,
            ])
            ->values()
            ->all();
    }

    public static function getBOmodels($brand, $store = 0)
    {
        return self::query()
            ->select(
                'compats.id_model',
                'compats_options.name',
                DB::raw('MIN(compats.model_position) as model_position')
            )
            ->join('compats_options', 'compats.id_model', '=', 'compats_options.id_option')
            ->where('compats.id_brand', $brand)
            ->forStore($store)
            ->groupBy('compats.id_model', 'compats_options.name')
            ->orderBy('model_position', 'asc')
            ->orderBy('compats_options.name', 'asc')
            ->get()
            ->map(fn ($model) => [
                'id_model' => (int) $model->id_model,
                'name' => $model->name,
            ])
            ->values()
            ->all();
    }

    public static function getBOtypes($brand, $model, $store = 0)
    {
        return self::query()
            ->select(
                'compats.id_type',
                'compats_options.name',
                DB::raw('MIN(compats.type_position) as type_position')
            )
            ->join('compats_options', 'compats.id_type', '=', 'compats_options.id_option')
            ->where('compats.id_brand', $brand)
            ->where('compats.id_model', $model)
            ->forStore($store)
            ->groupBy('compats.id_type', 'compats_options.name')
            ->orderBy('type_position', 'asc')
            ->orderBy('compats_options.name', 'asc')
            ->get()
            ->map(fn ($type) => [
                'id_type' => (int) $type->id_type,
                'name' => $type->name,
            ])
            ->values()
            ->all();
    }

    public static function getBOversions($brand, $model, $type, $store = 0)
    {
        return self::query()
            ->select(
                'compats.id_version',
                'compats_options.name',
                DB::raw('MIN(compats.version_position) as version_position')
            )
            ->join('compats_options', 'compats.id_version', '=', 'compats_options.id_option')
            ->where('compats.id_brand', $brand)
            ->where('compats.id_model', $model)
            ->where('compats.id_type', $type)
            ->forStore($store)
            ->groupBy('compats.id_version', 'compats_options.name')
            ->orderBy('version_position', 'asc')
            ->orderBy('compats_options.name', 'asc')
            ->get()
            ->map(fn ($version) => [
                'id_version' => (int) $version->id_version,
                'name' => $version->name,
            ])
            ->values()
            ->all();
    }
}
