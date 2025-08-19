@extends('Finance.layouts.app')
@section('title', 'Reimbursement Requests')
@section('header', 'Reimbursement Requests')
@section('subtitle', 'Manage your reimbursement claims')

@section('content')
    <div class="max-w-4xl mx-auto">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('finance.dashboard') }}" class="inline-flex items-center text-sm font-medium text-neutral-700 hover:text-primary-600">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <a href="{{ route('finance.official-travels.index') }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Official Travel Requests</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <span class="text-sm font-medium text-neutral-500">Request #{{ $officialTravel->id }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-xl font-bold text-white">Official Travel Request #{{ $officialTravel->id }}</h1>
                                <p class="text-primary-100 text-sm">Submitted on {{ $officialTravel->created_at->format('M d, Y \a\t H:i') }}</p>
                                <p class="text-sm mt-4 text-primary-100 font-medium">Owner Name: {{ $officialTravel->employee->name }}</p>
                            </div>
                            <div class="text-right">
                                @if($officialTravel->status_1 === 'rejected' || $officialTravel->status_2 === 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                        <i class="mr-1 mt-1 fas fa-times-circle"></i>
                                        Rejected
                                    </span>
                                @elseif($officialTravel->status_1 === 'approved' && $officialTravel->status_2 === 'approved')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                        <i class="mr-1 mt-1 fas fa-check-circle"></i>
                                        Approved
                                    </span>
                                @elseif($officialTravel->status_1 === 'pending' || $officialTravel->status_2 === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                        <i class="mr-1 mt-1 fas fa-clock"></i>
                                        {{ $officialTravel->status_1 === 'pending' ? 'Pending' : 'In Progress' }} Review
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h2 class="text-lg font-bold text-neutral-900">Travel Details</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Employee Email</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-envelope text-primary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $officialTravel->employee->email }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Team Lead</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-user-check text-info-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $officialTravel->approver->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Start Date</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-calendar-alt text-secondary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $officialTravel->date_start->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">End Date</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-calendar-alt text-secondary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $officialTravel->date_end->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Status 1 - Team Lead</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    @if($officialTravel->status_1 === 'pending')
                                        <i class="fas fa-clock text-warning-600 mr-3"></i>
                                        <span class="text-warning-800 font-medium">Pending Review</span>
                                    @elseif($officialTravel->status_1 === 'approved')
                                        <i class="fas fa-check-circle text-success-600 mr-3"></i>
                                        <span class="text-success-800 font-medium">Approved</span>
                                    @elseif($officialTravel->status_1 === 'rejected')
                                        <i class="fas fa-times-circle text-error-600 mr-3"></i>
                                        <span class="text-error-800 font-medium">Rejected</span>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Status 1 - Manager</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    @if($officialTravel->status_2 === 'pending')
                                        <i class="fas fa-clock text-warning-600 mr-3"></i>
                                        <span class="text-warning-800 font-medium">Pending Review</span>
                                    @elseif($officialTravel->status_2 === 'approved')
                                        <i class="fas fa-check-circle text-success-600 mr-3"></i>
                                        <span class="text-success-800 font-medium">Approved</span>
                                    @elseif($officialTravel->status_2 === 'rejected')
                                        <i class="fas fa-times-circle text-error-600 mr-3"></i>
                                        <span class="text-error-800 font-medium">Rejected</span>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Note - Team Lead</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                    <span class="text-neutral-900">{{ $overtime->note_1 ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Note - Manager</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                    <span class="text-neutral-900">{{ $overtime->note_2 ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Total Days</label>
                            <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                <i class="fas fa-calendar-day text-primary-600 mr-3"></i>
                                <span class="text-neutral-900 font-medium">{{ $officialTravel->total }} day{{ $officialTravel->total > 1 ? 's' : '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('finance.official-travels.index') }}" class="w-full flex items-center justify-center px-4 py-2 bg-neutral-600 hover:bg-neutral-700 text-white font-semibold rounded-lg transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to List
                        </a>
                        <button onclick="window.location.href='{{ route('finance.official-travels.exportPdf', $officialTravel->id) }}'" class="w-full flex items-center justify-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                            <i class="fas fa-print mr-2"></i>
                            Print Request
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection