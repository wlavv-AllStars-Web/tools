<?php

namespace App\Services\Finance;

use Illuminate\Support\Facades\DB;

final class CorrectedInventoryService
{
    public static function build(string $prefix): array
    {
        $rows = DB::connection('mysql2')->select("SELECT x.reference,SUM(x.quantity) quantity,MAX(x.supplier_price) wholesale_price,MAX(x.currency) currency,MAX(x.conversion_rate) conversion_rate FROM (SELECT p.reference,sa.quantity,COALESCE(NULLIF(cp.wholesale_price_base_currency,0),p.wholesale_price,0) supplier_price,COALESCE(NULLIF(UPPER(m.currency),''),'EUR') currency,CASE WHEN COALESCE(NULLIF(cp.wholesale_price_base_currency,0),0)>0 THEN COALESCE(cur.conversion_rate,1) ELSE 1 END conversion_rate FROM {$prefix}stock_available sa JOIN {$prefix}product p ON p.id_product=sa.id_product JOIN {$prefix}product_shop ps ON ps.id_product=p.id_product AND ps.id_shop=2 JOIN {$prefix}manufacturer m ON m.id_manufacturer=p.id_manufacturer LEFT JOIN {$prefix}custom_product cp ON cp.id_product=p.id_product LEFT JOIN {$prefix}currency cur ON UPPER(cur.iso_code)=CASE WHEN UPPER(m.currency)='YEN' THEN 'JPY' ELSE UPPER(m.currency) END WHERE sa.id_shop=2 AND sa.id_product_attribute=0 AND sa.quantity>0 AND p.reference<>'' AND NOT EXISTS(SELECT 1 FROM {$prefix}product_attribute pa0 WHERE pa0.id_product=p.id_product) AND NOT EXISTS(SELECT 1 FROM {$prefix}pack pk0 WHERE pk0.id_product_pack=p.id_product) UNION ALL SELECT pa.reference,sa.quantity,COALESCE(NULLIF(cpa.wholesale_price_base_currency,0),NULLIF(cp.wholesale_price_base_currency,0),pa.wholesale_price,p.wholesale_price,0),COALESCE(NULLIF(UPPER(m.currency),''),'EUR'),CASE WHEN COALESCE(NULLIF(cpa.wholesale_price_base_currency,0),NULLIF(cp.wholesale_price_base_currency,0),0)>0 THEN COALESCE(cur.conversion_rate,1) ELSE 1 END FROM {$prefix}stock_available sa JOIN {$prefix}product_attribute pa ON pa.id_product_attribute=sa.id_product_attribute JOIN {$prefix}product p ON p.id_product=sa.id_product JOIN {$prefix}product_shop ps ON ps.id_product=p.id_product AND ps.id_shop=2 JOIN {$prefix}manufacturer m ON m.id_manufacturer=p.id_manufacturer LEFT JOIN {$prefix}custom_product cp ON cp.id_product=p.id_product LEFT JOIN {$prefix}custom_product_attribute cpa ON cpa.id_product_attribute=pa.id_product_attribute LEFT JOIN {$prefix}currency cur ON UPPER(cur.iso_code)=CASE WHEN UPPER(m.currency)='YEN' THEN 'JPY' ELSE UPPER(m.currency) END WHERE sa.id_shop=2 AND sa.id_product_attribute>0 AND sa.quantity>0 AND pa.reference<>'' AND NOT EXISTS(SELECT 1 FROM {$prefix}pack pk1 WHERE pk1.id_product_pack=p.id_product)) x GROUP BY x.reference ORDER BY x.reference");
        $data=[];
        foreach($rows as $row){$price=(float)$row->wholesale_price;$rate=(float)$row->conversion_rate;if($rate<=0)$rate=1.0;$eur=$price*$rate;$qty=(int)$row->quantity;$data[(string)$row->reference]=(object)['reference'=>(string)$row->reference,'quantity'=>$qty,'wholesale_price'=>$price,'currency'=>(string)$row->currency,'eur_convertion'=>$eur,'total_row'=>$qty*$eur];}
        return $data;
    }
}
