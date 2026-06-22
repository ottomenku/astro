<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteVisitor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminVisitorController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $visitors = SiteVisitor::query()
            ->with('user')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('ip_address', 'like', "%{$q}%")
                        ->orWhere('user_name', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('last_seen_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.visitors.index', [
            'visitors' => $visitors,
            'q' => $q,
        ]);
    }

    public function ban(SiteVisitor $visitor): RedirectResponse
    {
        $visitor->update(['is_banned' => true]);

        return back()->with('status', "IP tiltva: {$visitor->ip_address}");
    }

    public function unban(SiteVisitor $visitor): RedirectResponse
    {
        $visitor->update(['is_banned' => false]);

        return back()->with('status', "IP feloldva: {$visitor->ip_address}");
    }
}
