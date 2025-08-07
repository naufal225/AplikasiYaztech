<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 z-50 bg-primary-800 text-white w-64 flex flex-col shadow-medium lg:relative lg:translate-x-0 transform -translate-x-full transition-transform duration-300 ease-in-out z-30"
    id="sidebar">
    <div class="bg-primary-900 px-6 py-4 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-bold text-white">YAZTECH ENGINEERING</h1>
            <p class="text-primary-200 text-xs">{{ Auth::user()->email }}</p>
        </div>
        <button class="lg:hidden text-white hover:text-primary-200" onclick="toggleSidebar()">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="fas fa-home w-5 text-center mr-3"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <a href="{{ route('admin.employee.index') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.employee.index') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="fas fa-calendar-alt w-5 text-center mr-3"></i>
            <span class="font-medium">Employee</span>
        </a>

    </nav>

    <div class="border-t border-primary-700 p-4">
        <div class="flex items-center mb-4">
            <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center mr-3">
                <span class="text-white font-semibold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-medium text-sm truncate">{{ Auth::user()->name }}</p>
                <p class="text-primary-200 text-xs">{{ ucfirst(Auth::user()->role) }}</p>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="w-full flex items-center px-4 py-2 rounded-lg text-primary-100 hover:bg-primary-700 hover:text-white transition-all duration-200">
                <i class="fas fa-sign-out-alt w-5 text-center mr-3"></i>
                <span class="font-medium">Logout</span>
            </button>
        </form>
    </div>
</div>
