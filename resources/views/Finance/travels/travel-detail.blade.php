@extends('Finance.layouts.app')
@section('title', 'Official Travel Requests')
@section('header', 'Official Travel Requests')
@section('subtitle', 'Manage your official travel claims')

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
                        <span class="text-sm font-medium text-neutral-500">Request #TY{{ $officialTravel->id }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="relative bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
                    <!-- Overlay Checklist -->
                    @if($officialTravel->marked_down)
                        <div class="absolute inset-0 flex items-center justify-center bg-white/70 z-10 rounded-xl">
                            <i class="fas fa-check-circle bg-white rounded-full text-green-500 text-7xl drop-shadow-lg"></i>
                        </div>
                    @endif

                    <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-xl font-bold text-white">Official Travel Request #TY{{ $officialTravel->id }}</h1>
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
                                        {{ (Auth::id() === $officialTravel->employee_id && $officialTravel->status_1 === 'pending' && !\App\Models\Division::where('leader_id', Auth::id())->exists()) || (\App\Models\Division::where('leader_id', Auth::id())->exists() && $officialTravel->status_2 === 'pending') ? 'Pending' : 'In Progress' }} Review
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
                                    <span class="text-neutral-900 font-medium truncate">{{ $officialTravel->employee->email }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Customer</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-users text-info-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium truncate">{{ $officialTravel->customer ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Start Date</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-calendar-alt text-secondary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium truncate">{{ $officialTravel->date_start->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">End Date</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-calendar-alt text-secondary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium truncate">{{ $officialTravel->date_end->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Total Days</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    @php
                                        $start = Carbon\Carbon::parse($officialTravel->date_start);
                                        $end = Carbon\Carbon::parse($officialTravel->date_end);
                                        $totalDays = $start->startOfDay()->diffInDays($end->startOfDay()) + 1;
                                    @endphp
                                    <i class="fas fa-calendar-day text-primary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $totalDays }} day{{ $totalDays > 1 ? 's' : '' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Total Costs</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-dollar-sign text-primary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium truncate">{{ 'Rp ' . number_format($officialTravel->total ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Status 1 - Approver 1</label>
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
                                <label class="text-sm font-semibold text-neutral-700">Status 2 - Approver 2</label>
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
                                <label class="text-sm font-semibold text-neutral-700">Note - Approver 1</label>
                                <div class="flex items-start p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                    <span class="text-neutral-900 break-all whitespace-pre-line max-w-full max-h-40 overflow-y-auto">{{ $officialTravel->note_1 ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Note - Approver 2</label>
                                <div class="flex items-start p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                    <span class="text-neutral-900 break-all whitespace-pre-line max-w-full max-h-40 overflow-y-auto">{{ $officialTravel->note_2 ?? '-' }}</span>
                                </div>
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
                        @if((Auth::id() === $officialTravel->employee_id && $officialTravel->status_1 === 'pending' && !\App\Models\Division::where('leader_id', Auth::id())->exists()) || (\App\Models\Division::where('leader_id', Auth::id())->exists() && $officialTravel->status_2 === 'pending'))
                            <a href="{{ route('finance.official-travels.edit', $officialTravel->id) }}" class="w-full flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Request
                            </a>
                            <form action="{{ route('finance.official-travels.destroy', $officialTravel->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this official travel request?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-error-600 hover:bg-error-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Request
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('finance.official-travels.index') }}" class="w-full flex items-center justify-center px-4 py-2 bg-neutral-600 hover:bg-neutral-700 text-white font-semibold rounded-lg transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to List
                        </a>

                        @if ($officialTravel->status_1 == 'approved' && $officialTravel->status_2 == 'approved' && $officialTravel->marked_down)
                            <button onclick="window.location.href='{{ route('finance.official-travels.exportPdf', $officialTravel->id) }}'" class="w-full flex items-center justify-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <i class="fas fa-print mr-2"></i>
                                Print Request
                            </button>
                        @endif

                        @if ($officialTravel->status_1 == 'approved' && $officialTravel->status_2 == 'approved' && !$officialTravel->marked_down && $officialTravel->locked_by === Auth::id() && $officialTravel->locked_at->addMinutes(60)->isFuture())
                            <form action="{{ route('finance.official-travels.marked') }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to mark selected overtimes as done?')">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="ids[]" value="{{ $officialTravel->id }}">
                                
                                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-success-600 hover:bg-success-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                    <i class="fas fa-check mr-2"></i>
                                    Mark as done
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection