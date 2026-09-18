<?php
namespace App\Models\modules\moloni_vat_validation;
use Illuminate\Database\Eloquent\Model;
class MoloniVatValidationOrder extends Model {
 protected $table='moloni_vat_validation_orders';
 protected $fillable=['id_order','id_customer','customer_group_id','moloni_invoice_id','moloni_vat_validation_id','source'];
 protected $casts=['id_order'=>'integer','id_customer'=>'integer','customer_group_id'=>'integer','moloni_invoice_id'=>'integer','moloni_vat_validation_id'=>'integer'];
 public function validation(){return $this->belongsTo(MoloniVatValidation::class,'moloni_vat_validation_id');}
}
