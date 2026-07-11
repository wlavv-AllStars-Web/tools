<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\Prestashop\PrestashopAdminLinkService;

class product_comment extends PrestashopModel
{
    use HasFactory;

    protected $primaryKey = 'id_product_comment';
    protected $fillable = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = self::tableName('product_comment');
    }

    public static function dashboard_reviews($type)
    {
        $pendingReviews = self::where('deleted', 0)
            ->where('validate', 0)
            ->count();

        $data = [];

        if ($pendingReviews > 0) {
            $data[] = [
                'pending_reviews' => $pendingReviews,
                'url' => PrestashopAdminLinkService::dashboardReviewsUrl('ASM'),
            ];
        }

        return self::dashboardPanel(
            trans('dashboard.Reviews'),
            $type,
            'reviews',
            ['pending_reviews'],
            $data,
            ['counter' => $pendingReviews]
        );
    }
}

