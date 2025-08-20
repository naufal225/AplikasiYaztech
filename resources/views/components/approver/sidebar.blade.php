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
        <a href="{{ route('approver.dashboard') }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('approver.dashboard') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">
            <i class="w-5 mr-3 text-center fas fa-tachometer-alt"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        @php
        $divisionId = Auth::user()->division_id;
        $unseenLeaveCount = 0;
        $unseenOfficialTravelCount = 0;
        $unseenOvertimeCount = 0;
        $unseenReimbursementCount = 0;
        $unseenLeaveCount = \App\Models\Leave::whereNull('seen_by_approver_at')
        ->where('status_1','pending')
        ->whereHas('employee', fn($q)=>$q->where('division_id', $divisionId))
        ->count();
        $unseenOfficialTravelCount = \App\Models\OfficialTravel::whereNull('seen_by_approver_at')
        ->where('status_1','pending')
        ->whereHas('employee', fn($q)=>$q->where('division_id', $divisionId))
        ->count();
        $unseenOvertimeCount = \App\Models\Overtime::whereNull('seen_by_approver_at')
        ->where('status_1','pending')
        ->whereHas('employee', fn($q)=>$q->where('division_id', $divisionId))
        ->count();
        $unseenReimbursementCount = \App\Models\Reimbursement::whereNull('seen_by_approver_at')
        ->where('status_1','pending')
        ->whereHas('employee', fn($q)=>$q->where('division_id', $divisionId))
        ->count();

        @endphp

        <a href="{{ route('approver.leaves.index') }}" id="leave-nav" data-role="{{ Auth::user()->role }}"
            data-division-id="{{ Auth::user()->division_id }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('approver.leaves.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">

            <i class="w-5 mr-3 text-center fas fa-plane-departure"></i>
            <span class="font-medium">Leave Requests</span>

            <span id="leave-badge"
                class="ml-auto inline-flex items-center justify-center rounded-full bg-red-600 text-white text-xs font-bold px-1 py-0.5 min-w-[1.25rem]"
                style="{{ $unseenLeaveCount > 0 ? '' : 'display: none' }}">
                {{ $unseenLeaveCount }}
            </span>
        </a>

        <a href="{{ route('approver.reimbursements.index') }}" id="reimbursement-nav" data-role="{{ Auth::user()->role }}"
            data-division-id="{{ Auth::user()->division_id }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('approver.reimbursements.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">

            <i class="w-5 mr-3 text-center fas fa-plane-departure"></i>
            <span class="font-medium">Reimbursement Requests</span>

            <span id="reimbursement-badge"
                class="ml-auto inline-flex items-center justify-center rounded-full bg-red-600 text-white text-xs font-bold px-1 py-0.5 min-w-[1.25rem]"
                style="{{ $unseenReimbursementCount > 0 ? '' : 'display: none' }}">
                {{ $unseenReimbursementCount }}
            </span>
        </a>

        <a href="{{ route('approver.overtimes.index') }}" id="overtime-nav" data-role="{{ Auth::user()->role }}"
            data-division-id="{{ Auth::user()->division_id }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('approver.overtimes.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">

            <i class="w-5 mr-3 text-center fas fa-plane-departure"></i>
            <span class="font-medium">Overtime Requests</span>

            <span id="overtime-badge"
                class="ml-auto inline-flex items-center justify-center rounded-full bg-red-600 text-white text-xs font-bold px-1 py-0.5 min-w-[1.25rem]"
                style="{{ $unseenOvertimeCount > 0 ? '' : 'display: none' }}">
                {{ $unseenOvertimeCount }}
            </span>
        </a>

        <a href="{{ route('approver.official-travels.index') }}" id="official-travel-nav" data-role="{{ Auth::user()->role }}"
            data-division-id="{{ Auth::user()->division_id }}"
            class="flex items-center px-4 py-3 rounded-lg transition-all duration-200 {{ request()->routeIs('approver.official-travels.*') ? 'bg-primary-700 text-white shadow-soft' : 'text-primary-100 hover:bg-primary-700 hover:text-white' }}">

            <i class="w-5 mr-3 text-center fas fa-plane-departure"></i>
            <span class="font-medium">Official Travel Requests</span>

            <span id="official-travel-badge"
                class="ml-auto inline-flex items-center justify-center rounded-full {{ $unseenOfficialTravelCount > 0 ? '' : 'opacity-0' }} bg-red-600 text-white text-xs font-bold px-1 py-0.5 min-w-[1.25rem]"
                style="{{ $unseenOfficialTravelCount > 0 ? '' : 'display: none' }}">
                {{ $unseenOfficialTravelCount }}
            </span>
        </a>

    </nav>

    <div class="p-4 border-t border-primary-700">
        <div class="flex items-center mb-4">
            <div class="flex items-center justify-center w-10 h-10 mr-3 rounded-full bg-primary-600">
                <span class="text-sm font-semibold text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-primary-200">{{ Auth::user()->email }}</p>
            </div>
        </div>
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
