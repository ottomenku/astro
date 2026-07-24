<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AstrologyEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAstrologyEntryController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'all');
        $locale = (string) $request->query('locale', 'all');

        $entries = AstrologyEntry::query()
            ->with(['createdBy', 'firstClickedBy'])
            ->when($type !== 'all', fn ($query) => $query->where('type', $type))
            ->when($locale !== 'all', fn ($query) => $query->where('locale', $locale))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('key', 'like', "%{$q}%")
                        ->orWhere('title', 'like', "%{$q}%")
                        ->orWhere('question', 'like', "%{$q}%")
                        ->orWhere('answer', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('last_clicked_at')
            ->orderByDesc('click_count')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.astrology-entries.index', [
            'entries' => $entries,
            'q' => $q,
            'type' => $type,
            'locale' => $locale,
        ]);
    }

    public function show(AstrologyEntry $astrologyEntry): View
    {
        $astrologyEntry->load(['createdBy', 'firstClickedBy']);

        return view('admin.astrology-entries.show', [
            'entry' => $astrologyEntry,
        ]);
    }
}
