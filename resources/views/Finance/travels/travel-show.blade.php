@extends('Finance.layouts.app')

@section('title', 'Official Travel Requests')
@section('header', 'Official Travel Requests')
@section('subtitle', 'Manage employee official travel requests')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">Official Travel Requests</h1>
                <p class="text-neutral-600">Submit and track employee official travel requests</p>
            </div>

            <div class="mt-4 sm:mt-0">
                <button onclick="window.location.href='{{ route('finance.official-travels.create') }}'" class="btn-primary cursor-pointer">
                    <i class="fas fa-plus mr-2"></i>
                    New Travel Request
                </button>
            </div>
        </div>

        <!-- Statistics Yours Cards -->
        <p class="text-sm text-neutral-500 mb-2 ms-4">Your Requests</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-primary-100 text-primary-500">
                        <i class="fas fa-plane text-xl"></i>
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
                        <i class="fas fa-plane text-xl"></i>
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
            <form method="GET" action="{{ route('finance.official-travels.index') }}" class="space-y-4">
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
                    <button type="button" onclick="window.location.href = '{{ route('finance.official-travels.index') }}'"
                        class="flex justify-center-safe items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl shadow-sm 
                            hover:bg-gray-200 hover:shadow-md transition-all duration-300 w-full sm:w-auto">
                        <i class="fas fa-refresh mr-2"></i> Reset
                    </button>

                    <!-- Bulk Request -->
                    <button type="button"
                        onclick="window.location.href='{{ route('finance.official-travels.bulkExport', [
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

        <!-- Your official travels Employee Table -->
        <p class="text-sm text-neutral-500 mb-2 ms-4">Your official travel requests are listed below.</p>
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Costs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 1 - Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 2 - Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($yourTravels as $officialTravel)
                            <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">#TY{{ $officialTravel->id }}</div>
                                        <div class="text-sm text-neutral-500">{{ $officialTravel->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-success-600 font-semibold text-xs">{{ substr($officialTravel->employee->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $officialTravel->employee->name }}</div>
                                            <div class="text-sm text-neutral-500">{{ $officialTravel->employee->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">
                                        {{ $officialTravel->date_start->format('M d Y') }}
                                    </div>
                                    <div class="text-sm text-neutral-500">
                                        to {{ $officialTravel->date_end->format('M d Y') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $start = Carbon\Carbon::parse($officialTravel->date_start);
                                        $end = Carbon\Carbon::parse($officialTravel->date_end);
                                        $totalDays = $start->startOfDay()->diffInDays($end->startOfDay()) + 1;
                                    @endphp
                                    <div class="text-sm font-bold text-neutral-900">{{ $totalDays }} day{{ $totalDays > 1 ? 's' : '' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-success-600">{{ '+Rp' . number_format($officialTravel->total ?? 0, 0, ',', '.') }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($officialTravel->status_1 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($officialTravel->status_1 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($officialTravel->status_1 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($officialTravel->status_2 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($officialTravel->status_2 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($officialTravel->status_2 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $officialTravel->approver->name ?? 'N/A' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $manager->name ?? 'N/A' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('finance.official-travels.show', $officialTravel->id) }}" class="text-primary-600 hover:text-primary-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if((Auth::id() === $officialTravel->employee_id && $officialTravel->status_1 === 'pending' && !\App\Models\Division::where('leader_id', Auth::id())->exists()) || (\App\Models\Division::where('leader_id', Auth::id())->exists() && $officialTravel->status_2 === 'pending'))
                                            <a href="{{ route('finance.official-travels.edit', $officialTravel->id) }}" class="text-secondary-600 hover:text-secondary-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('finance.official-travels.destroy', $officialTravel->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
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
                                        <i class="fas fa-plane text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No official travel requests found</p>
                                        <p class="text-sm">Submit your first travel request to get started</p>
                                        <a href="{{ route('finance.official-travels.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200">
                                            <i class="fas fa-plus mr-2"></i>
                                            New Travel Request
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($yourTravels->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200">
                    {{ $yourTravels->links() }}
                </div>
            @endif
        </div>

        <!-- Official Travels All Employee Table -->
        <form action="{{ route('finance.official-travels.marked') }}" method="POST"
            onsubmit="return confirm('Are you sure you want to mark selected official travels as done?')">
            @csrf
            @method('PATCH')

            <div class="flex flex-row max-md:flex-col justify-between items-center p-4 mb-2">
                <p class="text-sm text-neutral-500 max-md:mb-6">All employee official travel requests are listed below.</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Costs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 1 - Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 2 - Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($allTravels as $officialTravel)
                            <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                <!-- Checkbox per row -->
                                <td class="px-4 py-4">
                                    @if(!$officialTravel->marked_down && $officialTravel->locked_by === Auth::id())
                                        <input type="checkbox"
                                            name="ids[]"
                                            value="{{ $officialTravel->id }}"
                                            class="row-checkbox form-checkbox">
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">#TY{{ $officialTravel->id }}</div>
                                        <div class="text-sm text-neutral-500">{{ $officialTravel->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-success-600 font-semibold text-xs">{{ substr($officialTravel->employee->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $officialTravel->employee->name }}</div>
                                            <div class="text-sm text-neutral-500">{{ $officialTravel->employee->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">
                                        {{ $officialTravel->date_start->format('M d Y') }}
                                    </div>
                                    <div class="text-sm text-neutral-500">
                                        to {{ $officialTravel->date_end->format('M d Y') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $start = Carbon\Carbon::parse($officialTravel->date_start);
                                        $end = Carbon\Carbon::parse($officialTravel->date_end);
                                        $totalDays = $start->startOfDay()->diffInDays($end->startOfDay()) + 1;
                                    @endphp
                                    <div class="text-sm font-bold text-neutral-900">{{ $totalDays }} day{{ $totalDays > 1 ? 's' : '' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-success-600">{{ '+Rp' . number_format($officialTravel->total ?? 0, 0, ',', '.') }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($officialTravel->status_1 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($officialTravel->status_1 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($officialTravel->status_1 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($officialTravel->status_2 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($officialTravel->status_2 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($officialTravel->status_2 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $officialTravel->approver->name ?? 'N/A' }}</div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $manager->name ?? 'N/A' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('finance.official-travels.show', $officialTravel->id) }}" class="text-primary-600 hover:text-primary-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if((Auth::id() === $officialTravel->employee_id && $officialTravel->status_1 === 'pending' && !\App\Models\Division::where('leader_id', Auth::id())->exists()) || (\App\Models\Division::where('leader_id', Auth::id())->exists() && $officialTravel->status_2 === 'pending'))
                                            <a href="{{ route('finance.official-travels.edit', $officialTravel->id) }}" class="text-secondary-600 hover:text-secondary-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('finance.official-travels.destroy', $officialTravel->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
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
                                <td colspan="11" class="px-6 py-12 text-center">
                                    <div class="text-neutral-400">
                                        <i class="fas fa-plane text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No official travel employee (No marked done) requests found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Official Travels All Employee (Marked done) Table -->
        <p class="text-sm text-neutral-500 mb-2 ms-4">All employee official travels (Marked done) requests are listed below.</p>
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Costs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 1 - Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status 2 - Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 1</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver 2</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($allTravelsDone as $officialTravel)
                            <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">#TY{{ $officialTravel->id }}</div>
                                        <div class="text-sm text-neutral-500">{{ $officialTravel->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-success-600 font-semibold text-xs">{{ substr($officialTravel->employee->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $officialTravel->employee->name }}</div>
                                            <div class="text-sm text-neutral-500">{{ $officialTravel->employee->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">
                                        {{ $officialTravel->date_start->format('M d Y') }}
                                    </div>
                                    <div class="text-sm text-neutral-500">
                                        to {{ $officialTravel->date_end->format('M d Y') }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $start = Carbon\Carbon::parse($officialTravel->date_start);
                                        $end = Carbon\Carbon::parse($officialTravel->date_end);
                                        $totalDays = $start->startOfDay()->diffInDays($end->startOfDay()) + 1;
                                    @endphp
                                    <div class="text-sm font-bold text-neutral-900">{{ $totalDays }} day{{ $totalDays > 1 ? 's' : '' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-success-600">{{ '+Rp' . number_format($officialTravel->total ?? 0, 0, ',', '.') }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($officialTravel->status_1 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($officialTravel->status_1 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($officialTravel->status_1 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($officialTravel->status_2 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($officialTravel->status_2 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($officialTravel->status_2 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $officialTravel->approver->name ?? 'N/A' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $manager->name ?? 'N/A' }}</div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('finance.official-travels.show', $officialTravel->id) }}" class="text-primary-600 hover:text-primary-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        @if((Auth::id() === $officialTravel->employee_id && $officialTravel->status_1 === 'pending' && !\App\Models\Division::where('leader_id', Auth::id())->exists()) || (\App\Models\Division::where('leader_id', Auth::id())->exists() && $officialTravel->status_2 === 'pending'))
                                            <a href="{{ route('finance.official-travels.edit', $officialTravel->id) }}" class="text-secondary-600 hover:text-secondary-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('finance.official-travels.destroy', $officialTravel->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
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
                                        <i class="fas fa-plane text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No official travel (Marked done) requests found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($allTravelsDone->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200">
                    {{ $allTravelsDone->links() }}
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