@extends('components.admin.layout.layout-admin')

@section('header', 'Update User')
@section('subtitle', 'Update User data')

@section('content')
<!-- Update User Content -->
<main class="relative z-10 flex-1 p-6 overflow-x-hidden overflow-y-auto bg-gray-50">

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Update New User</h1>
                <p class="mt-2 text-sm text-gray-600">Update user record in the system</p>
            </div>

            <!-- Back Button -->
            <a href="{{ route('admin.users.index') }}"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-gray-100 rounded-lg hover:bg-gray-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="max-w-2xl mx-auto">
        <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
            <!-- Form Header -->
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">User Information</h3>
                <p class="mt-1 text-sm text-gray-600">Please fill in the user details below</p>
            </div>

            <!-- Form Content -->
            <div class="p-6">
                <!-- Success Message -->
                @if(session('success'))
                <div class="flex items-center p-4 mb-6 border border-green-200 bg-green-50 rounded-xl">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                <!-- Error Messages -->
                @if($errors->any())
                <div class="flex items-start p-4 mb-6 border border-red-200 bg-red-50 rounded-xl">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                <!-- user Form -->
                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6" id="userForm">
                    @csrf
                    @method('PUT')
                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-700">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                placeholder="Enter user full name" required>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                        </div>
                        @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Enter the user's full name as it appears on official
                            documents</p>
                    </div>


                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block mb-2 text-sm font-medium text-gray-700">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                placeholder="Enter user email address" required>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                        </div>
                        @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">This email will be used for system login and notifications
                        </p>
                    </div>

                    <div>
                        <label for="role" class="block mb-2 text-sm font-medium text-gray-700">
                            Select Role <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="role" name="role"
                                class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('role') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                required>
                                <option value="">Select a role...</option>
                                @if(isset($roles))
                                @foreach($roles as $role)
                                <option value="{{ $role->value }}" {{ old('role', $user->role)==$role->value ?
                                    'selected' :
                                    '' }}>
                                    {{ $role->value == App\Roles::Approver->value ? "team leader" : ($role->value == App\Roles::Employee->value ? "regular employee" : $role->value) }}
                                </option>
                                @endforeach
                                @endif
                            </select>
                            <div class="absolute inset-y-0 left-0 z-10 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                        @error('role')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Choose the role this user will be assigned to</p>
                    </div>

                    <div>
                        <label for="division_id" class="block mb-2 text-sm font-medium text-gray-700">
                            Select Division <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="division_id" name="division_id"
                                class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('division_id') border-red-300 focus:ring-red-500 focus:border-red-500 @enderror"
                                >
                                <option value="">Select a division...</option>
                                @if(isset($divisions))
                                @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ old('division_id', $user->
                                    division_id)==$division->id ? 'selected' :
                                    '' }}>
                                    {{ $division->name }}
                                </option>
                                @endforeach
                                @endif
                            </select>
                            <div class="absolute inset-y-0 left-0 z-10 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                        </div>
                        @error('division_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Choose the division this user will be assigned to</p>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end pt-6 space-x-4 border-t border-gray-200">
                        <!-- Cancel Button -->
                        <a href="{{ route('admin.users.index') }}"
                            class="px-6 py-3 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </a>
                        <!-- Submit Button -->
                        <button type="submit" id="submitBtn"
                            class="px-6 py-3 text-sm font-medium text-white transition-all rounded-lg bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="submitBtnText">Update User</span>
                            <svg id="submitBtnSpinner" class="hidden w-4 h-4 ml-2 -mr-1 text-white animate-spin"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Custom Select2 styling with Tailwind CSS */
    .select2-container--default .select2-selection--single {
        background-color: white;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        height: 48px !important;
        padding-left: 44px !important;
        font-size: 0.875rem;
        line-height: 1.25rem;
    }

    .select2-container--default .select2-selection--single:focus-within,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        outline: none;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 46px !important;
        padding-left: 0 !important;
        color: #374151;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #9ca3af;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 12px;
    }

    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        z-index: 9999;
    }

    .select2-results__option {
        padding: 8px 12px;
        font-size: 0.875rem;
        color: #374151;
    }

    .select2-results__option--highlighted[aria-selected] {
        background-color: #3b82f6 !important;
        color: white !important;
    }

    .select2-results__option[aria-selected="true"] {
        background-color: #eff6ff;
        color: #1d4ed8;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 8px 12px;
        font-size: 0.875rem;
    }

    .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        outline: none;
    }

    /* Error state styling */
    .select2-container--default .select2-selection--single.border-red-300 {
        border-color: #fca5a5 !important;
    }

    .select2-container--default .select2-selection--single.border-red-300:focus-within {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5) !important;
    }
</style>
@endpush

@push('scripts')
<!-- jQuery and Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
    // Initialize Select2 with search functionality
    $('#division_id').select2({
        placeholder: 'Search and select a division...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#division_id').parent(),
        language: {
            noResults: function() {
                return "No divisions found";
            },
            searching: function() {
                return "Searching divisions...";
            },
            inputTooShort: function() {
                return "Type to search divisions...";
            }
        },
        escapeMarkup: function(markup) {
            return markup;
        }
    });

    // Handle error state styling
    @error('division_id')
    $('#division_id').next('.select2-container').find('.select2-selection--single').addClass('border-red-300');
    @enderror

    // Form submission handling
    $('#userForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true);
        $('#submitBtnText').text('Adding user...');
        $('#submitBtnSpinner').removeClass('hidden');
    });

    // Clear error styling on selection
    $('#division_id').on('select2:select', function() {
        $(this).next('.select2-container').find('.select2-selection--single').removeClass('border-red-300');
    });
});
</script>
@endpush
