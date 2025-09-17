@extends('Employee.layouts.app')

@section('title', 'Request Overtime')
@section('header', 'Request Overtime')
@section('subtitle', 'Submit a new overtime request')

@section('content')
    <div class="max-w-3xl mx-auto">
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center text-sm font-medium text-neutral-700 hover:text-primary-600">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <a href="{{ route('employee.overtimes.index') }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Overtime Requests</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <span class="text-sm font-medium text-neutral-500">New Request</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
            <div class="px-6 py-4 border-b border-neutral-200">
                <h2 class="text-lg font-bold text-neutral-900">Submit Overtime Request</h2>
                <p class="text-neutral-600 text-sm">Fill in the details for your overtime request</p>
            </div>

            @if ($errors->any())
                <div class="mx-6 mt-6 bg-error-50 border border-error-200 text-error-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('employee.overtimes.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Work Hours Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-clock text-blue-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-800 mb-1">Normal Work Hours</h4>
                            <p class="text-xs text-blue-700">
                                Regular working hours: 09:00 - 17:00 (8 hours)<br>
                                Overtime is calculated for work outside these hours.
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="customer" class="block text-sm font-semibold text-neutral-700 mb-2">
                        <i class="fas fa-users mr-2 text-primary-600"></i>
                        Customer
                    </label>
                    
                    <!-- Input tampilan -->
                    <input type="text" name="customer" id="customer" class="form-input"
                        value="{{ old('customer') }}" placeholder="e.g., John Doe" required>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date_start" class="block text-sm font-semibold text-neutral-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                            Start Date & Time
                        </label>
                        <input type="datetime-local" 
                            name="date_start" 
                            id="date_start"
                            value="{{ old('date_start', now()->format('Y-m-d\TH:i')) }}"
                            class="form-input"
                            required>
                        <p class="text-xs text-neutral-500 mt-1">When did you start working overtime?</p>
                    </div>

                    <div>
                        <label for="date_end" class="block text-sm font-semibold text-neutral-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                            End Date & Time
                        </label>
                        <input type="datetime-local"
                            name="date_end"
                            id="date_end"
                            value="{{ old('date_end', now()->format('Y-m-d\TH:i')) }}"
                            class="form-input"
                            required>
                        <p class="text-xs text-neutral-500 mt-1">When did you finish working overtime?</p>
                    </div>
                </div>

                <!-- Overtime Duration Display -->
                <div id="duration-calculation" class="bg-green-50 border border-green-200 rounded-lg p-4" style="display:none;">
                    <div class="flex items-start">
                        <i class="fas fa-calculator text-green-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-green-800 mb-1">Overtime Duration</h4>
                            <p id="duration-total" class="text-sm font-bold text-green-800">Total Duration: 0 hours</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-6 border-t border-neutral-200">
                    <a href="{{ route('employee.overtimes.index') }}" class="px-6 py-2 text-sm font-medium text-neutral-700 bg-neutral-100 hover:bg-neutral-200 rounded-lg transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    const startInput = document.getElementById("date_start");
    const endInput = document.getElementById("date_end");
    const durationBox = document.getElementById("duration-calculation");
    const durationTotal = document.getElementById("duration-total");

    function setRestrictions() {
        const now = new Date();
        const todayStr = now.toISOString().split("T")[0];

        // Aturan untuk startInput (kalau hari ini, minimal jam 17:00)
        if (startInput.value) {
            const startDate = new Date(startInput.value);
            const startDay = startInput.value.split("T")[0];
            if (startDay === todayStr) {
                // min jam 17:00
                startInput.min = todayStr + "T17:00";
            } else {
                // kalau bukan hari ini, minimal jam 00:00
                startInput.min = startDay + "T00:00";
            }
        }

        // Aturan untuk endInput (harus >= startInput)
        if (startInput.value) {
            endInput.min = startInput.value;
        }
    }

    function calculateDuration() {
        if (!startInput.value || !endInput.value) return;

        const start = new Date(startInput.value);
        const end = new Date(endInput.value);

        if (end <= start) {
            durationBox.style.display = "none";
            return;
        }

        const diffMs = end - start;
        const diffHours = diffMs / (1000 * 60 * 60);
        const roundedHours = Math.floor(diffHours);

        durationBox.style.display = "block";
        durationTotal.textContent = "Total Duration: " + roundedHours + " hours";
    }

    startInput.addEventListener("change", () => {
        setRestrictions();
        calculateDuration();
    });
    endInput.addEventListener("change", calculateDuration);

    // apply restriction saat pertama kali load
    setRestrictions();
@endpush