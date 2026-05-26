<?php

namespace App\Models\modules\marketing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterProductDecision extends Model
{
    use HasFactory;

    protected $table = 'newsletter_product_decisions';

    protected $fillable = [
        'id_product',
        'reference',
        'brand',
        'decision',
        'operator',
    ];

    protected $casts = [
        'id_product' => 'integer',
        'operator' => 'integer',
    ];

    public static function decide(int $idProduct, ?string $reference, ?string $brand, string $decision): self
    {
        return self::updateOrCreate(
            ['id_product' => $idProduct],
            [
                'reference' => $reference,
                'brand' => $brand,
                'decision' => $decision,
                'operator' => auth()->id() ?: 0,
            ]
        );
    }
}
