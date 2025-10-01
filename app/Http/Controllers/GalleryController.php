<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class GalleryController extends Controller
{
    /**
     * Tampilkan daftar gallery.
     */
    public function index()
    {
        $userId = Auth::id();
        $galleries = Gallery::with('images')->get()->map(function ($g) use ($userId) {
            $images = $g->images->map(function ($img) {
                return [
                    'id' => $img->id,
                    'src' => $img->image_path ? Storage::url($img->image_path) : null,
                ];
            })->toArray();
            // legacy: jika tidak ada images relasi, ambil dari kolom image_path
            if (empty($images) && $g->image_path) {
                $images[] = [
                    'id' => null,
                    'src' => Storage::url($g->image_path),
                ];
            }
            return [
                'id' => $g->id,
                'title' => $g->title,
                'source_url' => $g->source_url,
                'images' => $images,
                'like_count' => $g->like_count,
                'share_count' => $g->share_count,
                'liked' => $userId ? (bool) $g->likes()->where('user_id', $userId)->exists() : false,
                'created_at' => $g->created_at,
                'updated_at' => $g->updated_at,
            ];
        });
        return Inertia::render('Gallery/Index', [
            'galleries' => $galleries
        ]);
    }

    /**
     * Form tambah gallery.
     */
    public function create()
    {
        return Inertia::render('Gallery/Create');
    }

    /**
     * Simpan data gallery baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'srcs'   => 'required|array|min:1', // array of image URLs
            'srcs.*' => 'required|url',
        ]);

        $gallery = Gallery::create([
            'title' => $validated['title'],
            'source_url' => $validated['srcs'][0], // simpan url pertama sebagai sumber utama
        ]);

        $client = new Client(['timeout' => 10]);

        foreach ($validated['srcs'] as $src) {
            try {
                $res = $client->get($src, ['http_errors' => false]);
                $status = $res->getStatusCode();
                if ($status !== 200) {
                    continue; // skip gagal
                }

                $contentType = $res->getHeaderLine('Content-Type');
                if (!str_starts_with($contentType, 'image/')) {
                    continue; // skip non-image
                }

                $body = $res->getBody()->getContents();

                $ext = null;
                if ($contentType && strpos($contentType, '/') !== false) {
                    $ext = explode('/', $contentType)[1];
                    $ext = explode(';', $ext)[0];
                }
                if (!$ext) {
                    $ext = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                }

                $filename = 'galleries/' . Str::slug($validated['title']) . '-' . time() . '-' . uniqid() . '.' . $ext;
                Storage::disk('public')->put($filename, $body);

                $gallery->images()->create([
                    'image_path' => $filename,
                ]);
            } catch (\Exception $e) {
                continue;
            }
        }

        return redirect()->route('gallery.index')
            ->with('success', 'Gallery berhasil ditambahkan.');
    }

    /**
     * Hapus gallery.
     */
    public function destroy(Gallery $gallery)
    {
        $gallery->delete();

        return redirect()->route('gallery.index')
        ->with('success', 'Gallery berhasil dihapus.');
    }

    // Like a gallery
    public function like(Gallery $gallery)
    {
    $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // attach if not already liked
        if (! $gallery->likes()->where('user_id', $user->id)->exists()) {
            $gallery->likes()->attach($user->id);
            $gallery->increment('like_count');
        }

        return response()->json(['like_count' => $gallery->like_count]);
    }

    // Unlike a gallery
    public function unlike(Gallery $gallery)
    {
    $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        if ($gallery->likes()->where('user_id', $user->id)->exists()) {
            $gallery->likes()->detach($user->id);
            $gallery->decrement('like_count');
        }

        return response()->json(['like_count' => $gallery->like_count]);
    }

    // Share counter increment (stateless)
    public function share(Gallery $gallery)
    {
        $gallery->increment('share_count');
        return response()->json(['share_count' => $gallery->share_count]);
    }
}
