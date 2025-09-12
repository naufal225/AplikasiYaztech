@extends('components.manager.layout.layout-manager')
@section('header', 'Leave Detail')
@section('subtitle', '')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Left Column - Main Details -->
        <div class="space-y-6 lg:col-span-2">
            <div class="relative overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
                <!-- Overlay Checklist -->
                @if($leave->status_1 === 'approved')
                <div class="absolute inset-0 z-10 flex items-center justify-center bg-white/70 rounded-xl">
                    <i class="text-5xl text-green-500 bg-white rounded-full fas fa-check-circle drop-shadow-lg"></i>
                </div>
                @endif

                <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-white">Leave Request #LY{{ $leave->id }}</h1>
                            <p class="text-sm text-primary-100">Submitted on {{
                                Carbon\Carbon::parse($leave->created_at)->format('M d, Y \a\t H:i') }}</p>
                        </div>
                        <div class="text-right">
                            @if($leave->status_1 === 'rejected')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                <i class="mt-1 mr-1 fas fa-times-circle"></i>
                                Rejected
                            </span>
                            @elseif($leave->status_1 === 'approved')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                <i class="mt-1 mr-1 fas fa-check-circle"></i>
                                Approved
                            </span>
                            @elseif($leave->status_1 === 'pending')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                <i class="mt-1 mr-1 fas fa-clock"></i>
                                {{ $leave->status_1 === 'pending' ? 'Pending' : 'In Progress' }} Review
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
                                <span class="font-medium text-neutral-900">{{ $leave->employee->email }}</span>
                            </div>
                        </div>

                        <!-- Division -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Division</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-building text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $leave->employee->division->name ?? 'N/A'
                                    }}</span>
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Start Date</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-alt text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{
                                    \Carbon\Carbon::parse($leave->date_start)->format('l, M d, Y') }}</span>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">End Date</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-alt text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{
                                    \Carbon\Carbon::parse($leave->date_end)->format('l, M d, Y') }}</span>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Duration</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-clock text-secondary-600"></i>
                                <span class="font-medium text-neutral-900">
                                    {{ (int)
                                    \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end,
                                    ), false) + 1 }}
                                    {{ (int)
                                    \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end,
                                    ), false) + 1 === 1 ? 'day' : 'days' }}
                                </span>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Reason for Leave</label>
                            <div class="p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <p class="leading-relaxed font text-neutral-900">{{ $leave->reason }}</p>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($leave->status_1 === 'pending')
                                <i class="mr-3 fas fa-clock text-warning-600"></i>
                                <span class="font-medium text-warning-800">Pending Review</span>
                                @elseif($leave->status_1 === 'approved')
                                <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                <span class="font-medium text-success-800">Approved</span>
                                @elseif($leave->status_1 === 'rejected')
                                <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                <span class="font-medium text-error-800">Rejected</span>
                                @endif
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Note</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                <span class="text-neutral-900">{{ $leave->note_1 ?? '-' }}</span>
                            </div>
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
                    <!-- Edit & Delete hanya muncul jika status masih pending dan user adalah pemilik -->
                    @if(Auth::id() === $leave->employee_id && $leave->status_1 === 'pending')
                    <a href="{{ route('manager.leaves.edit', $leave->id) }}"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                        <i class="mr-2 fas fa-edit"></i>
                        Edit Request
                    </a>

                    <button
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg delete-leave-btn bg-error-600 hover:bg-error-700"
                        data-leave-id="{{ $leave->id }}" data-leave-name="Leave Request #{{ $leave->id }}"
                        title="Delete">
                        <i class="mr-2 fas fa-trash"></i>
                        Delete Request
                    </button>

                    <form id="delete-form-{{ $leave->id }}" action="{{ route('manager.leaves.destroy', $leave->id) }}"
                        method="POST" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif

                    <!-- Print -->
                    <button onclick="window.location.href='{{ route('manager.leaves.exportPdf', $leave->id) }}'"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-secondary-600 hover:bg-secondary-700">
                        <i class="mr-2 fas fa-print"></i>
                        Print Request
                    </button>

                    <!-- Back to List -->
                    <a href="{{ route('manager.leaves.index') }}"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-neutral-600 hover:bg-neutral-700">
                        <i class="mr-2 fas fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>
            </div>

            <!-- Added approval/rejection form for pending requests -->
            @if($leave->status_1 === 'pending')
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-bold text-neutral-900">Review Request</h3>
                </div>
                <div class="p-6">
                    <form id="approvalForm" method="POST" action="{{ route('manager.leaves.update', $leave) }}">
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
        // const confirmMessage = `Are you sure you want to ${actionText} this leave request?`;

        // if (confirm(confirmMessage)) {
        document.getElementById('status_1').value = action;
        document.getElementById('approvalForm').submit();
        // }
    }
</script>

@endpush
@endsection
