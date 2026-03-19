<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\prestashop\suppliers;
use App\Models\prestashop\supplier_lang;

class asm_newsletter_email extends Model
{
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."asm_newsletter_email";
    }

    public static function insertRow($id_lang, $id_product, $email, $subject, $html, $attempts=0, $sent=0){

        asm_newsletter_email::insert(
            [
                'id_lang' => $id_lang,
                'id_product' => $id_product,
                'email' => $email,
                'subject' => $subject,
                'html' => $html,
                'attempts' => $attempts,
                'sent' => $sent
            ]
        );
        
        return 1;
       
    }
}
