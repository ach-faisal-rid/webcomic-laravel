<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comic;

class ComicController extends Controller
{
    public function index()
    {
        $comics = Comic::latest()->get();
        return inertia('Comic/Index', compact('comics'));
    }

    public function create()
    {
        return inertia('Comic/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'pdf' => 'required|mimes:pdf|max:20480', // max 20 MB
        ]);

        $path = $request->file('pdf')->store('comics', 'public');

        Comic::create([
            'title' => $request->title,
            'author' => $request->author,
            'pdf_path' => $path,
        ]);

        return redirect()->route('comics.index')
            ->with('success', 'Comic uploaded successfully!');
    }

    public function show(Comic $comic)
    {
        return inertia('Comics/Show', compact('comic'));
    }
}
