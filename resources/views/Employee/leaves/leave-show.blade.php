@extends('Employee.layouts.app')

@section('title', 'Leave Requests')
@section('header', 'Leave Requests')
@section('subtitle', 'Manage your leave requests')

@section('content')
    @if(isset($leave))
        {{-- Individual Leave Show --}}
        <div class="max-w-4xl mx-auto">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center text-sm font-medium text-neutral-700 hover:text-primary-600">
                            <i class="fas fa-home mr-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                            <a href="{{ route('employee.leaves.index') }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Leave Requests</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                            <span class="text-sm font-medium text-neutral-500">Cuti #{{ $leave->id }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Request Header -->
                    <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-xl font-bold text-white">Leave Request #{{ $leave->id }}</h1>
                                    <p class="text-primary-100 text-sm">Submitted on {{ $leave->created_at->format('M d, Y \a\t H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    @if($leave->status === 'pending')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-warning-100 text-warning-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending Review
                                        </span>
                                    @elseif($leave->status === 'approved')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-error-100 text-error-800">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Details -->
                    <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                        <div class="px-6 py-4 border-b border-neutral-200">
                            <h2 class="text-lg font-bold text-neutral-900">Leave Details</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Email -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700">Email</label>
                                    <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                        <i class="fas fa-envelope text-primary-600 mr-3"></i>
                                        <span class="text-neutral-900 font-medium">{{ Auth::user()->email }}</span>
                                    </div>
                                </div>

                                <!-- Approver -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700">Approver</label>
                                    <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                        <i class="fas fa-user-check text-info-600 mr-3"></i>
                                        <span class="text-neutral-900 font-medium">{{ $leave->approver->name ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <!-- Start Date -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700">Start Date</label>
                                    <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                        <i class="fas fa-calendar-alt text-primary-600 mr-3"></i>
                                        <span class="text-neutral-900 font-medium">{{ \Carbon\Carbon::parse($leave->date_start)->format('l, M d, Y') }}</span>
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700">End Date</label>
                                    <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                        <i class="fas fa-calendar-alt text-primary-600 mr-3"></i>
                                        <span class="text-neutral-900 font-medium">{{ \Carbon\Carbon::parse($leave->date_end)->format('l, M d, Y') }}</span>
                                    </div>
                                </div>

                                <!-- Duration -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700">Duration</label>
                                    <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                        <i class="fas fa-clock text-secondary-600 mr-3"></i>
                                        <span class="text-neutral-900 font-medium">
                                            {{ \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end)) + 1 }} 
                                            {{ \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end)) + 1 === 1 ? 'day' : 'days' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-neutral-700">Status</label>
                                    <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                        @if($leave->status === 'pending')
                                            <i class="fas fa-clock text-warning-600 mr-3"></i>
                                            <span class="text-warning-800 font-medium">Pending Review</span>
                                        @elseif($leave->status === 'approved')
                                            <i class="fas fa-check-circle text-success-600 mr-3"></i>
                                            <span class="text-success-800 font-medium">Approved</span>
                                        @elseif($leave->status === 'rejected')
                                            <i class="fas fa-times-circle text-error-600 mr-3"></i>
                                            <span class="text-error-800 font-medium">Rejected</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Reason -->
                            <div class="mt-6 space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Reason for Leave</label>
                                <div class="p-4 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <p class="text-neutral-900 leading-relaxed">{{ $leave->reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Sidebar -->
                <div class="space-y-6">
                    <!-- Actions -->
                    <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                        <div class="px-6 py-4 border-b border-neutral-200">
                            <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            @if(Auth::id() === $leave->employee_id && $leave->status === 'pending')
                                <a href="{{ route('employee.leaves.edit', $leave->id) }}" class="w-full flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                    <i class="fas fa-edit mr-2"></i>
                                    Edit Request
                                </a>
                                <form action="{{ route('employee.leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave request?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-error-600 hover:bg-error-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                        <i class="fas fa-trash mr-2"></i>
                                        Delete Request
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('employee.leaves.index') }}" class="w-full flex items-center justify-center px-4 py-2 bg-neutral-600 hover:bg-neutral-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Back to List
                            </a>

                            <button onclick="window.print()" class="w-full flex items-center justify-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <i class="fas fa-print mr-2"></i>
                                Print Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Leaves Index --}}
        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Leave Requests</h1>
                    <p class="text-neutral-600">Manage and track your leave requests</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('employee.leaves.create') }}" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        New Leave Request
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-primary-100 text-primary-500">
                            <i class="fas fa-calendar-alt text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-neutral-500">Total Requests</p>
                            <p class="text-lg font-semibold">{{ $leaves->total() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-warning-100 text-warning-500">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-neutral-500">Pending</p>
                            <p class="text-lg font-semibold">{{ $leaves->where('status', 'pending')->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-success-100 text-success-500">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-neutral-500">Approved</p>
                            <p class="text-lg font-semibold">{{ $leaves->where('status', 'approved')->count() }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-soft p-6 border border-neutral-200">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-error-100 text-error-500">
                            <i class="fas fa-times-circle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-neutral-500">Rejected</p>
                            <p class="text-lg font-semibold">{{ $leaves->where('status', 'rejected')->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leaves Table -->
            <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200">
                        <thead class="bg-neutral-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Request</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Approver</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-neutral-200">
                            @forelse($leaves as $leave)
                                <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">#{{ $leave->id }}</div>
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
                                        @if($leave->status === 'pending')
                                            <span class="badge-pending">
                                                <i class="fas fa-clock mr-1"></i>
                                                Pending
                                            </span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge-approved">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Approved
                                            </span>
                                        @elseif($leave->status === 'rejected')
                                            <span class="badge-rejected">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-neutral-900">{{ $leave->approver->name }}</div>
                                        <div class="text-sm text-neutral-500">{{ ucfirst($leave->approver->role) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('employee.leaves.show', $leave->id) }}" class="text-primary-600 hover:text-primary-900" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(Auth::id() === $leave->employee_id && $leave->status === 'pending')
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
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <div class="text-neutral-400">
                                            <i class="fas fa-inbox text-4xl mb-4"></i>
                                            <p class="text-lg font-medium">No leave requests found</p>
                                            <p class="text-sm">Create your first leave request to get started</p>
                                            <a href="{{ route('employee.leaves.create') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors duration-200">
                                                <i class="fas fa-plus mr-2"></i>
                                                New Leave Request
                                            </a>
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
    @endif
@endsection