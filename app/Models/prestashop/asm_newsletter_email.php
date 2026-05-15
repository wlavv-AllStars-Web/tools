<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class asm_newsletter_email extends PrestashopModel{
    
    use HasFactory;

    protected $fillable = [
        'id_lang',
        'id_product',
        'email',
        'subject',
        'html',
        'attempts',
        'sent'
    ];

    public function __construct(array $attributes = []){
        parent::__construct($attributes);
        $this->table = self::tableName('asm_newsletter_email');
    }

    public static function insertRow($id_lang, $id_product, $email, $subject, $html, $attempts = 0, $sent = 0){
        $row = self::create([
            'id_lang' => $id_lang,
            'id_product' => $id_product,
            'email' => $email,
            'subject' => $subject,
            'html' => $html,
            'attempts' => $attempts,
            'sent' => $sent
        ]);

        return $row;
    }
}