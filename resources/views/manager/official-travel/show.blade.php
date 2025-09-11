@extends('components.manager.layout.layout-manager')
@section('header', 'Official Travel Detail')
@section('subtitle', '')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Main Grid -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Left Column - Main Details -->
        <div class="space-y-6 lg:col-span-2">
            <!-- Header -->
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
                            <h1 class="text-xl font-bold text-white">Official Travel Request #{{ $officialTravel->id }}
                            </h1>
                            <p class="text-sm text-primary-100">
                                Submitted on {{ \Carbon\Carbon::parse($officialTravel->created_at)->format('M d, Y \a\t
                                H:i') }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($officialTravel->status_2 === 'pending')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                <i class="mr-1 fas fa-clock"></i>
                                Pending Review
                            </span>
                            @elseif($officialTravel->status_2 === 'approved')
                            <span
                                class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                <i class="mr-1 fas fa-check-circle"></i>
                                Approved
                            </span>
                            @elseif($officialTravel->status_2 === 'rejected')
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

            <!-- Official Travel Details -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h2 class="text-lg font-bold text-neutral-900">Official Travel Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Email -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Email</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-envelope text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{ $officialTravel->employee->email ?? 'N/A'
                                    }}</span>
                            </div>
                        </div>

                        <!-- Approver (Team Lead) -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Approver</label>
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
                                <i class="mr-3 fas fa-calendar-alt text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{
                                    \Carbon\Carbon::parse($officialTravel->date_start)->format('l, M d, Y') }}</span>
                            </div>
                        </div>

                        <!-- End Date -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">End Date</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-calendar-alt text-primary-600"></i>
                                <span class="font-medium text-neutral-900">{{
                                    \Carbon\Carbon::parse($officialTravel->date_end)->format('l, M d, Y') }}</span>
                            </div>
                        </div>

                        <!-- Duration -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Duration</label>
                            <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                <i class="mr-3 fas fa-clock text-secondary-600"></i>
                                @php
                                $duration =
                                \Carbon\Carbon::parse($officialTravel->date_start)->diffInDays(\Carbon\Carbon::parse($officialTravel->date_end))
                                + 1;
                                @endphp
                                <span class="font-medium text-neutral-900">
                                    {{ $duration }} {{ $duration === 1 ? 'day' : 'days' }}
                                </span>
                            </div>
                        </div>

                        <!-- Status 1 - Team Lead -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status 1 - Team Lead</label>
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


                        <!-- Status 2 - Manager -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-neutral-700">Status 2 - Manager</label>
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

                        <!-- Approval/Rejection Notes dari Team Lead -->
                        @if($officialTravel->status_1 === 'approved' && $officialTravel->note_1)
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-semibold text-neutral-700">Approval Notes (Team Lead)</label>
                            <div class="p-4 border rounded-lg bg-success-50 border-success-200">
                                <p class="leading-relaxed text-success-900">{{ $officialTravel->note_1 }}</p>
                            </div>
                        </div>
                        @elseif($officialTravel->status_1 === 'rejected' && $officialTravel->note_1)
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-semibold text-neutral-700">Rejection Notes (Team Lead)</label>
                            <div class="p-4 border rounded-lg bg-error-50 border-error-200">
                                <p class="leading-relaxed text-error-900">{{ $officialTravel->note_1 }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Approval/Rejection Notes dari Manager -->
                        @if($officialTravel->status_2 !== 'pending' && $officialTravel->note_2)
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-semibold text-neutral-700">
                                {{ $officialTravel->status_2 === 'approved' ? 'Approval Notes (Manager)' : 'Rejection
                                Notes (Manager)' }}
                            </label>
                            <div
                                class="p-4 border rounded-lg
                                    {{ $officialTravel->status_2 === 'approved' ? 'bg-success-50 border-success-200' : 'bg-error-50 border-error-200' }}">
                                <p
                                    class="leading-relaxed
                                        {{ $officialTravel->status_2 === 'approved' ? 'text-success-900' : 'text-error-900' }}">
                                    {{ $officialTravel->note_2 }}
                                </p>
                            </div>
                        </div>
                        @endif

                        <!-- Reason -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-semibold text-neutral-700">Reason for Official Travel</label>
                            <div class="p-4 border rounded-lg bg-neutral-50 border-neutral-200">
                                <p class="leading-relaxed text-neutral-900">{{ $officialTravel->reason }}</p>
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
                    @if(Auth::id() === $officialTravel->employee_id && $officialTravel->status_1 === 'pending')
                    <a href="{{ route('manager.official-travels.edit', $officialTravel->id) }}"
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
                    @endif

                    <button onclick="window.print()"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-secondary-600 hover:bg-secondary-700">
                        <i class="mr-2 fas fa-print"></i>
                        Print Request
                    </button>

                    <a href="{{ route('manager.official-travels.index') }}"
                        class="flex items-center justify-center w-full px-4 py-2 font-semibold text-white transition-colors duration-200 rounded-lg bg-neutral-600 hover:bg-neutral-700">
                        <i class="mr-2 fas fa-arrow-left"></i>
                        Back to List
                    </a>

                </div>
            </div>

            <!-- Manager Review -->
            <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
                <div class="px-6 py-4 border-b border-neutral-200">
                    <h3 class="text-lg font-bold text-neutral-900">Your Review</h3>
                </div>
                <div class="p-6">
                    @if($officialTravel->status_2 === 'approved')
                    <div class="p-4 text-center text-green-800 rounded-lg bg-green-50">
                        <i class="mb-2 text-xl fas fa-check-circle"></i>
                        <p class="font-medium">You Approved This Request</p>
                        @if($officialTravel->note_2)
                        <p class="mt-2 text-sm"><strong>Notes:</strong> {{ $officialTravel->note_2 }}</p>
                        @endif
                    </div>
                    @elseif($officialTravel->status_2 === 'rejected')
                    <div class="p-4 text-center text-red-800 rounded-lg bg-red-50">
                        <i class="mb-2 text-xl fas fa-times-circle"></i>
                        <p class="font-medium">You Rejected This Request</p>
                        @if($officialTravel->note_2)
                        <p class="mt-2 text-sm"><strong>Reason:</strong> {{ $officialTravel->note_2 }}</p>
                        @else
                        <p class="text-sm">No reason provided.</p>
                        @endif
                    </div>
                    @elseif($officialTravel->status_1 === 'approved' && $officialTravel->status_2 === 'pending')
                    <form id="approvalForm" method="POST"
                        action="{{ route('manager.official-travels.update', $officialTravel) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status_2" id="status_2" value="" />

                        <div class="space-y-4">
                            <div>
                                <label for="approval_notes" class="block mb-2 text-sm font-semibold text-neutral-700">
                                    Notes <span class="text-neutral-500">(Optional)</span>
                                </label>
                                <textarea name="note_2" id="approval_notes" rows="4"
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
                    @elseif($officialTravel->status_1 === 'pending')
                    <div class="p-4 text-center text-yellow-800 rounded-lg bg-yellow-50">
                        <i class="mb-2 text-xl fas fa-clock"></i>
                        <p class="font-medium">Waiting for Team Lead</p>
                        <p class="text-sm">Manager review will be available after Team Lead approval.</p>
                    </div>
                    @elseif($officialTravel->status_1 === 'rejected')
                    <div class="p-4 text-center text-red-800 rounded-lg bg-red-50">
                        <i class="mb-2 text-xl fas fa-times-circle"></i>
                        <p class="font-medium">Request Rejected by Team Lead</p>
                        <p class="text-sm">This request cannot be reviewed by manager.</p>
                    </div>
                    @else
                    <div class="p-4 text-center text-gray-800 rounded-lg bg-gray-50">
                        <i class="mb-2 text-xl fas fa-info-circle"></i>
                        <p class="font-medium">Review Completed</p>
                        <p class="text-sm">This request has been reviewed.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="delete-form-{{ $officialTravel->id }}"
    action="{{ route('manager.official-travels.destroy', $officialTravel->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('partial-modal')
<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50" onclick="closeDeleteModal()"></div>
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
                    class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
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
    let officialTravelIdToDelete = null;

    // Submit Approval
    function submitApproval(action) {

            document.getElementById('status_2').value = action;
            document.getElementById('approvalForm').submit();
    }

    // Initialize Delete Functionality
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.delete-officialTravel-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-officialTravel-id');
                const name = this.getAttribute('data-officialTravel-name');
                confirmDelete(id, name);
            });
        });

        document.getElementById('cancelDeleteButton')?.addEventListener('click', closeDeleteModal);
        document.getElementById('confirmDeleteBtn')?.addEventListener('click', executeDelete);
    });

    function confirmDelete(id, name) {
        officialTravelIdToDelete = id;
        document.getElementById('officialTravelName').textContent = name;
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

        const btn = document.getElementById('confirmDeleteBtn');
        const text = document.getElementById('deleteButtonText');
        const spinner = document.getElementById('deleteSpinner');
        const cancel = document.getElementById('cancelDeleteButton');

        cancel.disabled = true;
        btn.disabled = true;
        text.textContent = 'Deleting...';
        spinner.classList.remove('hidden');

        document.getElementById(`delete-form-${officialTravelIdToDelete}`).submit();
    }

    // Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeDeleteModal();
    });
</script>
@endpush