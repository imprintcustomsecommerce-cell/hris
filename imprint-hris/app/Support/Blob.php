<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * File storage that works both locally and on Vercel.
 *
 * Vercel's filesystem is read-only apart from /tmp, which is discarded between
 * requests, so uploads there go to Vercel Blob over its REST API and we keep the
 * returned URL in the same `file_path` / `photo` columns. When BLOB_READ_WRITE_TOKEN
 * is absent (local dev, tests) every method falls through to the normal disk, so
 * nothing changes for developers.
 *
 * Stored values are therefore either a relative disk path ("documents/abc.pdf")
 * or an absolute URL ("https://....public.blob.vercel-storage.com/..."). The
 * helpers below branch on that.
 */
class Blob
{
    protected const ENDPOINT = 'https://blob.vercel-storage.com';

    public static function enabled(): bool
    {
        return (bool) config('services.blob.token');
    }

    public static function isRemote(?string $path): bool
    {
        return is_string($path) && str_starts_with($path, 'http');
    }

    /**
     * Store an upload and return the value to persist.
     *
     * @param  string  $prefix     folder, e.g. "documents"
     * @param  string  $localDisk  disk used when Blob is not configured
     */
    public static function put(UploadedFile $file, string $prefix, string $localDisk = 'local'): string
    {
        if (! self::enabled()) {
            return $file->store($prefix, $localDisk);
        }

        $name = $prefix . '/' . $file->hashName();

        $response = Http::withToken(config('services.blob.token'))
            ->withHeaders([
                'x-api-version' => '7',
                'x-content-type' => $file->getMimeType() ?: 'application/octet-stream',
                'x-add-random-suffix' => '1',
                'x-access' => 'public',
            ])
            ->withBody(file_get_contents($file->getRealPath()), $file->getMimeType() ?: 'application/octet-stream')
            ->put(self::ENDPOINT . '/' . $name);

        $response->throw();

        return $response->json('url');
    }

    public static function delete(?string $path, string $localDisk = 'local'): void
    {
        if (! $path) {
            return;
        }

        if (! self::isRemote($path)) {
            Storage::disk($localDisk)->delete($path);

            return;
        }

        if (self::enabled()) {
            Http::withToken(config('services.blob.token'))
                ->withHeaders(['x-api-version' => '7'])
                ->post(self::ENDPOINT . '/delete', ['urls' => [$path]]);
        }
    }

    public static function exists(?string $path, string $localDisk = 'local'): bool
    {
        if (! $path) {
            return false;
        }

        return self::isRemote($path) || Storage::disk($localDisk)->exists($path);
    }

    /** Public URL for displaying an image. */
    public static function url(?string $path): string
    {
        if (self::isRemote($path)) {
            return $path;
        }

        return asset('storage/' . $path);
    }

    /** Download response — a redirect for Blob-hosted files, a streamed download locally. */
    public static function download(string $path, ?string $name = null, string $localDisk = 'local')
    {
        if (self::isRemote($path)) {
            return redirect()->away($path);
        }

        return Storage::disk($localDisk)->download($path, $name);
    }
}
