<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditorUploadController extends Controller
{
    /**
     * Handle an in-editor image upload (CKEditor SimpleUploadAdapter) and return its public URL.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'upload' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('upload')->store('uploads', 'public');

        return response()->json(['url' => asset('storage/'.$path)]);
    }
}
