<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\HomeHeroSlide;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeHeroSlideController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return HomeHeroSlide::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return HomeHeroSlide::query()->with(['desktopMedia', 'mobileMedia'])->orderBy('sort_order');
    }

    protected function storeRules(Request $request): array
    {
        return $this->rules();
    }

    protected function updateRules(Request $request, \Illuminate\Database\Eloquent\Model $model): array
    {
        return $this->rules();
    }

    protected function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'media_type' => ['required', Rule::in(['image', 'video', 'youtube'])],
            'desktop_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'mobile_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable'],
            'autoplay_override_seconds' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
