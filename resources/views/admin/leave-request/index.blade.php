@extends('components.admin.layout.layout-admin')
@section('header', 'Manage Leaves')
@section('subtitle', 'Manage Leaves data')

@section('content')
<main class="relative z-10 flex-1 p-0 space-y-6 overflow-x-hidden overflow-y-auto bg-gray-50">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Leave Requests</h1>
            <p class="text-neutral-600">Manage and track your leave requests</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <div class="flex flex-col items-center gap-5 mt-4 sm:mt-0 sm:flex-row">
                <div class="mt-4 sm:mt-0">
                    <button onclick="window.location.href='{{ route('admin.leaves.create') }}'"
                        class="btn-primary @if($sisaCuti <= 0) cursor-not-allowed @else cursor-pointer @endif"
                        @if($sisaCuti <=0) disabled @endif>
                        <i class="mr-2 fas fa-plus"></i>
                        New Leave Request
                    </button>
                </div>
                <button id="exportLeaveRequests"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white transition-all duration-200 transform rounded-lg shadow-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 hover:scale-105">
                    <i class="mr-2 fa-solid fa-file-export"></i>
                    <span id="exportButtonText">Export Data</span>
                    <svg id="exportSpinner" class="hidden w-4 h-4 ml-2 -mr-1 text-white animate-spin" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div class="">
        <!-- Success Message -->
        @if(session('success'))
        <div class="flex items-center p-4 my-6 border border-green-200 bg-green-50 rounded-xl">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
        <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary-100 text-primary-500">
                    <i class="text-xl fas fa-calendar-alt"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Total Requests</p>
                    <p class="text-lg font-semibold">{{ $totalRequests }}</p>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-warning-100 text-warning-500">
                    <i class="text-xl fas fa-clock"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Pending</p>
                    <p class="text-lg font-semibold">{{ $pendingRequests }}</p>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-success-100 text-success-500">
                    <i class="text-xl fas fa-check-circle"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Approved</p>
                    <p class="text-lg font-semibold">{{ $approvedRequests }}</p>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-error-100 text-error-500">
                    <i class="text-xl fas fa-times-circle"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Rejected</p>
                    <p class="text-lg font-semibold">{{ $rejectedRequests }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
        <form id="filterForm" method="GET" action="{{ route('admin.leaves.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="block mb-2 text-sm font-medium text-neutral-700">Status</label>
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status')==='approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status')==='rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <label class="block mb-2 text-sm font-medium text-neutral-700">From Date</label>
                <input type="date" name="from_date" id="fromDateFilter" value="{{ request('from_date') }}"
                    class="form-input">
            </div>
            <div>
                <label class="block mb-2 text-sm font-medium text-neutral-700">To Date</label>
                <input type="date" name="to_date" id="toDateFilter" value="{{ request('to_date') }}" class="form-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="mr-2 btn-primary">
                    <i class="mr-2 fas fa-search"></i>
                    Filter
                </button>
                <a href="{{ route('admin.leaves.index') }}" class="btn-secondary">
                    <i class="mr-2 fas fa-refresh"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- First Table: My Leave Requests -->
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">My Leave Requests</h3>
                <span class="px-3 py-1 text-sm font-medium text-blue-800 bg-blue-100 rounded-full">
                    {{ $ownRequests->total() }} requests
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200">
                <thead class="bg-neutral-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Request ID</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Duration</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Status</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Manager</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-neutral-200">
                    @forelse($ownRequests as $leave)
                    <tr class="transition-colors duration-200 hover:bg-neutral-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-neutral-900">#{{ $leave->id }}</div>
                                <div class="text-sm text-neutral-500">{{ $leave->created_at->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-neutral-900">
                                {{ \Carbon\Carbon::parse($leave->date_start)->format('M d') }} - {{
                                \Carbon\Carbon::parse($leave->date_end)->format('M d, Y') }}
                            </div>
                            <div class="text-sm text-neutral-500">
                                {{
                                \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end))
                                + 1 }} days
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($leave->status_1 === 'pending')
                            <span class="text-yellow-500 badge-pending">
                                <i class="mr-1 fas fa-clock"></i>
                                Pending
                            </span>
                            @elseif($leave->status_1 === 'approved')
                            <span class="text-green-500 badge-approved">
                                <i class="mr-1 fas fa-check-circle"></i>
                                Approved
                            </span>
                            @elseif($leave->status_1 === 'rejected')
                            <span class="text-red-500 badge-rejected">
                                <i class="mr-1 fas fa-times-circle"></i>
                                Rejected
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-neutral-900">{{ $manager->name ?? "N/A" }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.leaves.show', $leave->id) }}"
                                    class="text-primary-600 hover:text-primary-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-neutral-400">
                                <i class="mb-4 text-4xl fas fa-inbox"></i>
                                <p class="text-lg font-medium">No personal leave requests found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($ownRequests->hasPages())
        <div class="px-6 py-4 border-t border-neutral-200">
            {{ $ownRequests->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <!-- Second Table: All Users' Leave Requests -->
    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">All Leave Requests</h3>
                <span class="px-3 py-1 text-sm font-medium text-green-800 bg-green-100 rounded-full">
                    {{ $allUsersRequests->total() }} requests
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200">
                <thead class="bg-neutral-50">
                    <tr>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Request ID</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Employee</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Duration</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Status</th>
                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-neutral-200">
                    @forelse($allUsersRequests as $leave)
                    <tr class="transition-colors duration-200 hover:bg-neutral-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-neutral-900">#{{ $leave->id }}</div>
                                <div class="text-sm text-neutral-500">{{ $leave->created_at->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10">
                                    @if($leave->employee->url_profile)
                                    <img class="object-cover w-10 h-10 rounded-full"
                                        src="{{ $leave->employee->url_profile }}" alt="{{ $leave->employee->name }}">
                                    @else
                                    <div class="flex items-center justify-center w-10 h-10 bg-gray-300 rounded-full">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ strtoupper(substr($leave->employee->name, 0, 1)) }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-neutral-900">{{ $leave->employee->name }}</div>
                                    <div class="text-sm text-neutral-500">
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                            @if($leave->employee->role === 'Manager') bg-purple-100 text-purple-800
                                            @elseif($leave->employee->role === 'Employee') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($leave->employee->role) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-neutral-900">
                                {{ \Carbon\Carbon::parse($leave->date_start)->format('M d') }} - {{
                                \Carbon\Carbon::parse($leave->date_end)->format('M d, Y') }}
                            </div>
                            <div class="text-sm text-neutral-500">
                                {{
                                \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end))
                                + 1 }} days
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($leave->status_1 === 'pending')
                            <span class="text-yellow-500 badge-pending">
                                <i class="mr-1 fas fa-clock"></i>
                                Pending
                            </span>
                            @elseif($leave->status_1 === 'approved')
                            <span class="text-green-500 badge-approved">
                                <i class="mr-1 fas fa-check-circle"></i>
                                Approved
                            </span>
                            @elseif($leave->status_1 === 'rejected')
                            <span class="text-red-500 badge-rejected">
                                <i class="mr-1 fas fa-times-circle"></i>
                                Rejected
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.leaves.show', $leave->id) }}"
                                    class="text-primary-600 hover:text-primary-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="text-neutral-400">
                                <i class="mb-4 text-4xl fas fa-inbox"></i>
                                <p class="text-lg font-medium">No leave requests found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($allUsersRequests->hasPages())
        <div class="px-6 py-4 border-t border-neutral-200">
            {{ $allUsersRequests->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <div id="toast" class="fixed z-50 hidden top-4 right-4">
        <div id="toastContent" class="px-6 py-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <span id="toastMessage"></span>
                <button onclick="hideToast()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastContent = document.getElementById('toastContent');
    const toastMessage = document.getElementById('toastMessage');

    toastMessage.textContent = message;

    if (type === 'success') {
        toastContent.className = 'px-6 py-4 rounded-lg shadow-lg bg-green-500 text-white';
    } else {
        toastContent.className = 'px-6 py-4 rounded-lg shadow-lg bg-red-500 text-white';
    }

    toast.classList.remove('hidden');

    setTimeout(() => {
        hideToast();
    }, 5000);
}

function hideToast() {
    document.getElementById('toast').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const exportButton = document.getElementById('exportLeaveRequests');
    const exportButtonText = document.getElementById('exportButtonText');
    const exportSpinner = document.getElementById('exportSpinner');

    exportButton.addEventListener('click', async function() {
        // Show loading state
        exportButtonText.textContent = 'Exporting...';
        exportSpinner.classList.remove('hidden');
        exportButton.disabled = true;

        try {
            // Get current filter values
            const status = document.getElementById('statusFilter').value;
            const fromDate = document.getElementById('fromDateFilter').value;
            const toDate = document.getElementById('toDateFilter').value;

            // Build export URL with filters
            const params = new URLSearchParams();
            if (status) params.append('status', status);
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);

            const exportUrl = `{{ route('admin.leaves.export') }}?${params.toString()}`;

            // Use fetch to get the file
            const response = await fetch(exportUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Export failed');
            }

            // Get the blob from response
            const blob = await response.blob();

            // Create download link
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `leave-requests-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.xlsx`;

            // Trigger download
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            // Clean up
            window.URL.revokeObjectURL(url);

            showToast('Export completed successfully!', 'success');

        } catch (error) {
            console.error('Export error:', error);
            showToast('Export failed: ' + error.message, 'error');
        } finally {
            // Reset button state
            exportButtonText.textContent = 'Export Data';
            exportSpinner.classList.add('hidden');
            exportButton.disabled = false;
        }
    });
});
</script>
@endpush