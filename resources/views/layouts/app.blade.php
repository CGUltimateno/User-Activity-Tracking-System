<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900" data-auth="{{ auth()->check() ? '1' : '0' }}" data-role="{{ auth()->user()?->role ?? '' }}">
    <div class="min-h-screen">
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    {{-- Back button: always show a back control; JS will try a same-origin referrer or history.back() and fall back to the homepage when necessary. --}}
                    <button
                        onclick="(function(){
                            try {
                                var currPath = location.pathname.toLowerCase();
                            } catch(e) { var currPath = ''; }

                            // If we are on any users page (index, show, create, edit), prefer the users index as the 'back' target
                            try {
                                if (currPath.indexOf('/users') === 0) {
                                    // Prefer a stored users index (so back returns to the list the admin used), otherwise fallback to route
                                    var usersIndex = null;
                                    try { usersIndex = sessionStorage.getItem('usersIndex'); } catch(e) { usersIndex = null; }
                                    if (usersIndex) { window.location = usersIndex; return; }
                                    window.location = '{{ route('users.index') }}';
                                    return;
                                }
                            } catch(e) {
                                // if route helper fails for some reason, fall through
                            }

                            try {
                                var ref = document.referrer ? new URL(document.referrer) : null;
                                var refPath = ref ? ref.pathname : '';
                                var sameOriginRef = ref ? (ref.origin === location.origin) : false;
                            } catch(e) {
                                var ref = null; var refPath = ''; var sameOriginRef = false;
                            }

                            // treat any path containing these tokens as an auth-related page we shouldn't go back to
                            var badTokens = ['login','register','logout','password','verify'];
                            var refIsBad = false;
                            if (refPath) {
                                var low = refPath.toLowerCase();
                                refIsBad = badTokens.some(function(t){ return low.indexOf(t) !== -1; });
                            }

                            // If there's a same-origin referrer and it doesn't look like an auth page, go there explicitly
                            if (sameOriginRef && ref && !refIsBad) {
                                window.location.href = ref.href;
                                return;
                            }

                            // Otherwise pick a sensible landing page based on authentication and role
                            var body = document.body || {};
                            var isAuth = body.getAttribute && body.getAttribute('data-auth') === '1';
                            var role = body.getAttribute ? body.getAttribute('data-role') : '';
                            if (isAuth) {
                                if (role === 'admin') { window.location = '{{ route('admin.dashboard') }}'; return; }
                                window.location = '{{ route('users.index') }}'; return;
                            }

                            // fallback for guests
                            window.location = '{{ url('/') }}';
                        })();"
                        class="inline-block px-3 py-1 text-sm border rounded text-gray-700 hover:bg-gray-100"
                    >&larr; Back</button>

                    {{-- Show quick Users link for authenticated users so admins can reach user management easily --}}
                    @auth
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-700">Users</a>
                    @endauth

                     <a href="/" class="font-bold">{{ config('app.name', 'App') }}</a>
                 </div>
                <div class="flex items-center space-x-4">
                    @auth
                        <span class="text-sm">{{ auth()->user()->name ?? '' }}</span>
                        @if(auth()->user()?->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600">Admin</a>
                        @endif
                        <form id="logout-form" method="POST" action="{{ route('logout') }}">@csrf<button class="text-sm text-red-600">Logout</button></form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm">Login</a>
                        <a href="{{ route('register') }}" class="text-sm">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto p-6">
            @if(session('status'))
                <div class="mb-4 p-3 bg-green-100 text-green-700">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>

    <script>
        // Remember the users index path so Back can reliably return there
        (function(){
            try {
                var p = location.pathname.toLowerCase();
                // treat exact /users (with optional trailing slash and query) as users index
                var usersIndexMatch = /^\/users\/?(\?.*)?$/.test(p + location.search.toLowerCase());
                if (usersIndexMatch) {
                    try { sessionStorage.setItem('usersIndex', location.pathname + location.search); } catch(e) {}
                }
            } catch(e) {}
        })();

        // Inactivity monitoring script
        (function(){
            if (!document) return;
            const IDLE_TIMEOUT = Number({{ \App\Models\Setting::get('idle_timeout_seconds', 5) }} || 5);
            const MONITORING_ENABLED = Number({{ \App\Models\Setting::get('monitoring_enabled', 1) }} || 1);
            if (!MONITORING_ENABLED) return;

            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let lastActivity = Date.now();
            let idleTimer = null;
            let idleCount = 0;
            let idleSessionId = null;
            let alerted = false;

            function sendEvent(type, extra={}){
                const payload = Object.assign({type:type, session_id: idleSessionId}, extra);
                return fetch("/idle/event", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                }).then(r => r.json()).catch(_=>({ok:false}));
            }

            function startIdle() {
                idleCount++;
                if (idleCount === 1) {
                    // start session and first alert
                    sendEvent('start').then(res => { if (res.idle_session_id) idleSessionId = res.idle_session_id; });
                    sendEvent('first_alert');
                    alert('You have been idle. Please interact to avoid logout.');
                    alerted = true;
                } else if (idleCount === 2) {
                    sendEvent('warning');
                    alert('Second inactivity detected. This is a warning.');
                } else if (idleCount >= 3) {
                    sendEvent('penalty', {reason: 'Third consecutive idle'}).then(res => {
                        if (res.logout) {
                            // trigger server logout
                            fetch("/logout", {
                                method: 'POST',
                                headers: {'X-CSRF-TOKEN': csrf}
                            }).then(()=>{
                                window.location = '/';
                            });
                        }
                    });
                }
            }

            function endIdle() {
                if (idleSessionId) {
                    sendEvent('end', {duration: Math.floor((Date.now() - lastActivity)/1000), session_id: idleSessionId});
                }
            }

            function resetTimer() {
                lastActivity = Date.now();
                if (idleTimer) { clearTimeout(idleTimer); }
                // if we were alerted due to idle, then mark session end
                if (alerted) {
                    endIdle();
                    alerted = false;
                }
                // schedule check
                idleTimer = setTimeout(function(){
                    // if no activity since lastActivity for IDLE_TIMEOUT seconds
                    const diff = Date.now() - lastActivity;
                    if (diff >= IDLE_TIMEOUT * 1000) {
                        startIdle();
                    }
                }, IDLE_TIMEOUT * 1000 + 200);
            }

            ['mousemove','keydown','scroll','click','touchstart'].forEach(ev => {
                window.addEventListener(ev, resetTimer, {passive:true});
            });

            resetTimer();

            setInterval(function(){
                if (Date.now() - lastActivity >= IDLE_TIMEOUT*1000) {
                }
            }, 30000);
        })();

        // Prevent native browser back/forward from showing auth pages to already-authenticated users.
        (function(){
            try {
                var role = document.body && (document.body.getAttribute('data-role') || '');

                // centralized redirect decision based on server state
                var goToLanding = function(authenticated, r) {
                    if (!authenticated) return;
                    if (r === 'admin') {
                        window.location.replace('{{ route('admin.dashboard') }}');
                    } else {
                        window.location.replace('{{ route('users.index') }}');
                    }
                };

                var authRedirect = function(){
                    // If we are on an auth-related path, verify server-side whether the user is actually signed in.
                    var path = window.location.pathname.toLowerCase();
                    if (path.indexOf('/login') === -1 && path.indexOf('/register') === -1 && path.indexOf('/password') === -1) {
                        return; // not an auth page
                    }

                    // try to fetch current auth status from server; if it reports authenticated => redirect
                    fetch('{{ route('auth.status') }}', {credentials: 'same-origin', headers: {'Accept': 'application/json'}})
                        .then(function(res){ if (!res.ok) throw new Error('bad'); return res.json(); })
                        .then(function(json){
                            if (json && json.authenticated) {
                                goToLanding(true, json.role || role);
                            }
                        }).catch(function(){
                            // If the fetch fails (offline or network), fall back to client-state check
                            var isAuthClient = document.body && document.body.getAttribute('data-auth') === '1';
                            if (isAuthClient) {
                                goToLanding(true, role);
                            }
                        });
                };

                // pageshow handles bfcache (back-forward cache) and normal loads
                window.addEventListener('pageshow', function(e){
                    var nav = performance.getEntriesByType && performance.getEntriesByType('navigation') && performance.getEntriesByType('navigation')[0];
                    var navType = nav && nav.type ? nav.type : '';

                    // If we arrived here via back/forward and the referrer was a users page, but current is admin, send user to the users list.
                    try {
                        var refUrl = document.referrer ? new URL(document.referrer) : null;
                        var refPath = refUrl ? refUrl.pathname.toLowerCase() : '';
                        var currPath = window.location.pathname.toLowerCase();
                        var cameFromUsers = refPath && refPath.indexOf('/users') === 0;
                        var amOnAdmin = currPath.indexOf('/admin') === 0 || currPath.indexOf('/admin/dashboard') === 0;
                        if ((e.persisted || navType === 'back_forward') && cameFromUsers && amOnAdmin) {
                            // Prefer stored usersIndex when available
                            var usersIndex = null;
                            try { usersIndex = sessionStorage.getItem('usersIndex'); } catch(e) { usersIndex = null; }
                            if (usersIndex) { window.location.replace(usersIndex); return; }
                            window.location.replace('{{ route('users.index') }}');
                            return;
                        }
                    } catch(err) {
                        // ignore
                    }

                    if (e.persisted || navType === 'back_forward') {
                        authRedirect();
                    }
                });

                // popstate handles history navigation
                window.addEventListener('popstate', function(){
                    authRedirect();
                });
            } catch (e) {
                // fail safe - do nothing
            }
        })();
    </script>

    @stack('scripts')
</body>
</html>
