<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - @yield('title')</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 py-4 px-6 flex flex-col">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">Employee Portal</h1>
            </div>
            <nav class="flex-1">
                <ul>
                    <li class="mb-4">
                        <a href="{{ route('dashboard') }}" class="flex items-center py-2 px-4 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            <i class="fas fa-home mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="mb-4">
                        <a href="{{ route('leaves.index') }}" class="flex items-center py-2 px-4 rounded-lg {{ request()->routeIs('leaves.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            <i class="fas fa-calendar-alt mr-3"></i>
                            <span>Leave Requests</span>
                        </a>
                    </li>
                    <li class="mb-4">
                        <a href="{{ route('reimbursements.index') }}" class="flex items-center py-2 px-4 rounded-lg {{ request()->routeIs('reimbursements.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            <i class="fas fa-receipt mr-3"></i>
                            <span>Reimbursements</span>
                        </a>
                    </li>
                    <li class="mb-4">
                        <a href="{{ route('overtimes.index') }}" class="flex items-center py-2 px-4 rounded-lg {{ request()->routeIs('overtimes.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            <i class="fas fa-clock mr-3"></i>
                            <span>Overtime</span>
                        </a>
                    </li>
                    <li class="mb-4">
                        <a href="{{ route('official-travels.index') }}" class="flex items-center py-2 px-4 rounded-lg {{ request()->routeIs('official-travels.*') ? 'bg-gray-700' : 'hover:bg-gray-700' }}">
                            <i class="fas fa-plane mr-3"></i>
                            <span>Official Travel</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="mt-auto pt-4 border-t border-gray-700">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-gray-500 rounded-full mr-3"></div>
                    <div>
                        <p class="font-medium">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-400">{{ ucfirst(Auth::user()->role) }}</p>
                    </div>
                </div>
                <a href="{{ route('logout') }}" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                   class="mt-4 block py-2 px-4 rounded-lg hover:bg-gray-700">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-3">
                    <div class="flex items-center">
                        <button id="sidebar-toggle" class="mr-4 text-gray-600 lg:hidden">
                            <i class="fas fa-bars"></i>
                        </button>
                        <h2 class="text-xl font-semibold">@yield('header')</h2>
                    </div>
                    <div class="flex items-center">
                        <div class="relative">
                            <button class="flex items-center text-gray-600 focus:outline-none">
                                <i class="fas fa-bell"></i>
                                <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-100 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('hidden');
        });
    </script>
</body>
</html>