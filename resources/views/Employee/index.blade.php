@extends('Employee.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard Analytics')
@section('subtitle', 'Overview of all employee requests')

@section('content')
    <!-- Statistics Cards - Adjusted for mobile -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Total Employees Card -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Total Employees</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">156</p>
                    <p class="text-neutral-500 text-xs mt-1">Active employees</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-primary-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Card -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Pending Approvals</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">{{ $pendingLeaves + $pendingReimbursements + $pendingOvertimes + $pendingTravels }}</p>
                    <p class="text-neutral-500 text-xs mt-1">Awaiting review</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-warning-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-warning-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Approved This Month Card -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Approved This Month</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">89</p>
                    <p class="text-neutral-500 text-xs mt-1">Successfully approved</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-success-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-success-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Rejected Requests Card -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Rejected Requests</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">7</p>
                    <p class="text-neutral-500 text-xs mt-1">This month</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-error-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-error-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons - Adjusted for mobile -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6 md:mb-8">
        <a href="{{ route('employee.leaves.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white rounded-xl p-3 md:p-4 transition-colors duration-200 shadow-soft hover:shadow-medium">
            <div class="flex items-center">
                <i class="fas fa-calendar-plus text-lg md:text-xl mr-2 md:mr-3"></i>
                <div>
                    <p class="font-semibold text-sm md:text-base">Request Leave</p>
                    <p class="text-primary-100 text-xs">Submit new leave request</p>
                </div>
            </div>
        </a>

        <a href="{{ route('employee.reimbursements.create') }}" class="bg-secondary-600 hover:bg-secondary-700 text-white rounded-xl p-3 md:p-4 transition-colors duration-200 shadow-soft hover:shadow-medium">
            <div class="flex items-center">
                <i class="fas fa-receipt text-lg md:text-xl mr-2 md:mr-3"></i>
                <div>
                    <p class="font-semibold text-sm md:text-base">Submit Reimbursement</p>
                    <p class="text-secondary-100 text-xs">Upload expense receipts</p>
                </div>
            </div>
        </a>

        <a href="{{ route('employee.overtimes.create') }}" class="bg-success-600 hover:bg-success-700 text-white rounded-xl p-3 md:p-4 transition-colors duration-200 shadow-soft hover:shadow-medium">
            <div class="flex items-center">
                <i class="fas fa-clock text-lg md:text-xl mr-2 md:mr-3"></i>
                <div>
                    <p class="font-semibold text-sm md:text-base">Request Overtime</p>
                    <p class="text-success-100 text-xs">Log overtime hours</p>
                </div>
            </div>
        </a>

        <a href="{{ route('employee.official-travels.create') }}" class="bg-warning-600 hover:bg-warning-700 text-white rounded-xl p-3 md:p-4 transition-colors duration-200 shadow-soft hover:shadow-medium">
            <div class="flex items-center">
                <i class="fas fa-plane text-lg md:text-xl mr-2 md:mr-3"></i>
                <div>
                    <p class="font-semibold text-sm md:text-base">Request Travel</p>
                    <p class="text-warning-100 text-xs">Plan business trip</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Requests and Pending Approvals - Stacked on mobile -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
            <div class="px-4 md:px-6 py-3 md:py-4 border-b border-neutral-200">
                <h3 class="text-base md:text-lg font-bold text-neutral-900">Recent Requests</h3>
                <p class="text-neutral-600 text-xs md:text-sm">Your latest submissions</p>
            </div>
            <div class="p-4 md:p-6">
                <div class="space-y-3 md:space-y-4">
                    @forelse($recentRequests as $request)
                        <div class="flex items-center justify-between p-3 md:p-4 bg-neutral-50 rounded-lg border border-neutral-200 hover:bg-neutral-100 transition-colors duration-200">
                            <div class="flex items-center">
                                @if($request['type'] === 'leave')
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-primary-100 rounded-lg flex items-center justify-center mr-3 md:mr-4">
                                        <i class="fas fa-calendar-alt text-primary-600 text-sm md:text-base"></i>
                                    </div>
                                @elseif($request['type'] === 'reimbursement')
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-secondary-100 rounded-lg flex items-center justify-center mr-3 md:mr-4">
                                        <i class="fas fa-receipt text-secondary-600 text-sm md:text-base"></i>
                                    </div>
                                @elseif($request['type'] === 'overtime')
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-success-100 rounded-lg flex items-center justify-center mr-3 md:mr-4">
                                        <i class="fas fa-clock text-success-600 text-sm md:text-base"></i>
                                    </div>
                                @elseif($request['type'] === 'travel')
                                    <div class="w-8 h-8 md:w-10 md:h-10 bg-warning-100 rounded-lg flex items-center justify-center mr-3 md:mr-4">
                                        <i class="fas fa-plane text-warning-600 text-sm md:text-base"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-neutral-900 text-xs md:text-sm truncate">{{ $request['title'] }}</p>
                                    <p class="text-neutral-600 text-2xs md:text-xs truncate">{{ $request['date'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                @if($request['status'] === 'pending')
                                    <span class="px-2 py-0.5 md:px-3 md:py-1 text-2xs md:text-xs font-semibold rounded-full bg-warning-100 text-warning-800">Pending</span>
                                @elseif($request['status'] === 'approved')
                                    <span class="px-2 py-0.5 md:px-3 md:py-1 text-2xs md:text-xs font-semibold rounded-full bg-success-100 text-success-800">Approved</span>
                                @elseif($request['status'] === 'rejected')
                                    <span class="px-2 py-0.5 md:px-3 md:py-1 text-2xs md:text-xs font-semibold rounded-full bg-error-100 text-error-800">Rejected</span>
                                @endif
                                <a href="{{ $request['url'] }}" class="ml-2 md:ml-3 text-neutral-400 hover:text-neutral-600 transition-colors duration-200">
                                    <i class="fas fa-chevron-right text-xs md:text-sm"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 md:py-8">
                            <i class="fas fa-inbox text-neutral-300 text-3xl md:text-4xl mb-3 md:mb-4"></i>
                            <p class="text-neutral-500 text-sm">No recent requests found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Pending Approvals (for approvers and admins) -->
        @if(Auth::user()->role === App\Roles::Approver->value || Auth::user()->role === App\Roles::Admin->value)
            <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                <div class="px-4 md:px-6 py-3 md:py-4 border-b border-neutral-200">
                    <h3 class="text-base md:text-lg font-bold text-neutral-900">Pending Approvals</h3>
                    <p class="text-neutral-600 text-xs md:text-sm">Requests awaiting your review</p>
                </div>
                <div class="p-4 md:p-6">
                    <div class="space-y-3 md:space-y-4">
                        @forelse($pendingApprovals as $approval)
                            <div class="flex items-center justify-between p-3 md:p-4 bg-neutral-50 rounded-lg border border-neutral-200 hover:bg-neutral-100 transition-colors duration-200">
                                <div class="flex items-center min-w-0">
                                    @if($approval['type'] === 'leave')
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-primary-100 rounded-lg flex items-center justify-center mr-3 md:mr-4">
                                            <i class="fas fa-calendar-alt text-primary-600 text-sm md:text-base"></i>
                                        </div>
                                    @elseif($approval['type'] === 'reimbursement')
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-secondary-100 rounded-lg flex items-center justify-center mr-3 md:mr-4">
                                            <i class="fas fa-receipt text-secondary-600 text-sm md:text-base"></i>
                                        </div>
                                    @elseif($approval['type'] === 'overtime')
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-success-100 rounded-lg flex items-center justify-center mr-3 md:mr-4">
                                            <i class="fas fa-clock text-success-600 text-sm md:text-base"></i>
                                        </div>
                                    @elseif($approval['type'] === 'travel')
                                        <div class="w-8 h-8 md:w-10 md:h-10 bg-warning-100 rounded-lg flex items-center justify-center mr-3 md:mr-4">
                                            <i class="fas fa-plane text-warning-600 text-sm md:text-base"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-semibold text-neutral-900 text-xs md:text-sm truncate">{{ $approval['title'] }}</p>
                                        <p class="text-neutral-600 text-2xs md:text-xs truncate">{{ $approval['employee'] }} - {{ $approval['date'] }}</p>
                                    </div>
                                </div>
                                <a href="{{ $approval['url'] }}" class="px-3 py-1 md:px-4 md:py-2 text-2xs md:text-xs font-semibold rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors duration-200 whitespace-nowrap">
                                    Review
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-6 md:py-8">
                                <i class="fas fa-check-circle text-neutral-300 text-3xl md:text-4xl mb-3 md:mb-4"></i>
                                <p class="text-neutral-500 text-sm">No pending approvals.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection