<?php

namespace App\Models\modules\marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterEmail extends Model
{
    use HasFactory;

    protected $table = 'newsletter_emails';

    protected $fillable = [
        'id_lang',
        'id_product',
        'email',
        'subject',
        'html',
        'attempts',
        'sent',
        'sent_at',
    ];

    protected $casts = [
        'id_lang' => 'integer',
        'id_product' => 'integer',
        'attempts' => 'integer',
        'sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public static function insertRow($idLang, $idProduct, $email, $subject, $html, $attempts = 0, $sent = 0): self
    {
        return self::create([
            'id_lang' => $idLang,
            'id_product' => $idProduct,
            'email' => $email,
            'subject' => $subject,
            'html' => $html,
            'attempts' => $attempts,
            'sent' => $sent,
            'sent_at' => $sent ? now() : null,
        ]);
    }
}
