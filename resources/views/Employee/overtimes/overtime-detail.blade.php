@extends('Employee.layouts.app')

@section('title', 'Overtime Request Details')
@section('header', 'Overtime Request Details')
@section('subtitle', 'View overtime request information')

@php
    $totalMinutes = $overtime->total;
    $hours = floor($totalMinutes / 60);
    $minutes = $totalMinutes % 60;
@endphp

@section('content')
    <div class="max-w-4xl mx-auto">
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
                        <a href="{{ route('employee.overtimes.index') }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Overtime Requests</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <span class="text-sm font-medium text-neutral-500">Request #{{ $overtime->id }}</span>
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
                                <h1 class="text-xl font-bold text-white">Overtime Request #{{ $overtime->id }}</h1>
                                <p class="text-primary-100 text-sm">Submitted on {{ $overtime->created_at->format('M d, Y \a\t H:i') }}</p>
                            </div>
                            <div class="text-right">
                                @if($overtime->status_1 === 'pending' || $overtime->status_2 === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-warning-100 text-warning-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        Pending Review
                                    </span>
                                @elseif($overtime->status_1 === 'approved' || $overtime->status_2 === 'approved')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-success-100 text-success-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Approved
                                    </span>
                                @elseif($overtime->status_1 === 'rejected' || $overtime->status_2 === 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-error-100 text-error-800">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Rejected
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overtime Details -->
                <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h2 class="text-lg font-bold text-neutral-900">Overtime Details</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Employee Email -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Employee Email</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-envelope text-primary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $overtime->employee->email }}</span>
                                </div>
                            </div>

                            <!-- Total Hours -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Total Overtime Hours</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-hourglass-half text-primary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $hours }} jam {{ $minutes }} menit</span>
                                </div>
                            </div>

                            <!-- Start Date & Time -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Start Date & Time</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-calendar-day text-secondary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $overtime->date_start->format('l, M d, Y \a\t H:i') }}</span>
                                </div>
                            </div>

                            <!-- End Date & Time -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">End Date & Time</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-calendar-day text-secondary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $overtime->date_end->format('l, M d, Y \a\t H:i') }}</span>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Status</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    @if($overtime->status_1 === 'pending' || $overtime->status_2 === 'pending')
                                        <i class="fas fa-clock text-warning-600 mr-3"></i>
                                        <span class="text-warning-800 font-medium">Pending Review</span>
                                    @elseif($overtime->status_1 === 'approved' || $overtime->status_2 === 'approved')
                                        <i class="fas fa-check-circle text-success-600 mr-3"></i>
                                        <span class="text-success-800 font-medium">Approved</span>
                                    @elseif($overtime->status_1 === 'rejected' || $overtime->status_2 === 'rejected')
                                        <i class="fas fa-times-circle text-error-600 mr-3"></i>
                                        <span class="text-error-800 font-medium">Rejected</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Approver -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Approver</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-user-check text-info-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $overtime->approver->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Work Hours Breakdown -->
                        <div class="mt-6 space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Work Hours Breakdown</label>
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="grid grid-cols-1 md:grid-cols-1 gap-4 text-sm">
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
                <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if(Auth::id() === $overtime->employee_id && $overtime->status === 'pending')
                            <a href="{{ route('employee.overtimes.edit', $overtime->id) }}" class="w-full flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Request
                            </a>
                            <form action="{{ route('employee.overtimes.destroy', $overtime->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this overtime request?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-error-600 hover:bg-error-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Request
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('employee.overtimes.index') }}" class="w-full flex items-center justify-center px-4 py-2 bg-neutral-600 hover:bg-neutral-700 text-white font-semibold rounded-lg transition-colors duration-200">
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
@endsection