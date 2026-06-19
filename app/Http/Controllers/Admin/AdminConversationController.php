<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class AdminConversationController extends Controller
{
    public function index(Request $request)
    {
        $conversations = Conversation::query()
            ->with('user')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.conversations.index', [
            'conversations' => $conversations,
        ]);
    }
}
