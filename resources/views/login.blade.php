<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-slate-50 to-sky-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xl">💬</div>
                    <div>
                        <div class="text-slate-900 font-bold text-lg">Connexion au chat</div>
                        <div class="text-slate-500 text-sm">Utilise ton compte Google</div>
                    </div>
                </div>

                <div class="mt-4 p-4 bg-indigo-50 border border-indigo-100 rounded-2xl text-sm text-indigo-900">
                    <div class="font-semibold mb-1">Pourquoi Google ?</div>
                    <div>On utilise ton profil (nom/email) pour envoyer les messages.</div>
                </div>

                <div class="mt-6">
                    <a
                        href="{{ route('google.redirect') }}"
                        class="w-full flex items-center justify-center gap-3 px-4 py-3 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M23.49 12.27c0-.87-.07-1.71-.2-2.53H12v4.78h6.56a5.65 5.65 0 0 1-2.45 3.71v3.07h3.96c2.31-2.13 3.42-5.27 3.42-9.01Z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.93l-3.96-3.07c-1.1.74-2.5 1.17-3.97 1.17-3.05 0-5.64-2.06-6.56-4.83H1.36v3.16A12 12 0 0 0 12 24Z"/>
                            <path fill="#FBBC05" d="M5.44 14.34a8.05 8.05 0 0 1 0-4.68V6.5H1.36a12 12 0 0 0 0 11.01l4.08-3.17Z"/>
                            <path fill="#EA4335" d="M12 4.77c1.76 0 3.34.6 4.58 1.78l3.43-3.43C17.95 1.08 15.24 0 12 0 7.61 0 3.8 2.46 1.36 6.5l4.08 3.16C6.36 6.84 8.95 4.77 12 4.77Z"/>
                        </svg>
                        <span class="font-semibold text-slate-900">Continuer avec Google</span>
                    </a>
                </div>

                <div class="mt-6 text-center text-xs text-slate-500">
                    Besoin d’aide ? Configure `GOOGLE_CLIENT_ID` et `GOOGLE_CLIENT_SECRET` dans ton `.env`.
                </div>
            </div>
        </div>
    </div>
</body>
</html>

