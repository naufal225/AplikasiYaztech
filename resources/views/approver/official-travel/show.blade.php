@extends('components.approver.layout.layout-approver')

@section('header', 'Official Travel Detail')
@section('subtitle', '')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Main Grid: 2 Columns on Large Screens -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Left Column - Main Details -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Header Card -->
            <div class="overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-white">
                                Official Travel Request #TY{{ $officialTravel->id }}
                            </h1>
                            <p class="text-sm text-primary-100">
                                Submitted on {{ $officialTravel->created_at->format('M d, Y \a\t H:i') }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($officialTravel->status_1 === 'rejected' || $officialTravel->status_2 === 'rejected')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                <i class="mt-1 mr-1 fas fa-times-circle"></i>
                                Rejected
                            </span>
                            @elseif($officialTravel->status_1 === 'approved' && $officialTravel->status_2 ===
                            'approved')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                <i class="mt-1 mr-1 fas fa-check-circle"></i>
                                Approved
                            </span>
                            @elseif($officialTravel->status_1 === 'pending' || $officialTravel->status_2 === 'pending')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                <i class="mt-1 mr-1 fas fa-clock"></i>
                                {{ $officialTravel->status_1 === 'pending' ? 'Pending' : 'In Progress' }} Review
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Travel Details Card -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h2 class="text-lg font-bold text-neutral-900">Travel Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Employee Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Employee Email</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-envelope text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $officialTravel->employee->email }}</span>
                            </div>
                        </div>

                        <!-- Team Lead -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Team Lead</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-user-check text-info-600"></i>
                                <span class="font-medium text-neutral-900">{{ $officialTravel->approver->name ?? 'N/A'
                                    }}</span>
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Start Date</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-alt text-secondary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $officialTravel->date_start->format('l, M
                                    d, Y') }}</span>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">End Date</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-alt text-secondary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $officialTravel->date_end->format('l, M d,
                                    Y') }}</span>
                            </div>
                        </div>

                        <!-- Total Days -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Total Days</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-day text-primary-600"></i>
                                <span class="font-medium text-neutral-900">
                                    {{ $officialTravel->total }} day{{ $officialTravel->total > 1 ? 's' : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Customer -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Customer</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-users text-info-600"></i>
                                <span class="font-medium text-neutral-900">{{ $officialTravel->customer ?? 'N/A'
                                    }}</span>
                            </div>
                        </div>

                        <!-- Status - Team Lead -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status - Team Lead</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($officialTravel->status_1 === 'pending')
                                <i class="mr-3 fas fa-clock text-warning-600"></i>
                                <span class="font-medium text-warning-800">Pending Review</span>
                                @elseif($officialTravel->status_1 === 'approved')
                                <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                <span class="font-medium text-success-800">Approved</span>
                                @elseif($officialTravel->status_1 === 'rejected')
                                <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                <span class="font-medium text-error-800">Rejected</span>
                                @endif
                            </div>
                        </div>

                        <!-- Status - Manager -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status - Manager</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($officialTravel->status_2 === 'pending')
                                <i class="mr-3 fas fa-clock text-warning-600"></i>
                                <span class="font-medium text-warning-800">Pending Review</span>
                                @elseif($officialTravel->status_2 === 'approved')
                                <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                <span class="font-medium text-success-800">Approved</span>
                                @elseif($officialTravel->status_2 === 'rejected')
                                <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                <span class="font-medium text-error-800">Rejected</span>
                                @endif
                            </div>
                        </div>

                        <!-- Note - Team Lead -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Note - Team Lead</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                <span class="text-neutral-900">{{ $officialTravel->note_1 ?? '-' }}</span>
                            </div>
                        </div>

                        <!-- Note - Manager -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Note - Manager</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                <span class="text-neutral-900">{{ $officialTravel->note_2 ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
            <!-- Actions Card -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    @if(Auth::id() === $officialTravel->employee_id && $officialTravel->status_1 === 'pending')
                    <a href="{{ route('approver.official-travels.edit', $officialTravel->id) }}"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                        <i class="mr-2 fas fa-edit"></i>
                        Edit Request
                    </a>

                    <button
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg delete-officialTravel-btn bg-error-600 hover:bg-error-700"
                        data-officialTravel-id="{{ $officialTravel->id }}"
                        data-officialTravel-name="Official Travel Request #{{ $officialTravel->id }}" title="Delete">
                        <i class="mr-2 fas fa-trash"></i>
                        Delete Request
                    </button>

                    <form id="delete-form-{{ $officialTravel->id }}"
                        action="{{ route('approver.official-travels.destroy', $officialTravel->id) }}" method="POST"
                        style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif

                    <a href="{{ route('approver.official-travels.index') }}"
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

            <!-- Approval/Rejection Form -->
            @if($officialTravel->final_status === 'pending' && $officialTravel->status_1 === 'pending')
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-bold text-neutral-900">Review Request</h3>
                </div>
                <div class="p-6">
                    <form id="approvalForm" method="POST"
                        action="{{ route('approver.official-travels.update', $officialTravel) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_1" id="status_1" value="" />

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
                    <p class="text-neutral-700">You have already reviewed this request.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('partial-modal')
<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>

            <div class="text-center">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">Delete Official Travel Request</h3>
                <p class="mb-6 text-sm text-gray-500">
                    Are you sure you want to delete <span id="officialTravelName"
                        class="font-medium text-gray-900"></span>?
                    This action cannot be undone.
                </p>
            </div>

            <div class="flex justify-center space-x-3">
                <button type="button" id="cancelDeleteButton"
                    class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg cancel-delete-btn hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteBtn"
                    class="px-4 py-2 text-sm font-medium text-white transition-colors bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <span id="deleteButtonText">Delete</span>
                    <svg id="deleteSpinner" class="hidden w-4 h-4 ml-2 -mr-1 text-white animate-spin" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initializeDeleteFunctionality();
    });

    let officialTravelIdToDelete = null;

    function initializeDeleteFunctionality() {
        const deleteButtons = document.querySelectorAll('.delete-officialTravel-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const officialTravelId = this.getAttribute('data-officialTravel-id');
                const officialTravelName = this.getAttribute('data-officialTravel-name');
                confirmDelete(officialTravelId, officialTravelName);
            });
        });

        document.getElementById('cancelDeleteButton')?.addEventListener('click', closeDeleteModal);
        document.getElementById('confirmDeleteBtn')?.addEventListener('click', executeDelete);
    }

    function confirmDelete(officialTravelId, officialTravelName) {
        officialTravelIdToDelete = officialTravelId;
        document.getElementById('officialTravelName').textContent = officialTravelName;
        document.getElementById('deleteConfirmModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        officialTravelIdToDelete = null;
        document.getElementById('deleteConfirmModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function executeDelete() {
        if (!officialTravelIdToDelete) return;

        const deleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteText = document.getElementById('deleteButtonText');
        const deleteSpinner = document.getElementById('deleteSpinner');
        const cancelButton = document.getElementById('cancelDeleteButton');

        cancelButton.disabled = true;
        deleteBtn.disabled = true;
        deleteText.textContent = 'Deleting...';
        deleteSpinner.classList.remove('hidden');

        const form = document.getElementById(`delete-form-${officialTravelIdToDelete}`);
        if (form) {
            form.submit();
        } else {
            console.error(`Delete form not found: delete-form-${officialTravelIdToDelete}`);
            alert('Error: Could not find the delete form.');
            closeDeleteModal();
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>
@endpush
