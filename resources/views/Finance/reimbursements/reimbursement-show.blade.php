@extends('Finance.layouts.app')

@section('title', 'Reimbursement Requests')
@section('header', 'Reimbursement Requests')
@section('subtitle', 'Manage employee reimbursement claims')

@section('content')
    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">Reimbursement Requests</h1>
                <p class="text-neutral-600">Manage and track employee reimbursement claims</p>
            </div>

            <div class="mt-4 sm:mt-0">
                <button onclick="window.location.href='{{ route('finance.reimbursements.create') }}'" class="btn-primary cursor-pointer">
                    <i class="fas fa-plus mr-2"></i>
                    New Reimbursement Request
                </button>
            </div>
        </div>

        <!-- Statistics Yours Cards -->
        <p class="text-sm text-neutral-500 mb-2 ms-4">Your Requests</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-primary-100 text-primary-500">
                        <i class="fas fa-receipt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Total Requests</p>
                        <p class="text-lg font-semibold">{{ $totalYoursRequests }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-warning-100 text-warning-600">
                        <i class="fas fa-clock text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Pending</p>
                        <p class="text-lg font-semibold">{{ $pendingYoursRequests }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-success-100 text-success-600">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Approved</p>
                        <p class="text-lg font-semibold">{{ $approvedYoursRequests }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-error-100 text-error-600">
                        <i class="fas fa-times-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Rejected</p>
                        <p class="text-lg font-semibold">{{ $rejectedYoursRequests }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics All Employee Cards -->
        <p class="text-sm text-neutral-500 mb-2 ms-4">All Requests</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-primary-100 text-primary-500">
                        <i class="fas fa-receipt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Total All Requests</p>
                        <p class="text-lg font-semibold">{{ $totalRequests }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-success-100 text-success-500">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Total All Approved</p>
                        <p class="text-lg font-semibold">{{ $approvedRequests }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-success-100 text-success-500">
                        <i class="fas fa-list-check text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Total All Marked</p>
                        <p class="text-lg font-semibold">{{ $markedRequests . '/' . $totalAllNoMark }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 p-6">
            <form method="GET" action="{{ route('finance.reimbursements.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status"
                            class="w-full rounded-xl p-2.5 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">All</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- From Date -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">From Date</label>
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full rounded-xl p-2.5 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    <!-- To Date -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">To Date</label>
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full rounded-xl p-2.5 border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row sm:justify-end sm:space-x-3 space-y-3 sm:space-y-0 border-t pt-3 border-gray-300/80">
                    
                    <!-- Filter -->
                    <button type="submit"
                        class="flex justify-center-safe items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-xl shadow-sm 
                            hover:bg-blue-700 hover:shadow-md transition-all duration-300 w-full sm:w-auto">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>

                    <!-- Reset -->
                    <button type="button" onclick="window.location.href = '{{ route('finance.reimbursements.index') }}'"
                        class="flex justify-center-safe items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl shadow-sm 
                            hover:bg-gray-200 hover:shadow-md transition-all duration-300 w-full sm:w-auto">
                        <i class="fas fa-refresh mr-2"></i> Reset
                    </button>

                    <!-- Bulk Request -->
                    <button type="button"
                        onclick="window.location.href='{{ route('finance.reimbursements.bulkExport', [
                            'status' => request('status'),
                            'from_date' => request('from_date'),
                            'to_date' => request('to_date'),
                        ]) }}'"
                        class="flex justify-center-safe items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-xl shadow-sm 
                            hover:bg-green-700 hover:shadow-md transition-all duration-300 w-full sm:w-auto">
                        <i class="fas fa-layer-group mr-2"></i> Bulk Request
                    </button>
                </div>
            </form>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-300/80 transform scale-y-50 mb-10 mt-6"></div>

        <!-- Your reimbursement Employee Table -->
        <p class="text-sm text-neutral-500 mb-2 ms-4">Your reimbursement requests are listed below.</p>
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 1 - Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 2 - Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($yourReimbursements as $reimbursement)
                            <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">#RY{{ $reimbursement->id }}</div>
                                        <div class="text-sm text-neutral-500">{{ $reimbursement->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center mr-3">
                                            @if($reimbursement->employee->url_profile)
                                                <img class="object-cover rounded-full"
                                                    src="{{ $reimbursement->employee->url_profile }}" alt="{{ $reimbursement->employee->name }}">
                                            @else
                                                <span class="text-success-600 font-semibold text-xs">{{ substr($reimbursement->employee->name, 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $reimbursement->employee->name }}</div>
                                            <div class="text-sm text-neutral-500">{{ $reimbursement->employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">Rp {{ number_format($reimbursement->total, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ \Carbon\Carbon::parse($reimbursement->date)->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($reimbursement->status_1 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($reimbursement->status_1 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($reimbursement->status_1 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($reimbursement->status_2 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($reimbursement->status_2 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($reimbursement->status_2 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $reimbursement->approver->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $manager->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $reimbursement->customer ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('finance.reimbursements.show', $reimbursement->id) }}" class="text-primary-600 hover:text-primary-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if((Auth::id() === $reimbursement->employee_id && $reimbursement->status_1 === 'pending' && !\App\Models\Division::where('leader_id', Auth::id())->exists()) || (\App\Models\Division::where('leader_id', Auth::id())->exists() && $reimbursement->status_2 === 'pending'))
                                            <a href="{{ route('finance.reimbursements.edit', $reimbursement->id) }}" class="text-secondary-600 hover:text-secondary-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('finance.reimbursements.destroy', $reimbursement->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error-600 hover:text-error-900" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center">
                                    <div class="text-neutral-400">
                                        <i class="fas fa-receipt text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No reimbursement requests found</p>
                                        <p class="text-sm">Create your first reimbursement request to get started</p>
                                        <a href="{{ route('finance.reimbursements.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>
                                            New Reimbursement Request
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($yourReimbursements->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200">
                    {{ $yourReimbursements->links() }}
                </div>
            @endif
        </div>

        <!-- Reimbursement All Employee Table -->
        <form action="{{ route('finance.reimbursements.marked') }}" method="POST"
            onsubmit="return confirm('Are you sure you want to mark selected reimbursements as done?')">
            @csrf
            @method('PATCH')

            <div class="flex flex-row max-md:flex-col justify-between items-center p-4 mb-2">
                <p class="text-sm text-neutral-500 max-md:mb-6">All employee reimbursement requests are listed below.</p>
                <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 bg-success-600 text-white rounded-lg hover:bg-success-700 disabled:opacity-50"
                        id="bulk-mark-btn"
                        disabled>
                    <i class="fas fa-check mr-1"></i> Mark Selected Done
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <!-- checkbox select all -->
                            <th class="px-4 py-3">
                                <input type="checkbox" id="select-all" class="form-checkbox">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Status 1 - Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Status 2 - Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($allReimbursements as $reimbursement)
                            <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                <!-- Checkbox per row -->
                                <td class="px-4 py-4">
                                    @if(!$reimbursement->marked_down && $reimbursement->locked_by === Auth::id())
                                        <input type="checkbox"
                                            name="ids[]"
                                            value="{{ $reimbursement->id }}"
                                            class="row-checkbox form-checkbox">
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">#RY{{ $reimbursement->id }}</div>
                                        <div class="text-sm text-neutral-500">{{ $reimbursement->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center mr-3">
                                            @if($reimbursement->employee->url_profile)
                                                <img class="object-cover rounded-full"
                                                    src="{{ $reimbursement->employee->url_profile }}"
                                                    alt="{{ $reimbursement->employee->name }}">
                                            @else
                                                <span class="text-success-600 font-semibold text-xs">
                                                    {{ substr($reimbursement->employee->name, 0, 1) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $reimbursement->employee->name }}</div>
                                            <div class="text-sm text-neutral-500">{{ $reimbursement->employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">
                                        Rp {{ number_format($reimbursement->total, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">
                                        {{ \Carbon\Carbon::parse($reimbursement->date)->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($reimbursement->status_1 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i> Pending
                                        </span>
                                    @elseif($reimbursement->status_1 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i> Approved
                                        </span>
                                    @elseif($reimbursement->status_1 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($reimbursement->status_2 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i> Pending
                                        </span>
                                    @elseif($reimbursement->status_2 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i> Approved
                                        </span>
                                    @elseif($reimbursement->status_2 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i> Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $reimbursement->approver->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $manager->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $reimbursement->customer ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('finance.reimbursements.show', $reimbursement->id) }}"
                                        class="text-primary-600 hover:text-primary-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center">
                                    <div class="text-neutral-400">
                                        <i class="fas fa-receipt text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No reimbursement employee (No marked done) requests found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Reimbursement All Employee (Marked done) Table -->
        <p class="text-sm text-neutral-500 mb-2 ms-4">All employee reimbursement (Marked done) requests are listed below.</p>
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 1 - Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 2 - Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($allReimbursementsDone as $reimbursement)
                            <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">#RY{{ $reimbursement->id }}</div>
                                        <div class="text-sm text-neutral-500">{{ $reimbursement->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center mr-3">
                                            @if($reimbursement->employee->url_profile)
                                                <img class="object-cover rounded-full"
                                                    src="{{ $reimbursement->employee->url_profile }}" alt="{{ $reimbursement->employee->name }}">
                                            @else
                                                <span class="text-success-600 font-semibold text-xs">{{ substr($reimbursement->employee->name, 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $reimbursement->employee->name }}</div>
                                            <div class="text-sm text-neutral-500">{{ $reimbursement->employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">Rp {{ number_format($reimbursement->total, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ \Carbon\Carbon::parse($reimbursement->date)->format('M d, Y') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($reimbursement->status_1 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($reimbursement->status_1 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($reimbursement->status_1 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($reimbursement->status_2 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($reimbursement->status_2 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($reimbursement->status_2 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $reimbursement->approver->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $manager->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $reimbursement->customer ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('finance.reimbursements.show', $reimbursement->id) }}" class="text-primary-600 hover:text-primary-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center">
                                    <div class="text-neutral-400">
                                        <i class="fas fa-receipt text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No reimbursement employee (Marked done) requests found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($allReimbursementsDone->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200">
                    {{ $allReimbursementsDone->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
@push('scripts')
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const bulkBtn = document.getElementById('bulk-mark-btn');

    function toggleButtons() {
        const anyChecked = document.querySelectorAll('.row-checkbox:checked').length > 0;
        bulkBtn.disabled = !anyChecked;
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        toggleButtons();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', toggleButtons);
    });
@endpush