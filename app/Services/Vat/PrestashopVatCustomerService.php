<?php

namespace App\Services\Vat;

use Illuminate\Support\Facades\DB;

class PrestashopVatCustomerService
{

    public function applyProfessionalGroup(int $idCustomer): void{
        $this->moveCustomerToProfessionalGroup($idCustomer);
    }
        
    public function moveCustomerToProfessionalGroup(int $idCustomer): void{
        $professionalGroupId = (int) env('VAT_VALIDATION_PROFESSIONAL_GROUP_ID', 4);
        $normalGroupId = (int) env('VAT_VALIDATION_NORMAL_GROUP_ID', 3);
        $prefix = env('VAT_VALIDATION_PS_PREFIX', 'ps_');
    
        if ($professionalGroupId <= 0) {
            throw new \RuntimeException('VAT_VALIDATION_PROFESSIONAL_GROUP_ID is not configured.');
        }
    
        DB::connection('mysql2')->transaction(function () use ($idCustomer, $professionalGroupId, $normalGroupId, $prefix) {
            DB::connection('mysql2')
                ->table($prefix . 'customer')
                ->where('id_customer', $idCustomer)
                ->update([
                    'id_default_group' => $professionalGroupId,
                    'date_upd' => now(),
                ]);
    
            DB::connection('mysql2')
                ->table($prefix . 'customer_group')
                ->where('id_customer', $idCustomer)
                ->where('id_group', $normalGroupId)
                ->delete();
    
            $exists = DB::connection('mysql2')
                ->table($prefix . 'customer_group')
                ->where('id_customer', $idCustomer)
                ->where('id_group', $professionalGroupId)
                ->exists();
    
            if (!$exists) {
                DB::connection('mysql2')
                    ->table($prefix . 'customer_group')
                    ->insert([
                        'id_customer' => $idCustomer,
                        'id_group' => $professionalGroupId,
                    ]);
            }
        });
    }

}