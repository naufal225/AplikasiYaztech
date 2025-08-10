<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Employee Portal')</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
</head>
<body class="bg-neutral-50 font-sans antialiased h-screen overflow-hidden">
    <div class="h-full flex">
        <!-- Sidebar -->
        <div class="bg-primary-800 fixed inset-y-0 left-0 z-50 text-white w-64 flex flex-col shadow-medium lg:relative lg:translate-x-0 transform -translate-x-full transition-transform duration-300 ease-in-out" id="sidebar">
            <div class="bg-primary-900 px-6 py-4 flex items-center justify-between">
                <div class="w-full">
                    <img src="{{ asset('yaztech-logo-web.png') }}" alt="Yaztech Logo" class="h-12 w-auto mx-auto">
                </div>
                <button class="lg:hidden text-white hover:text-primary-200" onclick="toggleSidebar()">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="{{ route('employee.dashboard') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('employee.dashboard') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
                    <i class="fas fa-home w-5 text-center mr-3"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('employee.leaves.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('employee.leaves.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
                    <i class="fas fa-calendar-alt w-5 text-center mr-3"></i>
                    <span class="font-medium">Pengajuan Cuti</span>
                </a>

                <a href="{{ route('employee.reimbursements.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('employee.reimbursements.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
                    <i class="fas fa-receipt w-5 text-center mr-3"></i>
                    <span class="font-medium">Reimbursement</span>
                </a>

                <a href="{{ route('employee.overtimes.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('employee.overtimes.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
                    <i class="fas fa-clock w-5 text-center mr-3"></i>
                    <span class="font-medium">Overtime</span>
                </a>

                <a href="{{ route('employee.official-travels.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('employee.official-travels.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
                    <i class="fas fa-plane w-5 text-center mr-3"></i>
                    <span class="font-medium">Perjalanan Dinas</span>
                </a>

                @if(Auth::user()->role === App\Roles::Approver->value || Auth::user()->role === App\Roles::Admin->value)
                    <div class="border-t border-primary-700 my-4 pt-4">
                        <p class="text-primary-300 text-xs font-semibold uppercase tracking-wider px-4 mb-2">Approver</p>
                        <a href="#" class="flex items-center px-4 py-3 rounded-lg text-primary-100 hover:bg-primary-700 hover:text-white transition-all duration-200">
                            <i class="fas fa-user-check w-5 text-center mr-3"></i>
                            <span class="font-medium">Approver</span>   
                        </a>
                    </div>
                @endif
            </nav>

            <div class="border-t border-primary-700 p-4">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-white font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white font-medium text-sm truncate">{{ Auth::user()->name }}</p>
                        <p class="text-primary-200 text-xs">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2 rounded-lg text-primary-100 hover:bg-primary-700 hover:text-white transition-all duration-200">
                        <i class="fas fa-sign-out-alt w-5 text-center mr-3"></i>
                        <span class="font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Overlay for mobile -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden hidden" id="sidebar-overlay" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="bg-secondary-500 shadow-soft">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button class="lg:hidden text-white hover:text-secondary-100 mr-4" onclick="toggleSidebar()">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <div>
                            <h2 class="text-xl font-bold text-white">@yield('header', 'Dashboard')</h2>
                            <p class="text-secondary-100 text-sm">@yield('subtitle', 'Welcome back!')</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center bg-secondary-600 rounded-full px-3 py-2">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center lg:mr-2">
                                <span class="text-secondary-600 font-semibold text-sm">{{ strtoupper(substr(trim(explode(' ', Auth::user()->name)[0]), 0, 1)) }}</span>
                            </div>
                            <span class="text-white font-medium text-sm hidden lg:block">{{ Auth::user()->name }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6 overflow-y-auto">
                @if(session('success'))
                    <div class="mb-6 bg-success-50 border border-success-200 text-success-800 px-4 py-3 rounded-lg shadow-soft">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-error-50 border border-error-200 text-error-800 px-4 py-3 rounded-lg shadow-soft">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.querySelector('[onclick="toggleSidebar()"]');
            
            if (window.innerWidth < 1024 && 
                !sidebar.contains(event.target) && 
                !sidebarToggle.contains(event.target) &&
                !sidebar.classList.contains('-translate-x-full')) {
                toggleSidebar();
            }
        });

        @stack('scripts')
    </script>
</body>
</html>