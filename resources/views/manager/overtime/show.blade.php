@extends('components.manager.layout.layout-manager')

@section('header', 'Manage Reimbursements')
@section('subtitle', 'Manage Reimbursements data')

@php
$totalMinutes = $overtime->total;
$hours = floor($totalMinutes / 60);
$minutes = $totalMinutes % 60;
@endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Left Column - Main Details -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Request Header -->
            <div class="overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-white">Overtime Request #{{ $overtime->id }}</h1>
                            <p class="text-sm text-primary-100">Submitted on {{ $overtime->created_at->format('M d, Y
                                \a\t H:i') }}</p>
                        </div>
                        <div class="text-right">
                            @if($overtime->status === 'pending')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                <i class="mr-1 fas fa-clock"></i>
                                Pending Review
                            </span>
                            @elseif($overtime->status === 'approved')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                <i class="mr-1 fas fa-check-circle"></i>
                                Approved
                            </span>
                            @elseif($overtime->status === 'rejected')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                <i class="mr-1 fas fa-times-circle"></i>
                                Rejected
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overtime Details -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h2 class="text-lg font-bold text-neutral-900">Overtime Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Employee Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Employee Email</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-envelope text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $overtime->employee->email }}</span>
                            </div>
                        </div>

                        <!-- Total Hours -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Total Overtime Hours</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-hourglass-half text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $hours }} jam {{ $minutes }} menit</span>
                            </div>
                        </div>

                        <!-- Start Date & Time -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Start Date & Time</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-day text-secondary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $overtime->date_start->format('l, M d, Y
                                    \a\t H:i') }}</span>
                            </div>
                        </div>

                        <!-- End Date & Time -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">End Date & Time</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-day text-secondary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $overtime->date_end->format('l, M d, Y
                                    \a\t H:i') }}</span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($overtime->status === 'pending')
                                <i class="mr-3 fas fa-clock text-warning-600"></i>
                                <span class="font-medium text-warning-800">Pending Review</span>
                                @elseif($overtime->status === 'approved')
                                <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                <span class="font-medium text-success-800">Approved</span>
                                @elseif($overtime->status === 'rejected')
                                <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                <span class="font-medium text-error-800">Rejected</span>
                                @endif
                            </div>
                        </div>

                        <!-- Approver -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Approver</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-user-check text-info-600"></i>
                                <span class="font-medium text-neutral-900">{{ $overtime->approver->name ?? 'N/A'
                                    }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Work Hours Breakdown -->
                    <div class="mt-6 space-y-2">
                        <label class="text-sm font-semibold text-neutral-700">Work Hours Breakdown</label>
                        <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                            <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-1">
                                <div>
                                    <span class="font-medium text-blue-800">Normal Hours:</span>
                                    <span class="text-blue-700">09:00 - 17:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('manager.overtimes.index') }}"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-neutral-600 hover:bg-neutral-700">
                        <i class="mr-2 fas fa-arrow-left"></i>
                        Back to List
                    </a>
                    <button onclick="window.print()"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-secondary-600 hover:bg-secondary-700">
                        <i class="mr-2 fas fa-print"></i>
                        Print Request
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
