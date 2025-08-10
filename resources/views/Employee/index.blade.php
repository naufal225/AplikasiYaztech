@extends('Employee.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard Analytics')
@section('subtitle', 'Overview of all employee requests')

@section('content')
    <!-- Statistics Cards - Adjusted for mobile -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-5 mb-6 md:mb-8">
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
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">{{ $approvedLeaves + $approvedReimbursements + $approvedOvertimes + $approvedTravels }}</p>
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
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Rejected This Month</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">{{ $rejectedLeaves + $rejectedReimbursements + $rejectedOvertimes + $rejectedTravels }}</p>
                    <p class="text-neutral-500 text-xs mt-1">Rejected approved</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-error-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-times-circle text-error-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('employee.leaves.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white rounded-lg p-4 hover:shadow-md transition-all">
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-calendar-plus text-primary-600 text-xl"></i>
                </div>
                <h3 class="font-semibold mb-1">Request Leave</h3>
                <p class="text-primary-100 text-sm">Submit new leave request</p>
            </div>
        </a>

        <a href="{{ route('employee.reimbursements.create') }}" class="bg-secondary-600 hover:bg-secondary-700 text-white rounded-lg p-4 hover:shadow-md transition-all">
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-receipt text-secondary-600 text-xl"></i>
                </div>
                <h3 class="font-semibold mb-1">Submit Reimbursement</h3>
                <p class="text-secondary-100 text-sm">Upload expense receipts</p>
            </div>
        </a>

        <a href="{{ route('employee.overtimes.create') }}" class="bg-success-600 hover:bg-success-700 text-white rounded-lg p-4 hover:shadow-md transition-all">
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-clock text-success-600 text-xl"></i>
                </div>
                <h3 class="font-semibold mb-1">Request Overtime</h3>
                <p class="text-success-100 text-sm">Log overtime hours</p>
            </div>
        </a>

        <a href="{{ route('employee.official-travels.create') }}" class="bg-warning-600 hover:bg-warning-700 text-white rounded-lg p-4 hover:shadow-md transition-all">
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mb-3">
                    <i class="fas fa-plane text-warning-600 text-xl"></i>
                </div>
                <h3 class="font-semibold mb-1">Request Travel</h3>
                <p class="text-warning-100 text-sm">Plan business trip</p>
            </div>
        </a>
    </div>

    <!-- Recent Requests Section - Redesigned to match the image -->
    <div class="bg-white rounded-lg border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Requests</h3>
            <p class="text-gray-500 text-sm">Your latest submissions</p>
        </div>
        <div class="p-6">
            @forelse($recentRequests as $request)
                <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0">
                    <div class="flex items-center">
                        @if($request['type'] === App\TypeRequest::Leaves->value)
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">{{ $request['title'] }}</h4>
                                <p class="text-gray-500 text-sm">{{ $request['date'] }}</p>
                            </div>
                        @elseif($request['type'] === App\TypeRequest::Reimbursements->value)
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-receipt text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">{{ $request['title'] }}</h4>
                                <p class="text-gray-500 text-sm">{{ $request['date'] }}</p>
                            </div>
                        @elseif($request['type'] === App\TypeRequest::Overtimes->value)
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-clock text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">Overtime Request</h4>
                                <p class="text-gray-500 text-sm">{{ $request['date'] }}</p>
                            </div>
                        @elseif($request['type'] === App\TypeRequest::Travels->value)
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-plane text-yellow-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">Travel Request</h4>
                                <p class="text-gray-500 text-sm">{{ $request['date'] }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center">
                        @if($request['status'] === 'pending')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 mr-3">Pending</span>
                        @elseif($request['status'] === 'approved')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 mr-3">Approved</span>
                        @elseif($request['status'] === 'rejected')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 mr-3">Rejected</span>
                        @endif
                        <a href="{{ $request['url'] }}" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">No recent requests found.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pending Approvals (for approvers and admins) -->
    @if(Auth::user()->role === App\Roles::Approver->value || Auth::user()->role === App\Roles::Admin->value)
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Pending Approvals</h3>
                <p class="text-gray-500 text-sm">Requests awaiting your review</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($pendingApprovals as $approval)
                        <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0">
                            <div class="flex items-center">
                                @if($approval['type'] === 'leave')
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-calendar-alt text-blue-600"></i>
                                    </div>
                                @elseif($approval['type'] === 'reimbursement')
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-receipt text-purple-600"></i>
                                    </div>
                                @elseif($approval['type'] === 'overtime')
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-clock text-green-600"></i>
                                    </div>
                                @elseif($approval['type'] === 'travel')
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                                        <i class="fas fa-plane text-yellow-600"></i>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-medium text-gray-800">{{ $approval['title'] }}</h4>
                                    <p class="text-gray-500 text-sm">{{ $approval['employee'] }} - {{ $approval['date'] }}</p>
                                </div>
                            </div>
                            <a href="{{ $approval['url'] }}" class="px-4 py-2 text-xs font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                                Review
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-gray-300 text-4xl mb-3"></i>
                            <p class="text-gray-500">No pending approvals.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
@endsection