@extends('Employee.layouts.app')

@section('title', 'Edit Official Travel Request')
@section('header', 'Edit Official Travel Request')
@section('subtitle', 'Modify your official travel request details')

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
                        <a href="{{ route('employee.official-travels.index') }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Official Travel Requests</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <a href="{{ route('employee.official-travels.show', $officialTravel->id) }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Request #{{ $officialTravel->id }}</a>
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
                <h2 class="text-lg font-bold text-neutral-900">Edit Official Travel Request #{{ $officialTravel->id }}</h2>
                <p class="text-neutral-600 text-sm">Update your official travel request information</p>
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

            <form action="{{ route('employee.official-travels.update', $officialTravel->id) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date_start" class="block text-sm font-semibold text-neutral-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                            Start Date
                        </label>
                        <input type="date" id="date_start" name="date_start" class="form-input"
                               value="{{ $officialTravel->date_start->format('Y-m-d') }}" required min="{{ date('Y-m-d') }}" onchange="calculateDays()">
                    </div>

                    <div>
                        <label for="date_end" class="block text-sm font-semibold text-neutral-700 mb-2">
                            <i class="fas fa-calendar-alt mr-2 text-primary-600"></i>
                            End Date
                        </label>
                        <input type="date" id="date_end" name="date_end" class="form-input"
                               value="{{ $officialTravel->date_end->format('Y-m-d') }}" required min="{{ date('Y-m-d') }}" onchange="calculateDays()">
                        <p class="text-xs text-neutral-500 mt-1">Current duration: {{ $officialTravel->total }} day{{ $officialTravel->total > 1 ? 's' : '' }}</p>
                    </div>
                </div>

                 Travel Duration Display 
                <div id="duration-calculation" class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-calculator text-green-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-semibold text-green-800 mb-1">Travel Duration</h4>
                            <p id="duration-total" class="text-sm font-bold text-green-800"></p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-6 border-t border-neutral-200">
                    <a href="{{ route('employee.official-travels.show', $officialTravel->id) }}" class="px-6 py-2 text-sm font-medium text-neutral-700 bg-neutral-100 hover:bg-neutral-200 rounded-lg transition-colors duration-200">
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

    @push('scripts')
        function calculateDays() {
            const startInput = document.getElementById('date_start');
            const endInput = document.getElementById('date_end');
            const calculationDiv = document.getElementById('duration-calculation');
            const totalP = document.getElementById('duration-total');
            
            if (!startInput.value || !endInput.value) {
                calculationDiv.style.display = 'none';
                return;
            }
            
            const startDate = new Date(startInput.value);
            const endDate = new Date(endInput.value);
            
            if (endDate < startDate) {
                calculationDiv.style.display = 'none';
                return;
            }
            
            const timeDiff = endDate.getTime() - startDate.getTime();
            const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1; // +1 to include both start and end date
            
            if (daysDiff > 0) {
                calculationDiv.style.display = 'block';
                totalP.textContent = `Total Duration: ${daysDiff} day${daysDiff > 1 ? 's' : ''}`;
            } else {
                calculationDiv.style.display = 'none';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            calculateDays();
        });
    @endpush
@endsection
