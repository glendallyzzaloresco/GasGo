<?php

namespace App\Http\Controllers;

use App\Models\SiteTheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SiteThemeController extends Controller
{
    public function index()
    {
        $theme = SiteTheme::singleton();

        return response()->json([
            'data' => [
                'primaryColor' => $theme->primaryColor,
                'accentColor' => $theme->accentColor,
                'backgroundColor' => $theme->backgroundColor,
                'sidebarBackground' => $theme->sidebarBackground,
                'logoUrl' => $theme->logoUrl,
                'footerDescription' => $theme->footerDescription,
                'contactAddress' => $theme->contactAddress,
                'contactPhone' => $theme->contactPhone,
            ],
        ]);
    }

    public function update(Request $request)
    {
        if (! Auth::check() || Auth::user()?->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'primaryColor' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'accentColor' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'backgroundColor' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'sidebarBackground' => ['nullable', 'string', 'regex:/^#([0-9a-fA-F]{6})$/'],
            'footerDescription' => ['nullable', 'string', 'max:1000'],
            'contactAddress' => ['nullable', 'string', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:80'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $theme = SiteTheme::singleton();

        $payload = [
            'primaryColor' => $request->input('primaryColor', $theme->primaryColor),
            'accentColor' => $request->input('accentColor', $theme->accentColor),
            'backgroundColor' => $request->input('backgroundColor', $theme->backgroundColor),
            'sidebarBackground' => $request->input('sidebarBackground', $theme->sidebarBackground),
            'footerDescription' => $request->input('footerDescription', $theme->footerDescription),
            'contactAddress' => $request->input('contactAddress', $theme->contactAddress),
            'contactPhone' => $request->input('contactPhone', $theme->contactPhone),
        ];

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('theme', 'public');
            $payload['logoUrl'] = asset('storage/' . ltrim($logoPath, '/'));
        }

        $theme->fill($payload);
        $theme->save();

        return response()->json([
            'message' => 'Theme updated successfully.',
            'data' => $payload,
        ]);
    }
}
