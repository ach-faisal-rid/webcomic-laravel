<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Exception;

class GalleryController extends Controller
{
    public function fetch(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'title' => 'nullable|string|max:255',
        ]);

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'webcomic-laravel/1.0',
            ])->get($request->url);
        } catch (Exception $e) {
            Log::error('Failed to fetch remote image', ['url' => $request->url, 'error' => $e->getMessage()]);
            return back()->withErrors(['url' => 'Gagal mengunduh gambar: koneksi gagal']);
        }

        if (! $response->successful()) {
            return back()->withErrors(['url' => 'Gagal mengunduh gambar: HTTP ' . $response->status()]);
        }

        $contentType = $response->header('Content-Type');

        if (! $contentType || ! Str::startsWith($contentType, 'image/')) {
            return back()->withErrors(['url' => 'URL tidak mengarah ke gambar (Content-Type: ' . ($contentType ?? 'unknown') . ')']);
        }

        // Try to determine extension from content type or URL
        $extension = null;
        if (preg_match('#image/(\w+)#', $contentType, $m)) {
            $extension = strtolower($m[1]);
            // normalize jpeg -> jpg
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }
        }

        if (! $extension) {
            $extension = pathinfo(parse_url($request->url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
        }

        $safeTitle = $request->filled('title') ? Str::slug($request->title) : null;
        $filename = 'pinterest_' . time() . '_' . Str::random(6) . ($safeTitle ? '_' . $safeTitle : '') . '.' . $extension;
        $path = 'comics/gallery/' . $filename;

        // store to the public disk
        try {
            Storage::disk('public')->put($path, $response->body());
        } catch (Exception $e) {
            Log::error('Failed to store downloaded image', ['path' => $path, 'error' => $e->getMessage()]);
            return back()->withErrors(['url' => 'Gagal menyimpan gambar ke storage']);
        }

        // simpan metadata ke database
        Gallery::create([
            'title' => $request->title,
            'source_url' => $request->url,
            'image_path' => $path,
        ]);

        return back()->with('success', 'Gambar berhasil diunduh!');
    }
}
