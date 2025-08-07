@extends('layouts.app')

@section('title', 'Dashboard')

@section('header', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Leave Requests Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Leave Requests</p>
                    <p class="text-lg font-semibold">{{ $pendingLeaves }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('leaves.create') }}" class="text-blue-500 hover:text-blue-700 text-sm font-medium">
                    Request Leave <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Reimbursements Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500">
                    <i class="fas fa-receipt text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Reimbursements</p>
                    <p class="text-lg font-semibold">{{ $pendingReimbursements }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('reimbursements.create') }}" class="text-green-500 hover:text-green-700 text-sm font-medium">
                    Submit Reimbursement <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Overtime Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-500">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Overtime</p>
                    <p class="text-lg font-semibold">{{ $pendingOvertimes }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('overtimes.create') }}" class="text-purple-500 hover:text-purple-700 text-sm font-medium">
                    Request Overtime <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>

        <!-- Official Travel Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100 text-orange-500">
                    <i class="fas fa-plane text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Official Travel</p>
                    <p class="text-lg font-semibold">{{ $pendingTravels }}</p>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('official-travels.create') }}" class="text-orange-500 hover:text-orange-700 text-sm font-medium">
                    Request Travel <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Requests Section -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold">Recent Requests</h3>
        </div>
        <div class="p-6">
            <ul class="divide-y divide-gray-200">
                @forelse($recentRequests as $request)
                    <li class="py-4 flex items-center justify-between">
                        <div class="flex items-center">
                            @if($request['type'] === 'leave')
                                <div class="p-2 rounded-md bg-blue-100 text-blue-500">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            @elseif($request['type'] === 'reimbursement')
                                <div class="p-2 rounded-md bg-green-100 text-green-500">
                                    <i class="fas fa-receipt"></i>
                                </div>
                            @elseif($request['type'] === 'overtime')
                                <div class="p-2 rounded-md bg-purple-100 text-purple-500">
                                    <i class="fas fa-clock"></i>
                                </div>
                            @elseif($request['type'] === 'travel')
                                <div class="p-2 rounded-md bg-orange-100 text-orange-500">
                                    <i class="fas fa-plane"></i>
                                </div>
                            @endif
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">{{ $request['title'] }}</p>
                                <p class="text-sm text-gray-500">{{ $request['date'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center">
                            @if($request['status'] === 'pending')
                                <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @elseif($request['status'] === 'approved')
                                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-800">Approved</span>
                            @elseif($request['status'] === 'rejected')
                                <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-800">Rejected</span>
                            @endif
                            <a href="{{ $request['url'] }}" class="ml-4 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-center text-gray-500">No recent requests found.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Approvals Section (for approvers and admins) -->
    @if(Auth::user()->role === 'approver' || Auth::user()->role === 'admin')
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Pending Approvals</h3>
            </div>
            <div class="p-6">
                <ul class="divide-y divide-gray-200">
                    @forelse($pendingApprovals as $approval)
                        <li class="py-4 flex items-center justify-between">
                            <div class="flex items-center">
                                @if($approval['type'] === 'leave')
                                    <div class="p-2 rounded-md bg-blue-100 text-blue-500">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                @elseif($approval['type'] === 'reimbursement')
                                    <div class="p-2 rounded-md bg-green-100 text-green-500">
                                        <i class="fas fa-receipt"></i>
                                    </div>
                                @elseif($approval['type'] === 'overtime')
                                    <div class="p-2 rounded-md bg-purple-100 text-purple-500">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                @elseif($approval['type'] === 'travel')
                                    <div class="p-2 rounded-md bg-orange-100 text-orange-500">
                                        <i class="fas fa-plane"></i>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $approval['title'] }}</p>
                                    <p class="text-sm text-gray-500">{{ $approval['employee'] }} - {{ $approval['date'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <a href="{{ $approval['url'] }}" class="px-3 py-1 text-xs rounded-full bg-blue-500 text-white hover:bg-blue-600">
                                    Review
                                </a>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-gray-500">No pending approvals.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    @endif
@endsection