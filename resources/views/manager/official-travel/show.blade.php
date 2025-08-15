@extends('components.manager.layout.layout-manager')

@section('header', 'Official Travel Detail')
@section('subtitle', '')

@section('content')
<div class="max-w-4xl mx-auto">

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
                    <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-xl font-bold text-white">Official Travel Request #{{ $officialTravel->id }}</h1>
                                <p class="text-sm text-primary-100">Submitted on {{ $officialTravel->created_at->format('M d, Y \a\t H:i') }}</p>
                            </div>
                            <div class="text-right">
                                @if($officialTravel->status === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                        <i class="mr-1 fas fa-clock"></i>
                                        Pending Review
                                    </span>
                                @elseif($officialTravel->status === 'approved')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                        <i class="mr-1 fas fa-check-circle"></i>
                                        Approved
                                    </span>
                                @elseif($officialTravel->status === 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                        <i class="mr-1 fas fa-times-circle"></i>
                                        Rejected
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h2 class="text-lg font-bold text-neutral-900">Travel Details</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Employee Email</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-envelope text-primary-600"></i>
                                    <span class="font-medium text-neutral-900">{{ $officialTravel->employee->email }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Total Days</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-calendar-day text-primary-600"></i>
                                    <span class="font-medium text-neutral-900">{{ $officialTravel->total }} day{{ $officialTravel->total > 1 ? 's' : '' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Start Date</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-calendar-alt text-secondary-600"></i>
                                    <span class="font-medium text-neutral-900">{{ $officialTravel->date_start->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">End Date</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-calendar-alt text-secondary-600"></i>
                                    <span class="font-medium text-neutral-900">{{ $officialTravel->date_end->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Status</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    @if($officialTravel->status === 'pending')
                                        <i class="mr-3 fas fa-clock text-warning-600"></i>
                                        <span class="font-medium text-warning-800">Pending Review</span>
                                    @elseif($officialTravel->status === 'approved')
                                        <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                        <span class="font-medium text-success-800">Approved</span>
                                    @elseif($officialTravel->status === 'rejected')
                                        <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                        <span class="font-medium text-error-800">Rejected</span>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Approver</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-user-check text-info-600"></i>
                                    <span class="font-medium text-neutral-900">{{ $officialTravel->approver->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Travel Duration Breakdown</label>
                            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                                <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                                    <div>
                                        <span class="font-medium text-blue-800">Start Date:</span>
                                        <span class="text-blue-700">{{ $officialTravel->date_start->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-blue-800">End Date:</span>
                                        <span class="text-blue-700">{{ $officialTravel->date_end->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-blue-800">Duration:</span>
                                        <span class="font-bold text-blue-700">{{ $officialTravel->total }} day{{ $officialTravel->total > 1 ? 's' : '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">

                        <a href="{{ route('manager.official-travels.index') }}" class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-neutral-600 hover:bg-neutral-700">
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
