<?php

namespace App\Http\Controllers;

use App\Support\CmsSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ProtectedMediaController extends Controller
{
    public function show(Request $request, string $path)
    {
        abort_if(str_contains($path, '..'), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        $mimeType = $disk->mimeType($path) ?: 'application/octet-stream';
        $filename = basename($path);
        $disposition = CmsSecurity::isSafeInlineMime($mimeType) ? 'inline' : 'attachment';
        $size = null;

        try {
            $size = $disk->size($path);
        } catch (\Throwable) {
            $size = null;
        }

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition.'; filename="' . $filename . '"',
            'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=86400',
            'X-Content-Type-Options' => 'nosniff',
            'X-Download-Options' => 'noopen',
            'Cross-Origin-Resource-Policy' => $disposition === 'inline' ? 'cross-origin' : 'same-origin',
            'Accept-Ranges' => 'bytes',
        ];

        if ($disposition === 'attachment') {
            $headers['Content-Security-Policy'] = "default-src 'none'; sandbox";
        }

        if ($size !== null) {
            $headers['Content-Length'] = (string) $size;
        }

        $absolutePath = null;

        try {
            $absolutePath = $disk->path($path);
        } catch (\Throwable) {
            $absolutePath = null;
        }

        $rangeHeader = (string) $request->header('Range', '');

        if ($size !== null && $absolutePath && is_file($absolutePath) && preg_match('/bytes=(\d*)-(\d*)/i', $rangeHeader, $matches)) {
            $start = $matches[1] === '' ? 0 : (int) $matches[1];
            $end = $matches[2] === '' ? $size - 1 : (int) $matches[2];

            if ($matches[1] === '' && $matches[2] !== '') {
                $suffixLength = (int) $matches[2];
                $start = max(0, $size - $suffixLength);
                $end = $size - 1;
            }

            if ($start > $end || $start >= $size) {
                return response('', 416, [
                    'Content-Range' => 'bytes */'.$size,
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $end = min($end, $size - 1);
            $length = $end - $start + 1;
            $partialHeaders = array_merge($headers, [
                'Content-Length' => (string) $length,
                'Content-Range' => "bytes {$start}-{$end}/{$size}",
            ]);

            if ($request->isMethod('HEAD')) {
                return response('', 206, $partialHeaders);
            }

            return Response::stream(function () use ($absolutePath, $start, $end): void {
                $stream = fopen($absolutePath, 'rb');

                if (! is_resource($stream)) {
                    return;
                }

                fseek($stream, $start);
                $remaining = $end - $start + 1;

                while ($remaining > 0 && ! feof($stream)) {
                    $chunkSize = (int) min(8192, $remaining);
                    $buffer = fread($stream, $chunkSize);

                    if ($buffer === false) {
                        break;
                    }

                    echo $buffer;
                    flush();
                    $remaining -= strlen($buffer);
                }

                fclose($stream);
            }, 206, $partialHeaders);
        }

        if ($request->isMethod('HEAD')) {
            return response('', 200, $headers);
        }

        $stream = $disk->readStream($path);

        return Response::stream(function () use ($stream): void {
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, $headers);
    }
}
