<?php
namespace App\Models\modules\moloni_vat_validation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
class MoloniVatValidation extends Model {
 protected $table='moloni_vat_validations';
 protected $fillable=['country_iso','vat_number','normalized_vat_number','status','attempts','last_attempt_at','next_attempt_at','validated_at','valid_until','last_error','vies_response','manual_notes'];
 protected $casts=['attempts'=>'integer','last_attempt_at'=>'datetime','next_attempt_at'=>'datetime','validated_at'=>'datetime','valid_until'=>'datetime','vies_response'=>'array'];
 public const STATUS_PENDING='pending'; public const STATUS_PROCESSING='processing'; public const STATUS_RETRY_SCHEDULED='retry_scheduled'; public const STATUS_VALID='valid'; public const STATUS_INVALID='invalid'; public const STATUS_MISSING_VAT='missing_vat'; public const STATUS_MANUAL_REVIEW='manual_review';
 public static function normalizeCountryIso(?string $value): string { return strtoupper(substr(trim((string)$value),0,2)); }
 public static function normalizeVatNumber(?string $vat, ?string $country=null): string { $vat=strtoupper((string)$vat); $vat=preg_replace('/[^A-Z0-9]/','',$vat)??''; $country=self::normalizeCountryIso($country); return $country!==''&&str_starts_with($vat,$country)?substr($vat,2):$vat; }
 public static function normalizedVat(?string $country, ?string $vat): string { $country=self::normalizeCountryIso($country); return $country.self::normalizeVatNumber($vat,$country); }
 public function scopeDue(Builder $query): Builder { return $query->whereIn('status',[self::STATUS_PENDING,self::STATUS_RETRY_SCHEDULED])->where(fn(Builder $q)=>$q->whereNull('next_attempt_at')->orWhere('next_attempt_at','<=',now())); }
 public function isFreshlyValid(): bool { return $this->status===self::STATUS_VALID&&$this->valid_until!==null&&$this->valid_until->isFuture(); }
 public function orders(){return $this->hasMany(MoloniVatValidationOrder::class,'moloni_vat_validation_id');}
}
