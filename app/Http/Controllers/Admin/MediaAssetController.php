<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesAdminCrud;
use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Support\CmsSecurity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaAssetController extends Controller
{
    use HandlesAdminCrud;

    protected function modelClass(): string
    {
        return MediaAsset::class;
    }

    protected function indexQuery(Request $request): Builder
    {
        return MediaAsset::query()
            ->when($request->filled('type'), fn (Builder $query) => $query->where('type', $request->string('type')))
            ->orderByDesc('id');
    }

    protected function storeRules(Request $request): array
    {
        $allowedExtensions = implode(',', CmsSecurity::mediaLibraryExtensions());
        $allowedMimeTypes = implode(',', CmsSecurity::mediaLibraryMimeTypes());
        $maxUploadKilobytes = $this->resolveUploadMaxKilobytes($request);

        return [
            'type' => ['required_without:file', 'string', 'max:20', 'in:image,video,file'],
            'file' => ['nullable', 'file', 'mimes:'.$allowedExtensions, 'mimetypes:'.$allowedMimeTypes, 'max:'.$maxUploadKilobytes],
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'disk' => ['nullable', 'string', 'in:public'],
            'path' => ['nullable', 'string', 'max:255'],
            'meta_json' => ['nullable', 'array'],
        ];
    }

    protected function resolveUploadMaxKilobytes(Request $request): int
    {
        $imageMaxKb = (int) config('security.uploads.page_media.image_max_kb', 20480);
        $videoMaxKb = (int) config('security.uploads.page_media.video_max_kb', 102400);
        $type = (string) $request->input('type', '');

        if ($request->hasFile('file')) {
            $mimeType = (string) ($request->file('file')?->getMimeType() ?? '');

            if (str_starts_with($mimeType, 'video/')) {
                return $videoMaxKb;
            }

            if (str_starts_with($mimeType, 'image/')) {
                return $imageMaxKb;
            }
        }

        return match ($type) {
            'image' => $imageMaxKb,
            'video' => $videoMaxKb,
            default => max($imageMaxKb, $videoMaxKb),
        };
    }

    protected function updateRules(Request $request, \Illuminate\Database\Eloquent\Model $model): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'meta_json' => ['nullable', 'array'],
        ];
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->storeRules($request));
        $disk = $validated['disk'] ?? 'public';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads/'.now()->format('Y/m'), $disk);
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';

            $asset = MediaAsset::create([
                'type' => CmsSecurity::mediaAssetTypeForMime($mimeType),
                'disk' => $disk,
                'path' => $path,
                'title' => cms_plain_text($validated['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                'alt_text' => cms_plain_text($validated['alt_text'] ?? null),
                'mime_type' => $mimeType,
                'extension' => $file->getClientOriginalExtension(),
                'size_bytes' => $file->getSize(),
                'meta_json' => cms_sanitize_array($validated['meta_json'] ?? null),
            ]);
        } else {
            $asset = MediaAsset::create([
                'type' => cms_plain_text($validated['type']) ?? 'image',
                'disk' => $disk,
                'path' => $validated['path'],
                'title' => cms_plain_text($validated['title'] ?? null),
                'alt_text' => cms_plain_text($validated['alt_text'] ?? null),
                'meta_json' => cms_sanitize_array($validated['meta_json'] ?? null),
            ]);
        }

        return response()->json($asset, 201);
    }

    public function destroy(int|string $id): JsonResponse
    {
        $asset = MediaAsset::query()->findOrFail($id);

        if ($asset->path && Storage::disk($asset->disk)->exists($asset->path)) {
            Storage::disk($asset->disk)->delete($asset->path);
        }

        $asset->delete();

        return response()->json(['deleted' => true]);
    }
}
