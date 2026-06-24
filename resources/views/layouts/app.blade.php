<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Younic Home') - Hostel Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        window.AppConfig = {
            userId: {{ auth()->check() ? auth()->id() : 'null' }},
            isAdmin: {{ auth()->check() && auth()->user()->isAdmin() ? 'true' : 'false' }},
            socketHost: '{{ env("VITE_SOCKET_IO_HOST", "http://localhost") }}',
            socketPort: '{{ env("VITE_SOCKET_IO_PORT", 6001) }}'
        };
    </script>
</head>
<body class="flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 glass-card border-l-0 border-y-0 rounded-none border-r border-slate-700/50 flex flex-col fixed inset-y-0 left-0 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="h-16 flex items-center px-6 border-b border-slate-700/50">
            <h1 class="text-xl font-bold text-teal-400 tracking-wider">Younic <span class="text-amber-500">Home</span></h1>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('admin.users') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Users
                </a>
                <a href="{{ route('admin.rooms') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('admin.rooms') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Branches & Rooms
                </a>
                <a href="{{ route('admin.requests') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('admin.requests') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    All Requests
                </a>
                <a href="{{ route('admin.announcements') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('admin.announcements') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                    Announcements
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Profile
                </a>
                <a href="{{ route('rent') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('rent') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Rent & Payments
                </a>
                <a href="{{ route('seat-change') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('seat-change') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    Seat Change
                </a>
                <a href="{{ route('leave') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('leave') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Leave Application
                </a>
                <a href="{{ route('exit') }}" class="flex items-center px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-teal-400 transition {{ request()->routeIs('exit') ? 'bg-slate-800 text-teal-400 border-l-2 border-teal-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Exit Request
                </a>
            @endif
        </nav>
        
        <div class="p-4 border-t border-slate-700/50">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 text-red-400 hover:bg-red-500/10 rounded-lg transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:ml-64 h-screen">
        
        <!-- Header -->
        <header class="h-16 glass-panel border-x-0 border-t-0 rounded-none border-b border-slate-700/50 flex items-center justify-between px-4 sm:px-6 z-40">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="md:hidden text-slate-400 hover:text-white mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h2 class="text-lg font-semibold truncate hidden sm:block">@yield('header_title', 'Dashboard')</h2>
            </div>
            
            <div class="flex items-center space-x-4">
                
                <!-- Notification Bell -->
                <div class="relative">
                    @php
                        $unreadCount = \App\Models\Notification::forUser(auth()->id())->unread()->count();
                        $recentNotifs = \App\Models\Notification::forUser(auth()->id())->latest()->take(5)->get();
                    @endphp
                    <button id="notification-btn" class="relative p-2 text-slate-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span id="notif-badge" class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 bg-red-500 text-[10px] font-bold text-white rounded-full {{ $unreadCount > 0 ? '' : 'hidden' }}">
                            {{ $unreadCount }}
                        </span>
                    </button>
                    
                    <!-- Notification Dropdown -->
                    <div id="notification-dropdown" class="absolute right-0 mt-2 w-80 glass-card rounded-xl shadow-2xl hidden z-50">
                        <div class="p-3 border-b border-slate-700 flex justify-between items-center">
                            <h3 class="font-semibold text-sm">Notifications</h3>
                            <form action="{{ route('notifications.read.all') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs text-teal-400 hover:text-teal-300">Mark all read</button>
                            </form>
                        </div>
                        <div id="notif-list" class="max-h-80 overflow-y-auto">
                            @forelse($recentNotifs as $notif)
                                <div class="p-3 hover:bg-slate-700/50 transition border-b border-slate-700 last:border-0 {{ $notif->is_read ? 'opacity-70' : '' }}">
                                    <p class="text-sm font-medium text-slate-200">{{ $notif->title }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ $notif->message }}</p>
                                </div>
                            @empty
                                <div id="notif-empty" class="p-4 text-center text-slate-500 text-sm">No new notifications</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="flex items-center space-x-3 border-l border-slate-700/50 pl-4">
                    <div class="hidden md:block text-right">
                        <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Resident' }}</p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-teal-500/20 text-teal-400 border border-teal-500/50 flex items-center justify-center font-bold text-sm">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-auto p-4 sm:p-6 lg:p-8">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/50 text-red-400 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/50 text-red-400 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Toast Container for JS Notifications -->
    <div id="toast-container" class="fixed top-20 right-4 z-50 flex flex-col space-y-3 pointer-events-none *:pointer-events-auto"></div>

</body>
</html>
