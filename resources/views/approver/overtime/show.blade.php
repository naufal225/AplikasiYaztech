@extends('components.approver.layout.layout-approver')
@section('header', 'Overtime Detail')
@section('subtitle', '')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Left Column - Main Details -->
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                    @if($errors->any())
                    <div class="flex items-start p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-red-800">Please correct the following errors:</h4>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-white">Overtime Request #{{ $overtime->id }}</h1>
                            <p class="text-sm text-primary-100">Submitted on {{
                                Carbon\Carbon::parse($overtime->created_at)->format('M d, Y \a\t H:i') }}</p>
                        </div>
                        <div class="text-right">
                            @if($overtime->final_status === 'pending')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                <i class="mr-1 fas fa-clock"></i>
                                Pending Review
                            </span>
                            @elseif($overtime->final_status === 'approved')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                <i class="mr-1 fas fa-check-circle"></i>
                                Approved
                            </span>
                            @elseif($overtime->final_status === 'rejected')
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
            <!-- overtime Details -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h2 class="text-lg font-bold text-neutral-900">Overtime Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Email</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-envelope text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $overtime->employee->email ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <!-- Approver -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Approver</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-user-check text-info-600"></i>
                                <span class="font-medium text-neutral-900">{{ $overtime->approver->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <!-- Start Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Start Date</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-alt text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{
                                    \Carbon\Carbon::parse($overtime->date_start)->format('l, M d, Y') }}</span>
                            </div>
                        </div>
                        <!-- End Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">End Date</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-alt text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{
                                    \Carbon\Carbon::parse($overtime->date_end)->format('l, M d, Y') }}</span>
                            </div>
                        </div>
                        <!-- Duration -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Duration</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-clock text-secondary-600"></i>
                                <span class="font-medium text-neutral-900">
                                    {{ (int)
                                    \Carbon\Carbon::parse($overtime->date_start)->diffInDays(\Carbon\Carbon::parse($overtime->date_end,
                                    ), false) + 1 }}
                                    {{ (int)
                                    \Carbon\Carbon::parse($overtime->date_start)->diffInDays(\Carbon\Carbon::parse($overtime->date_end,
                                    ), false) + 1 === 1 ? 'day' : 'days' }}
                                </span>
                            </div>
                        </div>
                        <!-- final_status -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status 1 - Team Lead</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($overtime->status_1 === 'pending')
                                <i class="mr-3 fas fa-clock text-warning-600"></i>
                                <span class="font-medium text-warning-800">Pending Review</span>
                                @elseif($overtime->status_1 === 'approved')
                                <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                <span class="font-medium text-success-800">Approved</span>
                                @elseif($overtime->status_1 === 'rejected')
                                <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                <span class="font-medium text-error-800">Rejected</span>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Reason for overtime</label>
                            <div class="p-4 border rounded-lg bg-neutral-50 border-neutral-200">
                                <p class="leading-relaxed text-neutral-900">{{ $overtime->reason }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status 2 - Manager</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($overtime->status_2 === 'pending')
                                <i class="mr-3 fas fa-clock text-warning-600"></i>
                                <span class="font-medium text-warning-800">Pending Review</span>
                                @elseif($overtime->status_2 === 'approved')
                                <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                <span class="font-medium text-success-800">Approved</span>
                                @elseif($overtime->status_2 === 'rejected')
                                <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                <span class="font-medium text-error-800">Rejected</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- Reason -->


                    <!-- Added approval/rejection notes section if final_status is not pending -->
                    @if($overtime->final_status !== 'pending' && !empty($overtime->approval_notes))
                    <div class="mt-6 space-y-2">
                        <label class="text-sm font-semibold text-neutral-700">
                            @if($overtime->final_status === 'approved')
                            Approval Notes
                            @else
                            Rejection Notes
                            @endif
                        </label>
                        <div class="p-4 border rounded-lg
                            @if($overtime->final_status === 'approved')
                                bg-success-50 border-success-200
                            @else
                                bg-error-50 border-error-200
                            @endif">
                            <p class="leading-relaxed
                                @if($overtime->final_status === 'approved')
                                    text-success-900
                                @else
                                    text-error-900
                                @endif">{{ $overtime->approval_notes }}</p>
                        </div>
                    </div>
                    @endif
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
                    <a href="{{ route('approver.overtimes.index') }}"
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

            <!-- Added approval/rejection form for pending requests -->
            @if($overtime->final_status === 'pending' && $overtime->status_1 == 'pending')
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-bold text-neutral-900">Review Request</h3>
                </div>
                <div class="p-6">
                    <form id="approvalForm" method="POST" action="{{ route('approver.overtimes.update', $overtime) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_1" id="status_1" value="">

                        <div class="space-y-4">
                            <div>
                                <label for="approval_notes" class="block mb-2 text-sm font-semibold text-neutral-700">
                                    Notes <span class="text-neutral-500">(Optional)</span>
                                </label>
                                <textarea name="note_1" id="approval_notes" rows="4"
                                    class="w-full px-3 py-2 border rounded-lg resize-none border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    placeholder="Add any comments or reasons for your decision..."></textarea>
                            </div>

                            <div class="flex flex-col space-y-3">
                                <button type="button" onclick="submitApproval('approved')"
                                    class="flex items-center justify-center w-full px-4 py-3 font-semibold text-white transition-colors duration-200 rounded-lg bg-success-600 hover:bg-success-700 focus:ring-2 focus:ring-success-500 focus:ring-offset-2">
                                    <i class="mr-2 fas fa-check"></i>
                                    Approve Request
                                </button>

                                <button type="button" onclick="submitApproval('rejected')"
                                    class="flex items-center justify-center w-full px-4 py-3 font-semibold text-white transition-colors duration-200 rounded-lg bg-error-600 hover:bg-error-700 focus:ring-2 focus:ring-error-500 focus:ring-offset-2">
                                    <i class="mr-2 fas fa-times"></i>
                                    Reject Request
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-bold text-neutral-900">Review Request</h3>
                </div>
                <div class="p-6">
                    <h1>You have reviewed this request</h1>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')

<!-- Added JavaScript for form submission with confirmation -->
<script>
    function submitApproval(action) {
        const actionText = action === 'approved' ? 'approved' : 'rejected';
        // const confirmMessage = `Are you sure you want to ${actionText} this overtime request?`;

        // if (confirm(confirmMessage)) {
        document.getElementById('status_1').value = action;
        document.getElementById('approvalForm').submit();
        // }
    }
</script>

@endpush
@endsection
