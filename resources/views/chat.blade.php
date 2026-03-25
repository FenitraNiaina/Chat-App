<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messagerie</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://unpkg.com/laravel-echo/dist/echo.iife.js"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 via-slate-100 to-indigo-50 flex items-center justify-center p-4">
<div class="w-full max-w-6xl bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-200">
    <div class="flex h-[80vh]">
        <!-- SIDEBAR -->
        <aside class="w-80 bg-slate-50 border-r border-slate-200 flex flex-col">
            <div class="p-4 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg">💬</div>
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900">Conversations</div>
                        <div class="text-xs text-slate-500">Temps réel avec Pusher</div>
                    </div>
                </div>
            </div>

            <div class="p-4 border-b border-slate-200">
                <input type="text" id="user-search" placeholder="Rechercher un utilisateur..."
                    class="w-full bg-white border border-slate-200 rounded-2xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div id="users" class="flex-1 overflow-y-auto px-2 py-2">
                @if(isset($users) && $users->isNotEmpty())
                    @foreach($users as $u)
                        <div class="receiver-item p-3 hover:bg-white cursor-pointer rounded-2xl border border-transparent hover:border-slate-200 transition flex items-center gap-3 mb-2"
                            data-user-id="{{ $u->id }}"
                            data-user-name="{{ $u->name ?? $u->email ?? 'Utilisateur' }}"
                            data-user-avatar="{{ $u->avatar_url }}"
                            onclick="selectReceiverFromElement(this)">
                            <img src="{{ $u->avatar_url }}" alt="avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="font-medium text-slate-900 truncate">{{ $u->name ?? 'Utilisateur' }}</div>
                                    <div class="text-xs text-slate-500">●</div>
                                </div>
                                <div class="text-xs text-slate-500 truncate">{{ $u->last_message }}</div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-4 text-center text-sm text-slate-500 bg-white rounded-2xl border border-slate-200">
                        Aucun utilisateur inscrit pour le moment.
                    </div>
                @endif
            </div>

            <div class="p-3 border-t border-slate-200 text-xs text-slate-500 flex items-center gap-2">
                <span id="conn-status-dot" class="w-2 h-2 rounded-full bg-amber-400"></span>
                <span id="conn-status-text">Connexion...</span>
            </div>
            <div class="p-3 border-t border-slate-200 bg-white">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ auth()->user()->avatar ?: ('https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name ?? 'Utilisateur') . '&background=4f46e5&color=ffffff&size=80') }}"
                        alt="Mon profil"
                        class="w-10 h-10 rounded-full object-cover border border-slate-200"
                    >
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold text-slate-900 truncate">{{ auth()->user()->name ?? 'Utilisateur' }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- CHAT AREA -->
        <main class="flex-1 flex flex-col bg-gradient-to-b from-white to-slate-50">
            <!-- HEADER -->
            <header class="p-4 border-b border-slate-200 flex items-center justify-between bg-white/70 backdrop-blur">
                <div class="flex items-center gap-3">
                    <img id="active-partner-avatar" src="https://www.gravatar.com/avatar/?d=mp&s=80" alt="Avatar" class="w-10 h-10 rounded-2xl object-cover border border-slate-200">
                    <div>
                        <div id="active-partner-name" class="font-semibold text-slate-900">Discussion</div>
                        <div class="text-xs text-slate-500 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            En ligne
                        </div>
                    </div>
                </div>
                <div class="text-xs text-slate-500">Canal: <span class="font-medium" id="channel-name">...</span></div>
            </header>

            <!-- MESSAGES -->
            <div id="chat-box" class="flex-1 overflow-y-auto p-6 space-y-4"></div>

            <!-- FORM -->
            <form id="form" class="p-4 border-t border-slate-200 bg-white/80 backdrop-blur flex items-end gap-2">
                <input type="file" name="file" id="file" class="hidden">
                <input type="hidden" name="receiver_id" id="receiver_id" value="">

                <button type="button" onclick="document.getElementById('file').click()"
                    class="p-3 rounded-2xl bg-slate-50 border border-slate-200 hover:bg-slate-100 transition text-lg">
                    📎
                </button>

                <input type="text" name="message" id="message"
                    placeholder="Écrire un message..."
                    class="flex-1 bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">

                <button type="submit"
                    class="px-5 py-3 rounded-2xl bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-60">
                    Envoyer
                </button>
            </form>
        </main>
    </div>
</div>

<script>
const chatBox = document.getElementById("chat-box");
const form = document.getElementById("form");
const messageInput = document.getElementById("message");
const receiverIdInput = document.getElementById("receiver_id");
const userSearchInput = document.getElementById("user-search");
const usersContainer = document.getElementById("users");
const activePartnerNameEl = document.getElementById("active-partner-name");
const activePartnerAvatarEl = document.getElementById("active-partner-avatar");
const channelNameEl = document.getElementById("channel-name");

const authUserId = {{ auth()->id() }};
let activeReceiverId = null;

function renderUsers(users) {
    usersContainer.innerHTML = "";

    if (!users || users.length === 0) {
        usersContainer.innerHTML = `
            <div class="p-3 text-sm text-slate-500 text-center">
                Aucun utilisateur trouvé.
            </div>
        `;
        return;
    }

    users.forEach((u) => {
        const name = u.name ?? "Utilisateur";
        const email = u.email ?? "";
        const avatarUrl = u.avatar_url ?? "https://www.gravatar.com/avatar/?d=mp&s=80";
        const lastMessage = u.last_message ?? "Aucun message pour le moment";

        usersContainer.innerHTML += `
            <div class="receiver-item p-3 hover:bg-white cursor-pointer rounded-2xl border border-transparent hover:border-slate-200 transition flex items-center gap-3 mb-2"
                data-user-id="${u.id}"
                data-user-name="${escapeHtml(name)}"
                data-user-avatar="${avatarUrl}"
                onclick="selectReceiverFromElement(this)">
                <img src="${avatarUrl}" alt="avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-3">
                        <div class="font-medium text-slate-900 truncate">${escapeHtml(name)}</div>
                        <div class="text-xs text-slate-500">●</div>
                    </div>
                    <div class="text-xs text-slate-500 truncate">${escapeHtml(lastMessage || email)}</div>
                </div>
            </div>
        `;
    });
}

async function searchUsers(query = "") {
    try {
        const res = await fetch(`/users/search?q=${encodeURIComponent(query)}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        });
        const users = await res.json();
        renderUsers(users);
    } catch (err) {
        console.error("searchUsers failed", err);
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.innerText = text;
    return div.innerHTML;
}

function formatTime(date) {
    return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function appendMessage(message, isMine = false, createdAt = null) {
    const safeMessage = escapeHtml(message ?? "").replace(/\n/g, "<br/>");
    const time = createdAt ? formatTime(new Date(createdAt)) : formatTime(new Date());

    const wrapperClass = isMine ? "flex justify-end" : "flex justify-start";
    const bubbleClass = isMine
        ? "bg-indigo-600 text-white p-3 rounded-2xl  shadow-sm break-words whitespace-pre-wrap overflow-hidden"
        : "bg-slate-100 text-slate-900 p-3 rounded-2xl  border border-slate-200 break-words whitespace-pre-wrap overflow-hidden";
    const timeClass = isMine ? "text-indigo-200" : "text-slate-500";

    chatBox.innerHTML += `
        <div class="${wrapperClass}">
            <div>
                <div class="${bubbleClass}">${safeMessage}</div>
                <div class="mt-1 text-[11px] ${timeClass}">${time}</div>
            </div>
        </div>
    `;

    chatBox.scrollTop = chatBox.scrollHeight;
}

function setActiveReceiverUI(userEl) {
    const items = document.querySelectorAll(".receiver-item");
    items.forEach((el) => {
        el.classList.remove("border-indigo-400", "bg-indigo-50", "border-slate-200");
        el.classList.add("border-transparent");
    });

    userEl.classList.add("border-indigo-400", "bg-indigo-50");
    userEl.classList.remove("border-transparent");
}

window.selectReceiverFromElement = function (el) {
    const userId = parseInt(el.dataset.userId, 10);
    const userName = el.dataset.userName ?? "Utilisateur";
    const userAvatar = el.dataset.userAvatar ?? "https://www.gravatar.com/avatar/?d=mp&s=80";

    activeReceiverId = userId;
    receiverIdInput.value = String(userId);

    setActiveReceiverUI(el);

    activePartnerNameEl.textContent = userName;
    activePartnerAvatarEl.src = userAvatar;

    loadMessages(userId);
};

async function loadMessages(receiverId) {
    try {
        chatBox.innerHTML = "";
        const res = await fetch(`/messages/${receiverId}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        });
        const messages = await res.json();

        messages.forEach((m) => {
            const text = m.message ?? "";
            const isMine = parseInt(m.sender_id, 10) === parseInt(authUserId, 10);
            appendMessage(text, isMine, m.created_at);
        });
    } catch (err) {
        console.error("loadMessages failed", err);
    }
}

form.addEventListener("submit", async function (e) {
    e.preventDefault();

    const msg = messageInput.value.trim();
    if (!msg) return;
    if (!activeReceiverId) {
        alert("Choisis une conversation d’abord.");
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const formData = new FormData(this);

    try {
        submitBtn && (submitBtn.disabled = true);

        const res = await fetch('/send', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const saved = await res.json();
        appendMessage(saved.message ?? msg, true, saved.created_at ?? new Date().toISOString());
        messageInput.value = "";
    } finally {
        submitBtn && (submitBtn.disabled = false);
    }
});

window.Pusher = Pusher;
try {
    const statusDot = document.getElementById("conn-status-dot");
    const statusText = document.getElementById("conn-status-text");
    const setConnStatus = (connected) => {
        if (!statusDot || !statusText) return;
        statusDot.className = "w-2 h-2 rounded-full " + (connected ? "bg-emerald-500" : "bg-amber-400");
        statusText.textContent = connected ? "Connecté" : "Connexion...";
    };

    setConnStatus(false);

    const EchoConstructor = window.Echo && window.Echo.default ? window.Echo.default : window.Echo;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    window.Echo = new EchoConstructor({
        broadcaster: "pusher",
        key: "{{ env('PUSHER_APP_KEY') }}",
        cluster: "{{ env('PUSHER_APP_CLUSTER') }}",
        forceTLS: true,
        authEndpoint: "/broadcasting/auth",
        auth: csrfToken
            ? {
                headers: {
                    "X-CSRF-TOKEN": csrfToken
                }
            }
            : undefined
    });

    if (channelNameEl) {
        channelNameEl.textContent = `chat.${authUserId}`;
    }

    if (window.Echo?.connector?.pusher?.connection) {
        window.Echo.connector.pusher.connection.bind("connected", () => setConnStatus(true));
        window.Echo.connector.pusher.connection.bind("error", () => setConnStatus(false));
    } else {
        setConnStatus(true);
    }

    window.Echo.private(`chat.${authUserId}`)
        .listen(".MessageSent", (e) => {
            const m = e.message;
            if (!m || !m.message) return;

            // Sur le canal de l'utilisateur connecté (receiver_id = authUserId),
            // on affiche seulement si le message vient du partenaire actif.
            const senderId = parseInt(m.sender_id, 10);
            if (activeReceiverId && senderId === parseInt(activeReceiverId, 10)) {
                appendMessage(m.message, false, m.created_at);
            }
        });
} catch (err) {
    console.error("Echo init failed", err);
}

// Sélectionne automatiquement la première conversation
const firstReceiverEl = document.querySelector(".receiver-item");
if (firstReceiverEl) {
    window.selectReceiverFromElement(firstReceiverEl);
}

let searchDebounce = null;
userSearchInput?.addEventListener("input", (e) => {
    const value = e.target.value ?? "";
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(() => {
        searchUsers(value);
    }, 250);
});
</script>

</body>
</html>