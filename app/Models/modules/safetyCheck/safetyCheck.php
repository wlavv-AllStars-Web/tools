<?php
namespace App\Models\modules\safetyCheck;

use Illuminate\Database\Eloquent\Model;

class safetyCheck extends Model
{
    protected $table = 'safety_check';

    protected $fillable = [ 'equipment','estado_cabos','estado_geral','torre','elevacao','direcao','travao','travao_emergencia','travao_estacionamento','comandos','garfos','buzina','observacoes','user_id' ];
}
