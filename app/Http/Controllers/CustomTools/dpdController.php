<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\Controller;

use App\Models\prestashop\orders;

class dpdController extends Controller
{
    public function __construct(){

    }

    public function generateCSV($id_order, $weight){
        
        header('Content-Type: text/html; charset=UTF-8');
        
        $order = orders::with('customer', 'delivery.country', 'invoice')->where('id_order', $id_order)->first();
        $email = $order->customer->email;
        $id_country = $order->delivery->id_country;
        $iso_code = $order->delivery->country->iso_code;

    	$file = (int)$id_order.'guiaDPD.csv';

        $path = public_path('uploads/logistics/DPD/');
        
        switch ($order->id_shop){
            case 1:  { $store = 'EURO MUSCLE PARTS'; break; }
            case 2:  { $store = 'ALL STARS MOTORSPORT'; break; }
            case 3:  { $store = 'ALL STARS DISTRIBUTION'; break; }
            case 4:  { $store = 'EURO RIDER'; break; }
            default: $store = 'ALL STARS MOTORSPORT';
        }
        
        switch ($id_country){
            case 6:  { $dpdpais = '02380801'; break; }
            case 15: { $dpdpais = '02380805'; break; }
            case 19: { $dpdpais = '02380808'; break; }
            default: $dpdpais = '02380803';
        }

        if ($csv = fopen($path.$file, 'w')) {

            $data[] = $dpdpais;
            $data[] = $order->customer->id_customer;
            $data[] = strip_tags(iconv('UTF-8', 'ISO-8859-1', $order->delivery->firstname . ' ' . $order->delivery->lastname));
            $data[] = strip_tags(iconv('UTF-8', 'ISO-8859-1', $order->delivery->address1 . ' ' . $order->delivery->address2));
            $data[] = $order->delivery->postcode;
            $data[] = strip_tags(iconv('UTF-8', 'ISO-8859-1', $order->delivery->city));
            $data[] = $iso_code;
            $data[] = $order->delivery->phone;
            $data[] = $order->delivery->phone_mobile;
            $data[] = $email;
            $data[] = '';
            $data[] = $weight;
            $data[] = 0;
            $data[] = 0;
            $data[] = $order->reference;
            $data[] = $order->id_order;
            
            if( ($order->recyclable == 1 ) ){

                $data[] = strip_tags(iconv('UTF-8', 'ISO-8859-1', $order->invoice->firstname . ' ' . $order->invoice->lastname));
                $data[] = "ZONA INDUSTRIAL DE GANDRA,LOTE 6";
                $data[] = "4930-311";
                $data[] = "VALENCA";
            
            }else{

                $data[] = $store;
                $data[] = "ZONA INDUSTRIAL DE GANDRA,LOTE 6";
                $data[] = "4930-311";
                $data[] = "VALENCA";

            }
            
            fputs($csv, implode(';',$data));
					
        }
        
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
        ];

        return Response::download($path.$file, $file, $headers);
    }
}