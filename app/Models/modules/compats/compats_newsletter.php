<?php

namespace App\Models\modules\compats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class compats_newsletter extends Model{
    use HasFactory;

    protected $table = 'compats_newsletter';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'store',
        'id_customer',
        'id_compat',
        'id_brand',
        'brand',
        'id_model',
        'model',
        'id_type',
        'type',
        'id_version',
        'version',
        'email',
        'iso_code',
        'newsletter',
    ];

    protected $casts = [
        'id' => 'integer',
        'store' => 'integer',
        'id_customer' => 'integer',
        'id_compat' => 'integer',
        'id_brand' => 'integer',
        'id_model' => 'integer',
        'id_type' => 'integer',
        'id_version' => 'integer',
        'newsletter' => 'boolean',
    ];

    public function compat(){
        return $this->belongsTo(compats::class, 'id_compat', 'id_compat');
    }

    public function scopeFromStore($query, int $store){
        return $query->where('store', $store);
    }

    public function scopeForCustomer($query, int $idCustomer){
        return $query->where('id_customer', $idCustomer);
    }

    public static function saveMyCar($id_customer, string $iso_code, int $store, compats $compat, ?string $email = null): int{
        $isCustomerId = is_numeric($id_customer);
        $email = trim((string) ($email ?? ''));

        $row = new self();
        $row->id_compat = $compat->id_compat;
        $row->store = $store;
        $row->id_customer = $isCustomerId ? (int) $id_customer : 0;
        $row->id_brand = $compat->id_brand;
        $row->brand = $compat->brand->name ?? '';
        $row->id_model = $compat->id_model;
        $row->model = $compat->model->name ?? '';
        $row->id_type = $compat->id_type;
        $row->type = $compat->type->name ?? '';
        $row->id_version = $compat->id_version;
        $row->version = $compat->version->name ?? '';
        $row->email = $email !== '' ? $email : ($isCustomerId ? '' : (string) $id_customer);
        $row->iso_code = $iso_code;
        $row->newsletter = true;
        $row->save();

        return (int) $row->id;
    }

    public static function getMyGarage(int $id_customer){
        return self::where('id_customer', $id_customer)->get();
    }

    public static function removeCarFromMyGarage(int $id_customer, int $id_compat, int $store): int{
        $deleted = self::where('id_compat', $id_compat)->where('id_customer', $id_customer)->where('store', $store)->delete();
        return $deleted > 0 ? 1 : 0;
    }
}
