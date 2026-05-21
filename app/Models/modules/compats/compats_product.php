<?php

namespace App\Models\modules\compats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class compats_product extends Model
{
    use HasFactory;

    protected $table = 'compats_product';
    protected $primaryKey = 'id_compat_product';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_compat',
        'id_product',
        'store',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id_compat_product' => 'integer',
        'id_compat' => 'integer',
        'id_product' => 'integer',
        'store' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function compat()
    {
        return $this->belongsTo(compats::class, 'id_compat', 'id_compat');
    }

    public function scopeFromStore($query, int $store)
    {
        return $query->where('store', $store);
    }

    public static function existCompat(int $id_brand, int $id_model, int $id_type, int $id_version, int $store = 0): int
    {
        $compat = compats::where('store', $store)
            ->where('id_brand', $id_brand)
            ->where('id_model', $id_model)
            ->where('id_type', $id_type)
            ->where('id_version', $id_version)
            ->first();

        return (int) ($compat->id_compat ?? 0);
    }

    public static function getProducts(int $id_compat, int $store = 0): array
    {
        return self::where('id_compat', $id_compat)
            ->where('store', $store)
            ->pluck('id_product')
            ->map(fn ($id) => (int) $id)
            ->toArray();
    }

    public static function getCompats(int $id_product, int $store = 0)
    {
        return self::with(['compat.brand', 'compat.model', 'compat.type', 'compat.version'])
            ->where('id_product', $id_product)
            ->where('store', $store)
            ->get();
    }

    public static function createCompat($id_brand, $id_model, $id_type, $id_version, $id_product, $store = 0): int
    {
        $idCompat = compats::createCompat($id_brand, $id_model, $id_type, $id_version, $store);

        if (!$idCompat) {
            return 0;
        }

        $existing = self::where('id_compat', (int) $idCompat)
            ->where('id_product', (int) $id_product)
            ->where('store', (int) $store)
            ->first();

        if ($existing) {
            return 1;
        }

        self::create([
            'id_compat' => (int) $idCompat,
            'id_product' => (int) $id_product,
            'store' => (int) $store,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return 1;
    }

    public static function removeCompat(int $id_compat, int $store): int
    {
        self::where('id_compat', $id_compat)
            ->where('store', $store)
            ->delete();

        return 1;
    }
}
