<?php

namespace App\Models\modules\compats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class compats_options extends Model
{
    use HasFactory;

    protected $table = 'compats_options';
    protected $primaryKey = 'id_option';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_parent',
        'type',
        'slug',
        'name',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id_option' => 'integer',
        'id_parent' => 'integer',
        'type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'id_parent', 'id_option');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'id_parent', 'id_option')->orderBy('name', 'asc');
    }

    public function brandCompats()
    {
        return $this->hasMany(compats::class, 'id_brand', 'id_option');
    }

    public function modelCompats()
    {
        return $this->hasMany(compats::class, 'id_model', 'id_option');
    }

    public function typeCompats()
    {
        return $this->hasMany(compats::class, 'id_type', 'id_option');
    }

    public function versionCompats()
    {
        return $this->hasMany(compats::class, 'id_version', 'id_option');
    }

    public function scopeOfType($query, int $type)
    {
        return $query->where('type', $type);
    }

    public function scopeChildrenOf($query, int $parentId)
    {
        return $query->where('id_parent', $parentId);
    }

    public static function checkForSlug(string $slug, int $type): array
    {
        $record = self::where('slug', $slug)
            ->where('type', $type)
            ->first();

        return [
            'exist' => $record !== null,
            'data' => $record?->toArray(),
        ];
    }
    
    public static function updateImage($data)
    {
        $from = public_path('uploads/compats/image.png');
        $to = null;
    
        if (!file_exists($from)) {
            return false;
        }
    
        if (($data->element ?? null) === 'logo') {
            $to = public_path('uploads/compats/brand/' . $data->id . '.png');
        }
    
        if (($data->element ?? null) === 'hover') {
            $to = public_path('uploads/compats/brand_hover/' . $data->id . '.png');
        }
    
        if (($data->element ?? null) === 'cartoon') {
            $to = public_path('uploads/compats/compat/' . $data->id . '.png');
        }
    
        if (!$to) {
            return false;
        }
    
        $dir = dirname($to);
    
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    
        if (copy($from, $to)) {
            @unlink($from);
            return true;
        }
    
        return false;
    }

    public static function getByType(int $id_option, int $type)
    {
        return self::where('id_parent', $id_option)
            ->where('type', $type)
            ->orderBy('name', 'asc')
            ->get();
    }

    public static function getOptionsOfByType(int $type)
    {
        return self::where('type', $type)
            ->orderBy('name', 'asc')
            ->get();
    }
}
