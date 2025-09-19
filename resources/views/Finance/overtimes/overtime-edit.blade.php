@extends('Finance.layouts.app')

@section('title', 'Edit Overtime Request')
@section('header', 'Edit Overtime Request')
@section('subtitle', 'Modify your overtime request details')

@section('content')
<div class="max-w-3xl mx-auto">
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('finance.dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-neutral-700 hover:text-primary-600">
                    <i class="mr-2 fas fa-home"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                    <a href="{{ route('finance.overtimes.index') }}"
                        class="text-sm font-medium text-neutral-700 hover:text-primary-600">Overtime Requests</a>
                </div>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                    <a href="{{ route('finance.overtimes.show', $overtime->id) }}"
                        class="text-sm font-medium text-neutral-700 hover:text-primary-600">Request #OY{{ $overtime->id
                        }}</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                    <span class="text-sm font-medium text-neutral-500">Edit</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
        <div class="px-6 py-4 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">Edit Overtime Request #OY{{ $overtime->id }}</h2>
            <p class="text-sm text-neutral-600">Update your overtime request information</p>
        </div>

        @if ($errors->any())
        <div class="px-4 py-3 mx-6 mt-6 border rounded-lg bg-error-50 border-error-200 text-error-700">
            <ul class="pl-5 space-y-1 list-disc">
                @foreach ($errors->all() as $error)
                <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('finance.overtimes.update', $overtime->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Work Hours Info -->
            <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                <div class="flex items-start">
                    <i class="fas fa-clock text-blue-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="mb-1 text-sm font-semibold text-blue-800">Normal Work Hours</h4>
                        <p class="text-xs text-blue-700">
                            Regular working hours: 09:00 - 17:00 (8 hours)<br>
                            @php
                            $totalMinutes = $overtime->total;
                            $hours = floor($totalMinutes / 60);
                            $minutes = $totalMinutes % 60;
                            @endphp
                            Current overtime: {{ $hours }} hours {{ $minutes }} minutes
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <label for="customer" class="block mb-2 text-sm font-semibold text-neutral-700">
                    <i class="mr-2 fas fa-users text-primary-600"></i>
                    Customer
                </label>

                <!-- Input tampilan -->
                <input type="text" name="customer" id="customer" class="form-input"
                    value="{{ old('customer', $overtime->customer) }}" placeholder="e.g., John Doe" required>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label for="date_start" class="block mb-2 text-sm font-semibold text-neutral-700">
                        <i class="mr-2 fas fa-calendar-alt text-primary-600"></i>
                        Start Date & Time
                    </label>
                    <input type="datetime-local" name="date_start"
                        value="{{ old('date_start', $overtime->date_start->format('Y-m-d\TH:i')) }}" class="form-input"
                        required>
                </div>

                <div>
                    <label for="date_end" class="block mb-2 text-sm font-semibold text-neutral-700">
                        <i class="mr-2 fas fa-calendar-alt text-primary-600"></i>
                        End Date & Time
                    </label>
                    <input type="datetime-local" name="date_end"
                        value="{{ old('date_end', $overtime->date_end->format('Y-m-d\TH:i')) }}" class="form-input"
                        required>
                </div>
            </div>

            <!-- Warning Notice -->
            <div class="p-4 border rounded-lg bg-warning-50 border-warning-200">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-warning-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="mb-1 text-sm font-semibold text-warning-800">Important Notice</h4>
                        <p class="text-xs text-warning-700">
                            Editing this request will reset its status to pending and require re-approval from your team
                            lead.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Overtime Duration Display -->
            <div id="duration-calculation" class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-calculator text-green-600 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-green-800 mb-1">Overtime Duration</h4>
                        <p id="duration-total" class="text-sm font-bold text-green-800">Total Duration: {{ $hours }} hours</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 space-x-4 border-t border-neutral-200">
                <a href="{{ url()->previous() }}"
                    class="px-6 py-2 text-sm font-medium transition-colors duration-200 rounded-lg text-neutral-700 bg-neutral-100 hover:bg-neutral-200">
                    <i class="mr-2 fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <i class="mr-2 fas fa-save"></i>
                    Update Request
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