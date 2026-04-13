<?php

namespace App\Models;

use App\Support\SeoPageResolver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'page',
        'title',
        'description',
        'keywords',
        'og_image',
    ];

    public function getOgImageUrlAttribute(): ?string
    {
        return cms_media_url($this->og_image);
    }

    public static function forCurrentRequest(): ?self
    {
        $page = SeoPageResolver::resolvePageKey(request());

        if (! $page) {
            return null;
        }

        return static::query()->where('page', $page)->first();
    }
}
