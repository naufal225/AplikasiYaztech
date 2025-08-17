@extends('components.manager.layout.layout-manager')

@section('header', 'Manage Official Travels')
@section('subtitle', 'Manage official travels data')

@section('content')
<main class="relative z-10 flex-1 p-0 space-y-6 overflow-x-hidden overflow-y-auto bg-gray-50">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Official Travel Requests</h1>
            <p class="text-neutral-600">Manage and track official travel requests</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <div class="flex flex-col gap-3 mt-4 sm:mt-0 sm:flex-row">
                <button id="exportOfficialTravelsData"
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
    <!-- Statistics Cards -->
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
        <form id="filterForm" method="GET" action="{{ route('manager.official-travels.index') }}"
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
                <a href="{{ route('manager.official-travels.index') }}" class="btn-secondary">
                    <i class="mr-2 fas fa-refresh"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Official Travel Requests</h3>
            </div>
        </div>
        <div class="overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Request ID</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Employee</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Duration</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Days</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Status 1 - Team Lead</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Status 2 - Manager</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Team Lead</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Manager</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($officialTravels as $officialTravel)
                        <tr class="transition-colors duration-200 hover:bg-neutral-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-neutral-900">#{{ $officialTravel->id }}</div>
                                    <div class="text-sm text-neutral-500">{{ $officialTravel->created_at->format('M d,
                                        Y') }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 mr-3 rounded-full bg-success-100">
                                        <span class="text-xs font-semibold text-success-600">{{
                                            substr($officialTravel->employee->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">{{
                                            $officialTravel->employee->name }}</div>
                                        <div class="text-sm text-neutral-500">{{ $officialTravel->employee->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-neutral-900">
                                    {{ $officialTravel->date_start->format('M d') }}
                                </div>
                                <div class="text-sm text-neutral-500">
                                    to {{ $officialTravel->date_end->format('M d') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-neutral-900">{{ $officialTravel->total }} day{{
                                    $officialTravel->total > 1 ? 's' : '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($officialTravel->status_1 === 'pending')
                                <span class="text-yellow-500 badge-pending">
                                    <i class="mr-1 fas fa-clock"></i>
                                    Pending
                                </span>
                                @elseif($officialTravel->status_1 === 'approved')
                                <span class="text-green-500 badge-approved">
                                    <i class="mr-1 fas fa-check-circle"></i>
                                    Approved
                                </span>
                                @elseif($officialTravel->status_1 === 'rejected')
                                <span class="text-red-500 badge-rejected">
                                    <i class="mr-1 fas fa-times-circle"></i>
                                    Rejected
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($officialTravel->status_2 === 'pending')
                                <span class="text-yellow-500 badge-pending">
                                    <i class="mr-1 fas fa-clock"></i>
                                    Pending
                                </span>
                                @elseif($officialTravel->status_2 === 'approved')
                                <span class="text-green-500 badge-approved">
                                    <i class="mr-1 fas fa-check-circle"></i>
                                    Approved
                                </span>
                                @elseif($officialTravel->status_2 === 'rejected')
                                <span class="text-red-500 badge-rejected">
                                    <i class="mr-1 fas fa-times-circle"></i>
                                    Rejected
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-neutral-900">{{
                                        $officialTravel->approver->name ?? "N/A" }}</div>

                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-neutral-900">{{ $manager->name ?? "N/A" }}
                                    </div>

                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('manager.official-travels.show', $officialTravel->id) }}"
                                        class="text-primary-600 hover:text-primary-900" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-neutral-400">
                                    <i class="mb-4 text-4xl fas fa-plane"></i>
                                    <p class="text-lg font-medium">No official travel requests found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($officialTravels->hasPages())
            <div class="px-6 py-4 border-t border-neutral-200">
                {{ $officialTravels->links() }}
            </div>
            @endif
        </div>
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

@section('partial-modal')

<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>

            <div class="text-center">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">Delete Employee</h3>
                <p class="mb-6 text-sm text-gray-500">
                    Are you sure you want to delete <span id="employeeName" class="font-medium text-gray-900"></span>?
                    This action cannot be undone.
                </p>
            </div>

            <div class="flex justify-center space-x-3">
                <button type="button" id="cancelDeleteButton"
                    class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg cancel-delete-btn hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteBtn"
                    class="px-4 py-2 text-sm font-medium text-white transition-colors bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <span id="deleteButtonText">Delete</span>
                    <svg id="deleteSpinner" class="hidden w-4 h-4 ml-2 -mr-1 text-white animate-spin" fill="none"
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
</div>

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
    const exportButton = document.getElementById('exportOfficialTravelsData');
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

            const exportUrl = `{{ route('manager.official-travels.export') }}?${params.toString()}`;

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
            link.download = `official-travel-requests-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.xlsx`;

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
