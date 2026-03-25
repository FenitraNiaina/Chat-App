<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', auth()->id())
            ->orderBy('name')
            ->limit(30)
            ->get();

        $users = $this->attachSidebarMeta($users);

        return view('chat', compact('users'));
    }

    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->where('id', '!=', auth()->id())
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'email', 'avatar']);

        $users = $this->attachSidebarMeta($users);

        return response()->json($users);
    }

    private function attachSidebarMeta(Collection $users): Collection
    {
        $authId = (int) auth()->id();

        return $users->map(function ($user) use ($authId) {
            $lastMessage = Message::where(function ($q) use ($user, $authId) {
                $q->where('sender_id', $authId)
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($q) use ($user, $authId) {
                $q->where('sender_id', $user->id)
                    ->where('receiver_id', $authId);
            })->latest('created_at')->first();

            $user->avatar_url = $this->resolveAvatarUrl($user);
            $user->last_message = $lastMessage?->message ?: 'Aucun message pour le moment';

            return $user;
        })->values();
    }

    private function resolveAvatarUrl(User $user): string
    {
        if (!empty($user->avatar)) {
            return (string) $user->avatar;
        }

        $name = trim((string) ($user->name ?? 'Utilisateur'));

        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=4f46e5&color=ffffff&size=80';
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
            'message' => ['nullable', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'max:10240'], // <= 10MB
        ]);

        if ((int) $validated['receiver_id'] === (int) auth()->id()) {
            abort(422, 'Vous ne pouvez pas envoyer un message à vous-même.');
        }

        $imagePath = null;
        if ($request->hasFile('file')) {
            $imagePath = $request->file('file')->store('chat-images', 'public');
        }

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'] ?? null,
            'image' => $imagePath,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function messages($id)
    {
        $validatedId = (int) $id;

        $messages = Message::where(function ($q) use ($validatedId) {
            $q->where('sender_id', auth()->id())
                ->where('receiver_id', $validatedId);
        })->orWhere(function ($q) use ($validatedId) {
            $q->where('sender_id', $validatedId)
                ->where('receiver_id', auth()->id());
        })->orderBy('created_at', 'asc')->get();

        return response()->json($messages);
    }
}
