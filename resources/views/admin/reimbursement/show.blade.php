@extends('components.admin.layout.layout-admin')

@section('header', 'Reimbursement Requests')
@section('subtitle', 'Manage your reimbursement claims')

@section('content')
<div class="max-w-4xl mx-auto">
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-neutral-700 hover:text-primary-600">
                    <i class="mr-2 fas fa-home"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                    <a href="{{ route('admin.reimbursements.index') }}"
                        class="text-sm font-medium text-neutral-700 hover:text-primary-600">Reimbursement Requests</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                    <span class="text-sm font-medium text-neutral-500">Klaim #RY{{ $reimbursement->id }}</span>
                </div>
            </li>
        </ol>
    </nav>
    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Left Column - Main Details -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Request Header -->
            <div class="overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 bg-gradient-to-r from-primary-600 to-primary-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-white">Reimbursement Claim #RY{{ $reimbursement->id }}
                            </h1>
                            <p class="text-sm text-primary-100">Submitted on {{ $reimbursement->created_at->format('M d,
                                Y \a\t H:i') }}</p>
                        </div>
                        <div class="text-right">
                            @if($reimbursement->status_1 === 'rejected' || $reimbursement->status_2 === 'rejected')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                <i class="mt-1 mr-1 fas fa-times-circle"></i>
                                Rejected
                            </span>
                            @elseif($reimbursement->status_1 === 'approved' && $reimbursement->status_2 === 'approved')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                <i class="mt-1 mr-1 fas fa-check-circle"></i>
                                Approved
                            </span>
                            @elseif($reimbursement->status_1 === 'pending' || $reimbursement->status_2 === 'pending')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                <i class="mt-1 mr-1 fas fa-clock"></i>
                                {{ $reimbursement->status_1 === 'pending' ? 'Pending' : 'In Progress' }} Review
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Reimbursement Details -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h2 class="text-lg font-bold text-neutral-900">Claim Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Email</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200"
                                x-data="{ tooltip: false }">
                                <i class="flex-shrink-0 mr-3 fas fa-envelope text-primary-600"></i>
                                <span class="font-medium truncate text-neutral-900" @mouseenter="tooltip = true"
                                    @mouseleave="tooltip = false" x-tooltip="'{{ $reimbursement->employee->email }}'">
                                    {{ $reimbursement->employee->email }}
                                </span>
                                <!-- Tooltip -->
                                <div x-show="tooltip" x-cloak
                                    class="absolute px-3 py-2 -mt-12 text-sm text-white bg-gray-900 rounded-lg shadow-lg">
                                    {{ $reimbursement->employee->email }}
                                </div>
                            </div>
                        </div>
                        <!-- Approver -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Approver 1</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-user-check text-info-600"></i>
                                <span class="font-medium text-neutral-900">{{ $reimbursement->approver->name ?? 'N/A'
                                    }}</span>
                            </div>
                        </div>
                        <!-- Total (was Amount) -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Total Amount</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-dollar-sign text-primary-600"></i>
                                <span class="font-medium text-neutral-900">Rp {{ number_format($reimbursement->total, 0,
                                    ',', '.') }}</span>
                            </div>
                        </div>
                        <!-- Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Date of Expense</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-day text-secondary-600"></i>
                                <span class="font-medium text-neutral-900">{{
                                    \Carbon\Carbon::parse($reimbursement->date)->format('l, M d, Y') }}</span>
                            </div>
                        </div>
                        <!-- Customer -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Customer</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-users text-info-600"></i>
                                <span class="font-medium text-neutral-900">{{ $reimbursement->customer ?? 'N/A'
                                    }}</span>
                            </div>
                        </div>
                        <!-- Invoice Path (was Attachment) -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Invoice</label>
                            <div class="p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($reimbursement->invoice_path)
                                <a href="{{ Storage::url($reimbursement->invoice_path) }}" target="_blank"
                                    class="flex items-center font-medium text-primary-600 hover:text-primary-800">
                                    <i class="mr-2 fas fa-file-alt"></i>
                                    View Invoice ({{ pathinfo($reimbursement->invoice_path, PATHINFO_EXTENSION) }})
                                </a>
                                @else
                                <p class="text-neutral-500">No invoice provided.</p>
                                @endif
                            </div>
                        </div>
                        <!-- Status -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status - Approver 1</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($reimbursement->status_1 === 'pending')
                                <i class="mr-3 fas fa-clock text-warning-600"></i>
                                <span class="font-medium text-warning-800">Pending Review</span>
                                @elseif($reimbursement->status_1 === 'approved')
                                <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                <span class="font-medium text-success-800">Approved</span>
                                @elseif($reimbursement->status_1 === 'rejected')
                                <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                <span class="font-medium text-error-800">Rejected</span>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status - Approver 2</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                @if($reimbursement->status_2 === 'pending')
                                <i class="mr-3 fas fa-clock text-warning-600"></i>
                                <span class="font-medium text-warning-800">Pending Review</span>
                                @elseif($reimbursement->status_2 === 'approved')
                                <i class="mr-3 fas fa-check-circle text-success-600"></i>
                                <span class="font-medium text-success-800">Approved</span>
                                @elseif($reimbursement->status_2 === 'rejected')
                                <i class="mr-3 fas fa-times-circle text-error-600"></i>
                                <span class="font-medium text-error-800">Rejected</span>
                                @endif
                            </div>
                        </div>
                        <!-- Note -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Note - Approver 1</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                <span class="text-neutral-900">{{ $reimbursement->note_1 ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Note - Approver 2</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                <span class="text-neutral-900">{{ $reimbursement->note_2 ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Right Column - Sidebar -->
        <div class="space-y-6">
            <!-- Actions -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                </div>
                <div class="p-6 space-y-3">
                    @if(Auth::id() === $reimbursement->employee_id && ($reimbursement->status_1 === 'pending'))
                    <a href="{{ route('admin.reimbursements.edit', $reimbursement->id) }}"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                        <i class="mr-2 fas fa-edit"></i>
                        Edit Request
                    </a>
                    <button
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg delete-reimbursement-btn bg-error-600 hover:bg-error-700"
                        data-reimbursement-id="{{ $reimbursement->id }}"
                        data-reimbursement-name="reimbursement Request #{{ $reimbursement->id }}" title="Delete">
                        <i class="mr-2 fas fa-trash"></i>
                        Delete Request
                    </button>
                    <form id="delete-form-{{ $reimbursement->id }}"
                        action="{{ route('admin.reimbursements.destroy', $reimbursement->id) }}" method="POST"
                        style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif

                    @if ($reimbursement->status_1 == 'approved' && $reimbursement->status_2 == 'approved' &&
                    $reimbursement->employee_id == Auth::id())
                    <button
                        onclick="window.location.href='{{ route('admin.reimbursements.exportPdf', $reimbursement->id) }}'"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-secondary-600 hover:bg-secondary-700">
                        <i class="mr-2 fas fa-print"></i>
                        Print Request
                    </button>
                    @endif

                    <a href="{{ route('admin.reimbursements.index') }}"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-neutral-600 hover:bg-neutral-700">
                        <i class="mr-2 fas fa-arrow-left"></i>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('partial-modal')
{{-- Updated modal to handle reimbursement requests instead of users --}}
<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-opacity-75 " onclick="closeDeleteModal()"></div>
        <div
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>

            <div class="text-center">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">Delete reimbursement Request</h3>
                <p class="mb-6 text-sm text-gray-500">
                    Are you sure you want to delete <span id="reimbursementName"
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
                    class="z-40 px-4 py-2 text-sm font-medium text-white transition-colors bg-red-600 border border-transparent rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
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
    let reimbursementIdToDelete = null;

// Initialize delete functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDeleteFunctionality();
});

function initializeDeleteFunctionality() {
    // Add event listeners to all delete buttons
    const deleteButtons = document.querySelectorAll('.delete-reimbursement-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reimbursementId = this.getAttribute('data-reimbursement-id');
            const reimbursementName = this.getAttribute('data-reimbursement-name');
            confirmDelete(reimbursementId, reimbursementName);
        });
    });

    // Add event listener for confirm delete button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', executeDelete);
    }

    // Add event listener for cancel button
    const cancelButton = document.getElementById('cancelDeleteButton');
    if (cancelButton) {
        cancelButton.addEventListener('click', closeDeleteModal);
    }
}

function confirmDelete(reimbursementId, reimbursementName) {
    reimbursementIdToDelete = reimbursementId;
    document.getElementById('reimbursementName').textContent = reimbursementName;
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeDeleteModal() {
    reimbursementIdToDelete = null;
    document.getElementById('deleteConfirmModal').classList.add('hidden');
    document.body.style.overflow = 'auto'; // Restore scrolling
}

function executeDelete() {
    if (!reimbursementIdToDelete) return;

    // Show loading state
    const deleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteText = document.getElementById('deleteButtonText');
    const deleteSpinner = document.getElementById('deleteSpinner');
    const cancelButton = document.getElementById('cancelDeleteButton');

    cancelButton.disabled = true;
    deleteBtn.disabled = true;
    deleteText.textContent = 'Deleting...';
    deleteSpinner.classList.remove('hidden');

    // Submit the form
    document.getElementById(`delete-form-${reimbursementIdToDelete}`).submit();
}

// Close modal when pressing Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endpush
