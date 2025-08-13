@extends('Employee.layouts.app')

@section('title', 'Reimbursement Requests')
@section('header', 'Reimbursement Requests')
@section('subtitle', 'Manage your reimbursement claims')

@section('content')
    <div class="max-w-4xl mx-auto">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center text-sm font-medium text-neutral-700 hover:text-primary-600">
                        <i class="mr-2 fas fa-home"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                        <a href="{{ route('employee.leaves.index') }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Leave Requests</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                        <span class="text-sm font-medium text-neutral-500">Cuti #{{ $leave->id }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <!-- Main Content -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Left Column - Main Details -->
            <div class="space-y-6 lg:col-span-2">
                <div class="overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
                    <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-xl font-bold text-white">Leave Request #{{ $leave->id }}</h1>
                                <p class="text-sm text-primary-100">Submitted on {{ Carbon\Carbon::parse($leave->created_at)->format('M d, Y \a\t H:i') }}</p>
                            </div>
                            <div class="text-right">
                                @if($leave->status_1 === 'pending' || $leave->status_2 === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                        <i class="mr-1 fas fa-clock"></i>
                                        Pending Review
                                    </span>
                                @elseif($leave->status_1 === 'approved' || $leave->status_2 === 'approved')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                        <i class="mr-1 fas fa-check-circle"></i>
                                        Approved
                                    </span>
                                @elseif($leave->status_1 === 'rejected' || $leave->status_2 === 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                        <i class="mr-1 fas fa-times-circle"></i>
                                        Rejected
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Leave Details -->
                <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h2 class="text-lg font-bold text-neutral-900">Leave Details</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Email</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-envelope text-primary-600"></i>
                                    <span class="font-medium text-neutral-900">{{ Auth::user()->email }}</span>
                                </div>
                            </div>
                            <!-- Approver -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Approver</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-user-check text-info-600"></i>
                                    <span class="font-medium text-neutral-900">{{ $leave->approver->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <!-- Start Date -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Start Date</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-calendar-alt text-primary-600"></i>
                                    <span class="font-medium text-neutral-900">{{ \Carbon\Carbon::parse($leave->date_start)->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <!-- End Date -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">End Date</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-calendar-alt text-primary-600"></i>
                                    <span class="font-medium text-neutral-900">{{ \Carbon\Carbon::parse($leave->date_end)->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <!-- Duration -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Duration</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-clock text-secondary-600"></i>
                                    <span class="font-medium text-neutral-900">
                                        {{ (int) \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end, ), false) + 1 }}
                                        {{ (int) \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end, ), false) + 1 === 1 ? 'day' : 'days' }}
                                    </span>
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Status</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    @if($leave->status_1 === 'pending' || $leave->status_2 === 'pending')
                                        <i class="mr-3 fas fa-clock text-warning-600"></i>
                                        <span class="font-medium text-warning-800">Pending Review</span>
                                    @elseif($leave->status_1 === 'approved' || $leave->status_2 === 'approved')
                                        <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                        <span class="font-medium text-success-800">Approved</span>
                                    @elseif($leave->status_1 === 'rejected' || $leave->status_2 === 'rejected')
                                        <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                        <span class="font-medium text-error-800">Rejected</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- Reason -->
                        <div class="mt-6 space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Reason for Leave</label>
                            <div class="p-4 border rounded-lg bg-neutral-50 border-neutral-200">
                                <p class="leading-relaxed text-neutral-900">{{ $leave->reason }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if(Auth::id() === $leave->employee_id && ($leave->status_1 === 'pending' || $leave->status_2 === 'pending'))
                            <a href="{{ route('employee.leaves.edit', $leave->id) }}" class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                                <i class="mr-2 fas fa-edit"></i>
                                Edit Request
                            </a>
                            <form action="{{ route('employee.leaves.destroy', $leave->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave request?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-error-600 hover:bg-error-700">
                                    <i class="mr-2 fas fa-trash"></i>
                                    Delete Request
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('employee.leaves.index') }}" class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-neutral-600 hover:bg-neutral-700">
                            <i class="mr-2 fas fa-arrow-left"></i>
                            Back to List
                        </a>
                        <button onclick="window.print()" class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-secondary-600 hover:bg-secondary-700">
                            <i class="mr-2 fas fa-print"></i>
                            Print Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
