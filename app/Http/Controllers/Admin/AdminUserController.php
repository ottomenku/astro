<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('email', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'tier' => ['required', 'string', Rule::in(['base', 'premium', 'pro'])],
            'is_admin' => ['sometimes', 'boolean'],
            'token_quota_total' => ['required', 'integer', 'min:0'],
            'token_quota_used' => ['required', 'integer', 'min:0'],
        ]);

        $user->fill([
            'tier' => $validated['tier'],
            'is_admin' => (bool) ($validated['is_admin'] ?? false),
            'token_quota_total' => (int) $validated['token_quota_total'],
            'token_quota_used' => (int) $validated['token_quota_used'],
        ]);
        $user->save();

        return redirect()->route('admin.users.edit', $user)->with('status', 'Felhasználó mentve.');
    }
}
