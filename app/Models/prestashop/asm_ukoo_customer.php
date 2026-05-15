<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\modules\compats\compats_newsletter;

class asm_ukoo_customer extends PrestashopModel
{
    use HasFactory;

    protected $fillable = ['name'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = self::tableName('asm_ukoo_customer');
    }

    public static function dashboard_newsletter_registration($type)
    {
        $data = [];
        $excludedDomains = config('allstars.emails.excluded_domains', []);

        if (Schema::hasTable('compats_newsletter')) {
            $query = compats_newsletter::query()
                ->where('newsletter', 1)
                ->whereNotNull('email')
                ->where('email', '<>', '')
                ->where('email', '!=', 'bruno.fernandes.asm@gmail.com')
                ->when(!empty($excludedDomains), function ($query) use ($excludedDomains) {
                    foreach ($excludedDomains as $domain) {
                        $query->where('email', 'NOT LIKE', '%@' . $domain . '%');
                    }
                });

            $bd_data = (clone $query)
                ->select(
                    'brand',
                    'model',
                    'type',
                    'version',
                    DB::raw('COUNT(*) AS customers_by_version')
                )
                ->groupBy('id_brand', 'id_model', 'id_type', 'id_version', 'brand', 'model', 'type', 'version')
                ->orderBy('customers_by_version', 'DESC')
                ->get();

            foreach ($bd_data as $item) {
                $data[] = [
                    'name' => $item->brand . ' | ' . $item->model . ' | ' . $item->type . ' | ' . $item->version . ' | ',
                    'customers_by_version' => $item->customers_by_version
                ];
            }

            return [
                'name' => trans("dashboard.Newsletter registrations"),
                'col' => 4,
                'item_id' => $type . '_newsletters_registrations',
                'columns' => ['name', 'customers_by_version'],
                'counter' => (clone $query)->count(),
                'info' => true,
                'data' => $data
            ];
        }

        if (!self::hasPrestashopTable(self::tableName('asm_ukoo_customer'))) {
            return [
                'name' => trans("dashboard.Newsletter registrations"),
                'col' => 4,
                'item_id' => $type . '_newsletters_registrations',
                'columns' => ['name', 'customers_by_version'],
                'counter' => 0,
                'info' => true,
                'data' => $data
            ];
        }

        $bd_data = self::select(DB::raw('*'), DB::raw('COUNT(*) AS customers_by_version'))
            ->where('newsletter', 1)
            ->where('email', '!=', 'bruno.fernandes.asm@gmail.com')
            ->when(!empty($excludedDomains), function ($query) use ($excludedDomains) {
                foreach ($excludedDomains as $domain) {
                    $query->where('email', 'NOT LIKE', '%@' . $domain . '%');
                }
            })
            ->groupBy('version')
            ->orderBy('customers_by_version', 'DESC')
            ->get();

        foreach ($bd_data as $item) {
            $data[] = [
                'name' => $item->brand . ' | ' . $item->model . ' | ' . $item->type . ' | ' . $item->version . ' | ',
                'customers_by_version' => $item->customers_by_version
            ];
        }

        return [
            'name' => trans("dashboard.Newsletter registrations"),
            'col' => 4,
            'item_id' => $type . '_newsletters_registrations',
            'columns' => ['name', 'customers_by_version'],
            'counter' => self::where('newsletter', 1)
                ->where('email', '!=', 'bruno.fernandes.asm@gmail.com')
                ->when(!empty($excludedDomains), function ($query) use ($excludedDomains) {
                    foreach ($excludedDomains as $domain) {
                        $query->where('email', 'NOT LIKE', '%@' . $domain . '%');
                    }
                })
                ->count(),
            'info' => true,
            'data' => $data
        ];
    }

    public static function getEmailsOfTheCompats($detail, $iso)
    {
        if (Schema::hasTable('compats_newsletter')) {
            return compats_newsletter::where('id_brand', $detail->brand)
                ->where('id_model', $detail->model)
                ->where('id_version', $detail->version)
                ->where('id_type', $detail->type)
                ->where('iso_code', $iso)
                ->where('newsletter', 1)
                ->pluck('email');
        }

        return self::where('id_brand', $detail->brand)
            ->where('id_model', $detail->model)
            ->where('id_version', $detail->version)
            ->where('id_type', $detail->type)
            ->where('iso_code', $iso)
            ->where('newsletter', 1)
            ->pluck('email');
    }
}
