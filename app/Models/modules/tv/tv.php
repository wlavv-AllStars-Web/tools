<?php

namespace App\Models\modules\tv;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class tv extends Model{     

    use HasFactory;
    protected $table = 'tv';
    protected $fillable = [
        'id_manufacturer',
        'src',
        'active',
        'text',
    ];

    public $timestamps = false;

    public function mediaType(): string
    {
        $src = (string) $this->src;

        if (str_starts_with($src, 'youtube:')) {
            return 'youtube';
        }

        $extension = strtolower(pathinfo(parse_url($src, PHP_URL_PATH) ?? $src, PATHINFO_EXTENSION));

        return in_array($extension, ['mp4', 'webm', 'ogg'], true) ? 'video' : 'image';
    }

    public function youtubeCode(): string
    {
        return str_starts_with((string) $this->src, 'youtube:')
            ? substr((string) $this->src, strlen('youtube:'))
            : '';
    }
}
