@extends('Employee.layouts.app')

@section('title', 'Edit Overtime Request')
@section('header', 'Edit Overtime Request')
@section('subtitle', 'Modify your overtime request details')

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
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <a href="{{ route('employee.overtimes.show', $overtime->id) }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Request #{{ $overtime->id }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <span class="text-sm font-medium text-neutral-500">Edit</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
            <div class="px-6 py-4 border-b border-neutral-200">
                <h2 class="text-lg font-bold text-neutral-900">Edit Overtime Request #{{ $overtime->id }}</h2>
                <p class="text-neutral-600 text-sm">Update your overtime request information</p>
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

            <form action="{{ route('employee.overtimes.update', $overtime->id) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="approver_id" class="block text-sm font-semibold text-neutral-700 mb-2">
                        <i class="fas fa-user-check mr-2 text-primary-600"></i>
                        Approver
                    </label>
                    <select id="approver_id" name="approver_id" class="form-select" required>
                        <option value="">Select Approver</option>
                        @foreach($approvers as $approver)
                            <option value="{{ $approver->id }}" {{ $overtime->approver_id == $approver->id ? 'selected' : '' }}>
                                {{ $approver->name }} ({{ ucfirst($approver->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Work Hours Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-clock text-blue-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-800 mb-1">Normal Work Hours</h4>
                            <p class="text-xs text-blue-700">
                                Regular working hours: 09:00 - 17:00 (8 hours)<br>
                                Current overtime: {{ $overtime->total }} hours
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date_start" class="block text-sm font-semibold text-neutral-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                            Start Date & Time
                        </label>
                        <input type="datetime-local" 
                            name="date_start" 
                            value="{{ old('date_start', $overtime->date_start->format('Y-m-d\TH:i')) }}"
                            class="form-input"
                            required>
                    </div>

                    <div>
                        <label for="date_end" class="block text-sm font-semibold text-neutral-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                            End Date & Time
                        </label>
                        <input type="datetime-local"
                            name="date_end"
                            value="{{ old('date_end', $overtime->date_end->format('Y-m-d\TH:i')) }}"
                            class="form-input"
                            required>
                    </div>
                </div>

                 <!-- Warning Notice -->
                <div class="bg-warning-50 border border-warning-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-warning-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-warning-800 mb-1">Important Notice</h4>
                            <p class="text-xs text-warning-700">
                                Editing this request will reset its status to pending and require re-approval from your manager.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-6 border-t border-neutral-200">
                    <a href="{{ route('employee.overtimes.show', $overtime->id) }}" class="px-6 py-2 text-sm font-medium text-neutral-700 bg-neutral-100 hover:bg-neutral-200 rounded-lg transition-colors duration-200">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Update Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function calculateOvertime() {
        const startInput = document.getElementById('date_start');
        const endInput = document.getElementById('date_end');
        const calculationDiv = document.getElementById('overtime-calculation');
        const detailsP = document.getElementById('overtime-details');
        const totalP = document.getElementById('overtime-total');
        
        if (!startInput.value || !endInput.value) {
            calculationDiv.style.display = 'none';
            return;
        }
        
        const startTime = new Date(startInput.value);
        const endTime = new Date(endInput.value);
        
        if (endTime <= startTime) {
            calculationDiv.style.display = 'none';
            return;
        }
        
        // Normal work hours: 09:00 - 17:00
        const workStart = new Date(startTime);
        workStart.setHours(9, 0, 0, 0);
        
        const workEnd = new Date(startTime);
        workEnd.setHours(17, 0, 0, 0);
        
        let overtimeHours = 0;
        let details = [];
        
        // Check for early morning overtime (before 09:00)
        if (startTime < workStart) {
            const morningOvertime = (workStart - startTime) / (1000 * 60 * 60);
            overtimeHours += morningOvertime;
            details.push(`Morning overtime: ${morningOvertime.toFixed(1)} hours (${startTime.toLocaleTimeString()} - 09:00)`);
        }
        
        // Check for evening overtime (after 17:00)
        if (endTime > workEnd) {
            const eveningOvertime = (endTime - workEnd) / (1000 * 60 * 60);
            overtimeHours += eveningOvertime;
            details.push(`Evening overtime: ${eveningOvertime.toFixed(1)} hours (17:00 - ${endTime.toLocaleTimeString()})`);
        }
        
        if (overtimeHours > 0) {
            calculationDiv.style.display = 'block';
            detailsP.innerHTML = details.join('<br>');
            totalP.textContent = `Total Overtime: ${overtimeHours.toFixed(1)} hours`;
            
            if (overtimeHours < 0.5) {
                calculationDiv.className = 'bg-red-50 border border-red-200 rounded-lg p-4';
                totalP.innerHTML = `<span class="text-red-600">Total Overtime: ${overtimeHours.toFixed(1)} hours (Minimum 0.5 hours required)</span>`;
            } else {
                calculationDiv.className = 'bg-green-50 border border-green-200 rounded-lg p-4';
            }
        } else {
            calculationDiv.style.display = 'none';
        }
    }

    // Calculate on page load
    document.addEventListener('DOMContentLoaded', function() {
        calculateOvertime();
    });
    </script>
@endsection
