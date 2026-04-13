<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use App\Support\SeoPageResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SeoController extends Controller
{
    public function index(): Response
    {
        $metas = SeoMeta::query()->get()->keyBy('page');

        $pages = collect(SeoPageResolver::pages())
            ->map(function (array $pageConfig, string $pageKey) use ($metas): array {
                $meta = $metas->get($pageKey);

                return [
                    'key' => $pageKey,
                    'label' => $pageConfig['label'],
                    'title' => $meta?->title,
                    'description' => $meta?->description,
                    'keywords' => $meta?->keywords,
                    'og_image_url' => $meta?->og_image_url,
                    'recommended_title' => $pageConfig['recommended_title'],
                    'recommended_description' => $pageConfig['recommended_description'],
                    'recommended_keywords' => $pageConfig['recommended_keywords'],
                ];
            })
            ->values();

        return Inertia::render('Admin/Seo/Index', [
            'pages' => $pages,
        ]);
    }

    public function upsert(Request $request): RedirectResponse
    {
        $allowedPages = array_keys(SeoPageResolver::pages());

        $data = $request->validate([
            'page' => ['required', 'string', 'max:100', 'in:'.implode(',', $allowedPages)],
            'title' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:500'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_og_image' => ['nullable', 'boolean'],
        ]);

        $meta = SeoMeta::query()->firstOrNew([
            'page' => $data['page'],
        ]);

        if ($request->boolean('remove_og_image') && $meta->og_image) {
            Storage::disk('public')->delete($meta->og_image);
            $meta->og_image = null;
        }

        if ($request->hasFile('og_image')) {
            if ($meta->og_image) {
                Storage::disk('public')->delete($meta->og_image);
            }

            $meta->og_image = $request->file('og_image')->store('seo', 'public');
        }

        $meta->title = $this->normalizeMetaValue($data['title'] ?? null);
        $meta->description = $this->normalizeMetaValue($data['description'] ?? null);
        $meta->keywords = $this->normalizeMetaValue($data['keywords'] ?? null);

        if (! $meta->title && ! $meta->description && ! $meta->keywords && ! $meta->og_image) {
            if ($meta->exists) {
                $meta->delete();
            }

            cms_security_log('notice', 'SEO metadata removed.', [
                'seo_page' => $data['page'],
            ], $request);

            return back()->with('success', 'SEO eliminado para esta sección.');
        }

        $meta->save();

        cms_security_log('notice', 'SEO metadata updated.', [
            'seo_page' => $meta->page,
            'has_og_image' => (bool) $meta->og_image,
        ], $request);

        return back()->with('success', 'SEO guardado correctamente.');
    }

    private function normalizeMetaValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = cms_plain_text($value);

        return $value === '' ? null : $value;
    }
}
