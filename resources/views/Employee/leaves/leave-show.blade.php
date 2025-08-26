@extends('Employee.layouts.app')

@section('title', 'Leave Requests')
@section('header', 'Leave Requests')
@section('subtitle', 'Manage your leave requests')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">Leave Requests</h1>
                <p class="text-neutral-600">Manage and track your leave requests</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <button onclick="window.location.href='{{ route('employee.leaves.create') }}'" class="btn-primary @if($sisaCuti <= 0) cursor-not-allowed @else cursor-pointer @endif" @if($sisaCuti <= 0) disabled @endif>
                    <i class="fas fa-plus mr-2"></i>
                    New Leave Request
                </button>
            </div>
        </div>
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-primary-100 text-primary-500">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Total Requests</p>
                        <p class="text-lg font-semibold">{{ $totalRequests }}</p>
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
                        <p class="text-lg font-semibold">{{ $pendingRequests }}</p>
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
                        <p class="text-lg font-semibold">{{ $approvedRequests }}</p>
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
                        <p class="text-lg font-semibold">{{ $rejectedRequests }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-full {{ $sisaCuti <= 0 ? 'bg-error-100 text-error-600' : ($sisaCuti > ((int) env('CUTI_TAHUNAN', 20) / 2) ? 'bg-success-100 text-success-600' : 'bg-warning-100 text-warning-600')}}">
                        <i class="fas fa-calendar-xmark text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-neutral-500">Remaining days</p>
                        <p class="text-lg font-semibold">{{ $sisaCuti }}/{{ env('CUTI_TAHUNAN', 20) }} ({{ now()->year }})</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 p-6">
            <form method="GET" action="{{ route('employee.leaves.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-input">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary mr-2">
                        <i class="fas fa-search mr-2"></i>
                        Filter
                    </button>
                    <a href="{{ route('employee.leaves.index') }}" class="btn-secondary">
                        <i class="fas fa-refresh mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Divider -->
        <div class="border-t border-primary-700 transform scale-y-50 mb-10 mt-6"></div>

        <!-- Leaves Table -->
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Request ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Manager</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($leaves as $leave)
                            <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">#LY{{ $leave->id }}</div>
                                        <div class="text-sm text-neutral-500">{{ $leave->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">
                                        {{ \Carbon\Carbon::parse($leave->date_start)->format('M d') }} - {{ \Carbon\Carbon::parse($leave->date_end)->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-neutral-500">
                                        {{ \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end)) + 1 }} days
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($leave->status_1 === 'pending')
                                        <span class="badge-pending text-warning-600">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($leave->status_1 === 'approved')
                                        <span class="badge-approved text-success-600">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($leave->status_1 === 'rejected')
                                        <span class="badge-rejected text-error-600">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">{{ $manager->name ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('employee.leaves.show', $leave->id) }}" class="text-primary-600 hover:text-primary-900" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::id() === $leave->employee_id && $leave->status_1 === 'pending')
                                            <a href="{{ route('employee.leaves.edit', $leave->id) }}" class="text-secondary-600 hover:text-secondary-900" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('employee.leaves.destroy', $leave->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
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
                                <td colspan="5" class="h-64 text-center align-middle">
                                    <div class="flex flex-col items-center justify-center h-full text-neutral-400">
                                        <i class="fas fa-inbox text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No leave requests found</p>
                                        <p class="text-sm">Create your first leave request to get started</p>
                                        <button
                                            onclick="window.location.href='{{ route('employee.leaves.create') }}'"
                                            @if($sisaCuti <= 0) disabled @endif
                                            class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200 @if($sisaCuti <= 0) cursor-not-allowed @else cursor-pointer @endif">
                                                <i class="fas fa-plus mr-2"></i>
                                                New Leave Request
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($leaves->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection