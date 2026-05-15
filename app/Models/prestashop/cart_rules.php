<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\Prestashop\PrestashopAdminLinkService;

class cart_rules extends PrestashopModel{
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('cart_rule');
    }

    public function country(){
        return $this->hasOne(country::class, 'id_country', 'id_country');
    }

    public static function dashboard_inactive_cart_rules($type)
    {
        $data = [];
    
        $excludedCartRuleIds = asm_dashboard::getExceptions('inactive_cart_rules')
            ->pluck('id_product')
            ->toArray();
    
        $query = self::where('active', 0)
            ->where('description', '!=', 'Bonus system');
    
        if (!empty($excludedCartRuleIds)) {
            $query->whereNotIn('id_cart_rule', $excludedCartRuleIds);
        }
    
        $bd_data = $query->get();
    
        foreach ($bd_data as $item) {
            $data[] = [
                'clean'        => $item->id_cart_rule,
                'id_cart_rule' => $item->id_cart_rule,
                'code'         => $item->code,
                'description'  => $item->description,
                'url'          => PrestashopAdminLinkService::legacyAdminUrl(
                    'AdminLsgwebtoolsbridgeRedirect',
                    [
                        'target_controller' => 'AdminCartRules',
                        'target_params'     => base64_encode(http_build_query([
                            'id_cart_rule'    => $item->id_cart_rule,
                            'updatecart_rule' => '',
                        ])),
                    ],
                    'ASM'
                ),
            ];
        }
    
        return self::dashboardPanel(
            trans('dashboard.Inactive cart rules'),
            $type,
            'inactive_cart_rules',
            ['clean', 'id_cart_rule', 'code', 'description'],
            $data,
            [
                'exception_fields' => ['inactive_cart_rules', 'id_cart_rule', 'code', 'description'],
            ]
        );
    }
}