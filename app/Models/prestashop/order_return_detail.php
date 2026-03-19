<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\prestashop\order_return;
use App\Models\prestashop\orders_details;

class order_return_detail extends Model
{
    use HasFactory;

    protected $connection = 'mysql2';
    public $timestamps = false;
    /**protected $primaryKey = 'id_order_return_detail';**/
    protected $primaryKey = 'id_order_return';
    protected $fillable = ['files_required'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = env('DB2_prefix') . 'order_return_detail';
    }

    public function return(){
        return $this->belongsTo(order_return::class, 'id_order_return');
    }

    public function orderDetail(){
        return $this->belongsTo(orders_details::class, 'id_order_detail', 'id_order_detail');
    }    
}
