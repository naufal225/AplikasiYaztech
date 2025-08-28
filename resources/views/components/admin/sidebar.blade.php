<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 z-30 z-50 flex flex-col w-64 text-white transition-transform duration-300 ease-in-out transform -translate-x-full bg-primary-800 shadow-medium lg:relative lg:translate-x-0"
    id="sidebar">
    <div class="flex items-center justify-between px-6 py-4 bg-primary-900">
        <div class="w-full">
            <img src="{{ asset('yaztech-logo-web.png') }}" alt="Yaztech Logo" class="w-auto h-12 mx-auto">
        </div>
        <button class="text-white lg:hidden hover:text-primary-200" onclick="toggleSidebar()">
            <i class="text-lg fas fa-times"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2">
        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="w-5 mr-3 text-center fas fa-tachometer-alt"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <a href="{{ route('admin.divisions.index') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.divisions.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="w-5 mr-3 text-center fas fa-users"></i>
            <span class="font-medium">Division</span>
        </a>

        <a href="{{ route('admin.users.index') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.users.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="w-5 mr-3 text-center fas fa-users"></i>
            <span class="font-medium">User</span>
        </a>

        <a href="{{ route('admin.leaves.index') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.leaves.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="w-5 mr-3 text-center fas fa-plane-departure"></i>
            <span class="font-medium">Leave Requests</span>
        </a>

        <a href="{{ route('admin.reimbursements.index') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.reimbursements.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="w-5 mr-3 text-center fas fa-file-invoice-dollar"></i>
            <span class="font-medium">Reimbursement Requests</span>
        </a>

        <a href="{{ route('admin.overtimes.index') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.overtimes.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="w-5 mr-3 text-center fas fa-clock"></i>
            <span class="font-medium">Overtime Requests</span>
        </a>

        <a href="{{ route('admin.official-travels.index') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.official-travels.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="w-5 mr-3 text-center fas fa-briefcase"></i>
            <span class="font-medium">Official Travel Requests</span>
        </a>

    </nav>

    <div class="p-4 border-t border-primary-700">
        <a class="flex items-center mb-4" href="{{ route('admin.profile.index') }}">
            <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-full bg-primary-600">
                @if(Auth::user()->url_profile)
                <img class="object-cover w-10 h-10 rounded-full" src="{{ Auth::user()->url_profile }}"
                    alt="{{ Auth::user()->name }}">
                @else
                <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full">
                    <span class="text-sm font-medium text-gray-700">
                        {{ strtoupper(substr($leave->employee->name, 0, 1)) }}
                    </span>
                </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-primary-200">{{ Auth::user()->email }}</p>
            </div>
        </a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
                class="flex items-center w-full px-4 py-2 transition-all duration-200 rounded-lg text-primary-100 hover:bg-primary-700 hover:text-white">
                <i class="w-5 mr-3 text-center fas fa-sign-out-alt"></i>
                <span class="font-medium">Logout</span>
            </button>
        </form>
    </div>
</div>
