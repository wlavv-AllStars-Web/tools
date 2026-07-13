<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Services\Prestashop\PrestashopAdminLinkService;

class orders extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_order';
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('orders');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function order_detail()
    {
        return $this->hasMany(orders_details::class, 'id_order', 'id_order');
    }

    public function customer()
    {
        return $this->hasOne(customer::class, 'id_customer', 'id_customer');
    }

    public function delivery()
    {
        return $this->hasOne(address::class, 'id_address', 'id_address_delivery');
    }

    public function invoice()
    {
        return $this->hasOne(address::class, 'id_address', 'id_address_invoice');
    }

    public function carrier()
    {
        return $this->hasOne(carriers::class, 'id_carrier', 'id_carrier');
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD HELPERS
    |--------------------------------------------------------------------------
    */

    protected static function adminOrdersLink()
    {
        return PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD');
    }

    protected static function excludedIdsFromBoard($board)
    {
        return asm_dashboard::getExceptions($board)
            ->pluck('id_product')
            ->toArray();
    }

    protected static function dashboardPanel($name, $type, $suffix, array $columns, $data, array $extra = [], $prestashop = null, int $col = 4): array
    {
        $store = $prestashop['store'] ?? 'ASD';

        $data = collect($data)
            ->map(function ($row) use ($store) {
                $row = (array) $row;

                if (!empty($row['id_order']) && empty($row['url'])) {
                    $row['url'] = PrestashopAdminLinkService::dashboardOrderAdminUrl((int) $row['id_order'], $store);
                }

                return $row;
            })
            ->values()
            ->all();

        return parent::dashboardPanel(
            $name,
            $type,
            $suffix,
            $columns,
            $data,
            $extra,
            $prestashop ?? PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASD'),
            $col
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPLE DASHBOARDS
    |--------------------------------------------------------------------------
    */

    public static function dashboard_order_payed_with_voucher($type)
    {
        $data = [];
        $excludedOrderIds = self::excludedIdsFromBoard('order_payed_with_voucher');
        $ordersTable = self::tableName('orders');
        $orderCartRuleTable = self::tableName('order_cart_rule');
        $paidStates = array_map('intval', config('allstars.auto_orders.paid_order_states', [2, 3, 4, 5, 15, 16, 28]));
        $countableCurrentStates = array_values(array_unique(array_merge($paidStates, [30, 31])));

        $query = self::select(
                $ordersTable . '.id_order',
                $ordersTable . '.reference',
                $ordersTable . '.current_state',
                $ordersTable . '.total_discounts',
                DB::raw('GROUP_CONCAT(DISTINCT ' . $orderCartRuleTable . '.name SEPARATOR ", ") AS voucher')
            )
            ->leftJoin($orderCartRuleTable, $orderCartRuleTable . '.id_order', '=', $ordersTable . '.id_order')
            ->whereIn($ordersTable . '.current_state', $countableCurrentStates)
            ->whereNotNull($orderCartRuleTable . '.id_order_cart_rule')
            ->groupBy(
                $ordersTable . '.id_order',
                $ordersTable . '.reference',
                $ordersTable . '.current_state',
                $ordersTable . '.total_discounts'
            );

        if (!empty($excludedOrderIds)) {
            $query->whereNotIn($ordersTable . '.id_order', $excludedOrderIds);
        }

        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id_order,
                'id_order' => $item->id_order,
                'reference' => $item->reference,
                'current_state' => $item->current_state,
                'voucher' => $item->voucher,
                'total_discounts' => $item->total_discounts,
            ];
        }

        return self::dashboardPanel(
            trans('dashboard.ORDERS PAYED WITH VOUCHER'),
            $type,
            'order_payed_with_voucher',
            ['clean', 'id_order', 'reference', 'current_state', 'voucher', 'total_discounts'],
            $data,
            [
                'exception_fields' => ['order_payed_with_voucher', 'id_order', 'reference', 'total_discounts']
            ]
        );
    }

    public static function dashboard_partial_orders($type)
    {
        $data = [];

        foreach (self::select('id_order', 'reference')->where('current_state', 28)->get() as $item) {
            $data[] = [
                'id_order' => $item->id_order,
                'reference' => $item->reference,
            ];
        }

        return self::dashboardPanel(
            trans('dashboard.PARTIAL ORDERS'),
            $type,
            'partial_orders',
            ['id_order', 'reference'],
            $data
        );
    }

    public static function dashboard_waiting_info($type)
    {
        $data = [];

        foreach (self::select('id_order', 'reference')
            ->where('current_state', 30)
            ->get() as $item) {
            $data[] = [
                'id_order' => $item->id_order,
                'reference' => $item->reference,
            ];
        }

        return self::dashboardPanel(
            trans('dashboard.ORDERS - WAITING INFO'),
            $type,
            'waiting_info',
            ['id_order', 'reference'],
            $data,
            [],
            PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASM')
        );
    }

    public static function dashboard_returns_warranties($type)
    {
        $data = [];

        $ordersTable = self::tableName('orders');
        $customerTable = self::tableName('customer');

        $orders = self::select($ordersTable . '.id_order', $ordersTable . '.reference')
            ->join($customerTable, $ordersTable . '.id_customer', '=', $customerTable . '.id_customer')
            ->where($ordersTable . '.current_state', 29)
            ->where($ordersTable . '.id_shop', PrestashopAdminLinkService::shopId('ASM'))
            ->get();

        foreach ($orders as $order) {
            $data[] = [
                'id_order' => $order->id_order,
                'reference' => $order->reference,
                'products' => orders_details::getProductsOfOrder($order->id_order),
                'url' => PrestashopAdminLinkService::dashboardOrderAdminUrl((int) $order->id_order, 'ASM'),
            ];
        }

        return self::dashboardPanel(
            trans('dashboard.RETURNS & WARRANTIES OK'),
            $type,
            'returns_warranties',
            ['id_order', 'reference', 'products'],
            $data,
            [],
            PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASM')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT-REFERENCE QUERIES
    |--------------------------------------------------------------------------
    */

    protected static function getOrdersByStateAndReference($state, $product_reference)
    {
        $ordersTable = self::tableName('orders');
        $orderDetailTable = self::tableName('order_detail');

        return DB::connection('mysql2')
            ->table($ordersTable)
            ->join($orderDetailTable, $ordersTable . '.id_order', '=', $orderDetailTable . '.id_order')
            ->where($ordersTable . '.current_state', $state)
            ->where($orderDetailTable . '.product_reference', $product_reference)
            ->get()
            ->toArray();
    }

    public static function getParcials($product_reference)
    {
        return self::getOrdersByStateAndReference(28, $product_reference);
    }

    public static function getPreparations($product_reference)
    {
        return self::getOrdersByStateAndReference(3, $product_reference);
    }

    public static function getBackorders($product_reference)
    {
        return self::getOrdersByStateAndReference(15, $product_reference);
    }

    /*
    |--------------------------------------------------------------------------
    | SOLD ITEMS
    |--------------------------------------------------------------------------
    */

    public static function getSoldItems($reference)
    {
        $ordersTable = self::tableName('orders');
        $orderDetailTable = self::tableName('order_detail');
        $date = date('Y-m-d', strtotime('-1 year'));

        return (int) DB::connection('mysql2')
            ->table($ordersTable)
            ->join($orderDetailTable, $ordersTable . '.id_order', '=', $orderDetailTable . '.id_order')
            ->where($orderDetailTable . '.product_reference', $reference)
            ->where($ordersTable . '.date_add', '>', $date)
            ->sum($orderDetailTable . '.product_quantity');
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD WITHOUT TRACKING
    |--------------------------------------------------------------------------
    */

    public static function dashboard_without_tracking($type)
    {
        $data = [];

        $ordersTable = self::tableName('orders');
        $orderStateLangTable = self::tableName('order_state_lang');
        $orderCarrierTable = self::tableName('order_carrier');

        $excludedOrderIds = self::excludedIdsFromBoard('orders_without_tracking');

        $query = self::select(
                $ordersTable . '.id_order',
                $ordersTable . '.id_shop',
                $ordersTable . '.reference',
                $orderStateLangTable . '.name AS state'
            )
            ->join($orderStateLangTable, $ordersTable . '.current_state', '=', $orderStateLangTable . '.id_order_state')
            ->join($orderCarrierTable, $ordersTable . '.id_order', '=', $orderCarrierTable . '.id_order')
            ->where($orderStateLangTable . '.id_lang', 1)
            ->where(function ($query) use ($orderCarrierTable) {
                $query->whereNull($orderCarrierTable . '.tracking_number')
                    ->orWhere($orderCarrierTable . '.tracking_number', '');
            })
            ->whereIn($ordersTable . '.current_state', [4, 28])
            ->groupBy($ordersTable . '.id_order', $ordersTable . '.id_shop', $ordersTable . '.reference', $orderStateLangTable . '.name');

        if (!empty($excludedOrderIds)) {
            $query->whereNotIn($ordersTable . '.id_order', $excludedOrderIds);
        }

        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id_order,
                'id_order' => $item->id_order,
                'reference' => $item->reference,
                'state' => $item->state,
                'url' => PrestashopAdminLinkService::dashboardOrderAdminUrl(
                    (int) $item->id_order,
                    config('allstars.auto_orders.shop_codes', [])[(int) $item->id_shop] ?? 'ASM'
                ),
            ];
        }

        return self::dashboardPanel(
            trans('dashboard.ORDER WITHOUT TRACKING'),
            $type,
            'orders_without_tracking',
            ['clean', 'id_order', 'reference', 'state'],
            $data,
            [
                'exception_fields' => ['orders_without_tracking', 'id_order', 'reference', 'state']
            ],
            PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASM')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DUPLICATED ORDERS / STATUS
    |--------------------------------------------------------------------------
    */
    
    public static function dashboard_duplicated_orders($type = 'panel', $counter = null)
    {
        $data = [];
    
        $excludedOrderIds = self::excludedIdsFromBoard('duplicated_order');
    
        $duplicateGroups = self::select( 'id_customer', 'payment', 'total_paid', DB::raw('DATE(date_add) AS order_date'), DB::raw('COUNT(*) AS repeated') )
            ->when(!empty($excludedOrderIds), function ($query) use ($excludedOrderIds) {
                $query->whereNotIn('id_order', $excludedOrderIds);
            })
            ->groupBy( 'id_customer', 'payment', 'total_paid', DB::raw('DATE(date_add)') )
            ->havingRaw('COUNT(*) > 1')
            ->get();
    
        foreach ($duplicateGroups as $group) {
            $orders = self::select(
                    'id_order',
                    'reference',
                    'total_paid',
                    'id_customer',
                    'payment',
                    'date_add'
                )
                ->where('id_customer', $group->id_customer)
                ->where('payment', $group->payment)
                ->where('total_paid', $group->total_paid)
                ->whereDate('date_add', $group->order_date)
                ->when(!empty($excludedOrderIds), function ($query) use ($excludedOrderIds) {
                    $query->whereNotIn('id_order', $excludedOrderIds);
                })
                ->orderBy('id_order')
                ->get();
    
            foreach ($orders as $order) {
                $data[] = [
                    'clean'      => $order->id_order,
                    'id_order'   => $order->id_order,
                    'reference'  => $order->reference,
                    'total_paid' => $order->total_paid,
                    'repeated'   => $group->repeated,
                    'url'        => PrestashopAdminLinkService::dashboardOrderAdminUrl($order->id_order, 'ASD'),
                ];
            }
        }
    
        return self::dashboardPanel(
            trans('dashboard.DUPLICATED ORDER'),
            $type,
            'duplicated_order',
            ['clean', 'id_order', 'reference', 'total_paid', 'repeated', 'url'],
            $data,
            [
                'exception_fields' => ['duplicated_order', 'id_order', 'reference', 'repeated'],
            ]
        );
    }


    public static function dashboard_duplicated_status($type)
    {
        $data = [];
        $excludedOrderIds = self::excludedIdsFromBoard('duplicated_status');

        $bd_data = order_history::getPanelInfo($excludedOrderIds);

        foreach ($bd_data as $item) {
            $data[] = [
                'clean' => $item->id_order,
                'id_order' => $item->id_order,
                'reference' => $item->reference,
                'id_order_state' => $item->id_order_state,
                'status' => $item->status,
                'client' => $item->firstname . ' ' . $item->lastname,
                'total' => $item->total,
            ];
        }

        return self::dashboardPanel(
            trans('dashboard.DUPLICATED STATUS'),
            $type,
            'duplicated_status',
            ['clean', 'id_order', 'reference', 'id_order_state', 'status', 'client', 'total'],
            $data,
            [
                'exception_fields' => ['duplicated_status', 'id_order', 'reference', 'client']
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SHIPPING
    |--------------------------------------------------------------------------
    */

    public static function shippingPaidByCustomer($year)
    {
        $ordersTable = self::tableName('orders');
        $carrierTable = self::tableName('carrier');
        $dateFrom = $year . '-01-01 00:00:00';

        $total_order = self::select(DB::raw('SUM(' . $ordersTable . '.total_shipping_tax_incl) AS total_shipping'))
            ->where($ordersTable . '.date_add', '>', $dateFrom)
            ->whereIn($ordersTable . '.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->value('total_shipping');

        $total_by_carrier = self::select(
                $carrierTable . '.name AS name',
                DB::raw('SUM(' . $ordersTable . '.total_shipping_tax_incl) AS total_shipping')
            )
            ->leftJoin($carrierTable, $carrierTable . '.id_carrier', '=', $ordersTable . '.id_carrier')
            ->where($ordersTable . '.date_add', '>', $dateFrom)
            ->whereIn($ordersTable . '.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->groupBy($carrierTable . '.id_reference', $carrierTable . '.name')
            ->get();

        $carrier_data = [
            'DPD' => 0,
            'UPS' => 0,
            'TNT' => 0,
            'NACEX' => 0,
            'GLS' => 0,
            'FEDEX' => 0,
        ];

        foreach ($total_by_carrier as $carrier) {
            $name = strtoupper($carrier->name ?? '');
            $total = (float) ($carrier->total_shipping ?? 0);

            if (strpos($name, 'DPD') !== false)   $carrier_data['DPD'] += $total;
            if (strpos($name, 'UPS') !== false)   $carrier_data['UPS'] += $total;
            if (strpos($name, 'TNT') !== false)   $carrier_data['TNT'] += $total;
            if (strpos($name, 'NACEX') !== false) $carrier_data['NACEX'] += $total;
            if (strpos($name, 'GLS') !== false)   $carrier_data['GLS'] += $total;
            if (strpos($name, 'FEDEX') !== false) $carrier_data['FEDEX'] += $total;
        }

        $carrier_data['total'] = (float) ($total_order ?? 0);

        return $carrier_data;
    }

    /*
    |--------------------------------------------------------------------------
    | KPI COUNTERS
    |--------------------------------------------------------------------------
    */

    public static function getCounters($id_shop, $expectedEvolution)
    {
        $ordersTable = self::tableName('orders');
        $orderHistoryTable = self::tableName('order_history');

        if ($id_shop == 2) {
            $yesterday = order_payment::getASMTotals(0);
            $today = order_payment::getASMTotals(1);
        } elseif ($id_shop == 3) {
            $yesterday = order_payment::getASDTotals(0);
            $today = order_payment::getASDTotals(1);
        } else {
            $yesterday = order_payment::getShopTotals($id_shop, 0);
            $today = order_payment::getShopTotals($id_shop, 1);
        }

        $data = [];

        $data['awaiting'] = 0;

        $data['packing'] = self::query()
            ->where('id_shop', $id_shop)
            ->where('current_state', 3)
            ->count();

        $data['shipped'] = self::query()
            ->join($orderHistoryTable, $orderHistoryTable . '.id_order', '=', $ordersTable . '.id_order')
            ->where($ordersTable . '.id_shop', $id_shop)
            ->where($ordersTable . '.current_state', 4)
            ->where($orderHistoryTable . '.id_order_state', 4)
            ->where($orderHistoryTable . '.date_add', '>', date('Y-m-d') . ' 00:00:00')
            ->count();

        $data['warranty'] = self::query()
            ->where('id_shop', $id_shop)
            ->where('current_state', 29)
            ->count();

        $data['backorders'] = self::query()
            ->where('id_shop', $id_shop)
            ->where('current_state', 15)
            ->count();

        $data['partial'] = self::query()
            ->where('id_shop', $id_shop)
            ->where('current_state', 28)
            ->count();

        $data['pending'] = self::query()
            ->where('id_shop', $id_shop)
            ->where('current_state', 30)
            ->count();

        $data['today_forcast'] = (float) $today->homologue_day * $expectedEvolution;
        $data['today_realized'] = (float) $today->day;
        $data['yesterday_forcast'] = (float) $yesterday->homologue_day * $expectedEvolution;
        $data['yesterday_realized'] = (float) $yesterday->day;

        return (object) $data;
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER REVIEW PANELS
    |--------------------------------------------------------------------------
    */

    public static function dashboard_order_reviewed($type)
    {
        return self::dashboardOrderReviewedBase(
            $type,
            'order_reviewed',
            7,
            15
        );
    }

    public static function dashboard_order_reviewed_2($type)
    {
        return self::dashboardOrderReviewedBase(
            $type,
            'order_reviewed_2',
            15,
            30
        );
    }

    protected static function dashboardOrderReviewedBase($type, $board, $daysSinceShipped, $daysSinceOrder)
    {
        $data = [];

        $ordersTable = self::tableName('orders');
        $customerTable = self::tableName('customer');
        $orderHistoryTable = self::tableName('order_history');
        $customOrdersTable = self::tableName('custom_orders');
        $hasCustomNotForReview = self::hasPrestashopColumn($customOrdersTable, 'not_for_review');
        $hasLegacyNotForReview = self::hasPrestashopColumn($ordersTable, 'not_for_review');

        $excludedOrderIds = self::excludedIdsFromBoard($board);

        $beforeShipped = now()->subDays($daysSinceShipped);
        $afterOrder = now()->subDays($daysSinceOrder);

        $query = self::select(
                $ordersTable . '.id_order',
                $ordersTable . '.reference',
                $customerTable . '.email',
                DB::raw("(SELECT oh.date_add
                    FROM {$orderHistoryTable} oh
                    WHERE oh.id_order = {$ordersTable}.id_order
                    AND oh.id_order_state = 4
                    ORDER BY oh.date_add ASC
                    LIMIT 1) AS date_shipped")
            )
            ->leftJoin($customerTable, $customerTable . '.id_customer', '=', $ordersTable . '.id_customer')
            ->when($hasCustomNotForReview, function ($query) use ($ordersTable, $customOrdersTable) {
                $query->leftJoin($customOrdersTable, $customOrdersTable . '.id_order', '=', $ordersTable . '.id_order');
            })
            ->where($ordersTable . '.id_shop', PrestashopAdminLinkService::shopId('ASM'))
            ->where($ordersTable . '.current_state', 4)
            ->whereNotNull($customerTable . '.email')
            ->where($customerTable . '.email', '<>', '')
            ->where($ordersTable . '.date_add', '>', $afterOrder)
            ->when($hasCustomNotForReview, function ($query) use ($customOrdersTable) {
                $query->where(function ($subQuery) use ($customOrdersTable) {
                    $subQuery->whereNull($customOrdersTable . '.not_for_review')
                        ->orWhere($customOrdersTable . '.not_for_review', '<>', 1);
                });
            })
            ->when(!$hasCustomNotForReview && $hasLegacyNotForReview, function ($query) use ($ordersTable) {
                $query->where($ordersTable . '.not_for_review', '<>', 1);
            })
            ->whereNotExists(function ($sub) use ($ordersTable, $orderHistoryTable) {
                $sub->select(DB::raw(1))
                    ->from($orderHistoryTable)
                    ->whereRaw($orderHistoryTable . '.id_order = ' . $ordersTable . '.id_order')
                    ->where($orderHistoryTable . '.id_order_state', 15);
            })
            ->whereExists(function ($sub) use ($ordersTable, $orderHistoryTable, $beforeShipped) {
                $sub->select(DB::raw(1))
                    ->from($orderHistoryTable)
                    ->whereRaw($orderHistoryTable . '.id_order = ' . $ordersTable . '.id_order')
                    ->where($orderHistoryTable . '.id_order_state', 4)
                    ->where($orderHistoryTable . '.date_add', '<', $beforeShipped);
            });

        if (!empty($excludedOrderIds)) {
            $query->whereNotIn($ordersTable . '.id_order', $excludedOrderIds);
        }

        foreach ($query->get() as $item) {
            $data[] = [
                'clean' => $item->id_order,
                'id_order' => $item->id_order,
                'email' => $item->email,
                'send_email_reviewed' => $item->email,
                'date_shipped' => $item->date_shipped ? date('Y-m-d', strtotime($item->date_shipped)) : null,
            ];
        }

        return self::dashboardPanel(
            trans('dashboard.ORDERS PAYED WITH VOUCHER'),
            $type,
            'order_reviewed',
            ['clean', 'id_order', 'date_shipped', 'email', 'send_email_reviewed'],
            $data,
            [
                'exception_fields' => [$board, 'id_order', 'date_shipped', 'email', 'send_email_reviewed']
            ],
            PrestashopAdminLinkService::dashboardOrderLink('id_order', 'ASM')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COMPLEX ORDER STATE ANALYSIS
    |--------------------------------------------------------------------------
    */
    public static function getAllOrdersOf($id_current_state, $id_shop = null){
        
        $query = self::with(
            'order_detail.product.stock',
            'order_detail.product.pack.product_pack.stock_product_pack',
            'order_detail.product.manufacturer',
            'order_detail.product_attribute.stock',
            'customer',
            'delivery.country',
            'invoice.country.lang_en'
        )
        ->where('current_state', $id_current_state);
    
        if (!is_null($id_shop)) {
            $query->where('id_shop', $id_shop);
        }
    
        $data = $query->get();
    
        if ($id_current_state == 15) {
            foreach ($data as $item) {
                $extra_data_product = [];
    
                $paymentHistory = order_history::where('id_order', $item->id_order)
                    ->where('id_order_state', 2)
                    ->select('date_add')
                    ->first();

                $detailIds = $item->order_detail
                    ->pluck('id_order_detail')
                    ->filter()
                    ->values()
                    ->all();

                $customDetails = collect();

                if (!empty($detailIds)) {
                    $customDetails = DB::connection('mysql2')
                        ->table(self::tableName('custom_order_detail'))
                        ->whereIn('id_order_detail', $detailIds)
                        ->get()
                        ->keyBy('id_order_detail');
                }
    
                foreach ($item->order_detail as $detail) {
                    $is_pack = 0;
    
                    if ($detail->product_quantity <= 0) {
                        continue;
                    }

                    $customDetail = $customDetails->get($detail->id_order_detail);
                    $qtdSent = (int) ($customDetail->qtd_sent ?? 0);
                    $control = (int) ($customDetail->control ?? 0);
                    $lineStock = self::stockQuantityFor(
                        (int) $detail->product_id,
                        (int) $detail->product_attribute_id,
                        (int) $item->id_shop
                    );
    
                    $row_extra_data_product = [
                        'id_product' => $detail->product_id,
                        'id_product_attribute' => 0,
                        'reference' => $detail->product_reference,
                        'supplier' => isset($detail->product->supplier) ? $detail->product->supplier->name : '',
                        'brand' => isset($detail->product->manufacturer) ? $detail->product->manufacturer->name : 'N/D',
                        'product_quantity' => $detail->product_quantity,
                        'qtd_sent' => $qtdSent,
                        'control' => $control,
                        'type' => $detail->type,
                        'sold' => $detail->product_quantity - $qtdSent,
                        'date_payed' => $paymentHistory->date_add ?? null,
                        'store' => self::shopCode($id_shop),
                        'stock' => $lineStock ?? 0,
                    ];
    
                    if ($detail->product_attribute_id != 0) {
                        $row_extra_data_product['id_product_attribute'] = $detail->product_attribute_id;
                    } else {
                        if (isset($detail->product->pack[0])) {
                            foreach ($detail->product->pack as $product_pack) {
                                $row_extra_data_product['id_product'] = $product_pack->id_product_item;
                                $row_extra_data_product['id_product_attribute'] = $product_pack->id_product_attribute_item;
    
                                $row_extra_data_product['sold'] = ($product_pack->quantity * $detail->product_quantity) - $qtdSent;
                                $row_extra_data_product['stock'] = self::stockQuantityFor(
                                    (int) $product_pack->id_product_item,
                                    (int) $product_pack->id_product_attribute_item,
                                    (int) $item->id_shop
                                ) ?? 0;
    
                                if (($row_extra_data_product['stock'] < 1) && ($row_extra_data_product['sold'] != 0)) {
                                    $extra_data_product[] = $row_extra_data_product;
                                }
    
                                $is_pack = 1;
                            }
                        }
                    }
    
                    if ($is_pack == 0 && isset($row_extra_data_product['stock'])) {
                        if (($id_current_state == 15) && ($row_extra_data_product['stock'] < 1)) {
                            $extra_data_product[] = $row_extra_data_product;
                        }
                    }
                }
    
                $item->extraDataField = $extra_data_product;
            }
        }
    
        return $data;
    }

    private static function stockQuantityFor(int $idProduct, int $idProductAttribute, int $idShop): ?int
    {
        if ($idProduct <= 0) {
            return null;
        }

        $stockTable = self::tableName('stock_available');

        return DB::connection('mysql2')
            ->table($stockTable)
            ->where('id_product', $idProduct)
            ->where('id_product_attribute', $idProductAttribute)
            ->when($idShop > 0, fn ($query) => $query->where('id_shop', $idShop))
            ->value('quantity');
    }

    private static function shopCode($idShop): string
    {
        return match ((int) $idShop) {
            2 => 'ASM',
            3 => 'ASD',
            default => is_null($idShop) ? 'ALL' : (string) $idShop,
        };
    }
}
