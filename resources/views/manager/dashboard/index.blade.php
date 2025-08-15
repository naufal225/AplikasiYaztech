@extends('components.hr.layout.layout-hr')

@section('header', 'Dashboard')
@section('subtitle', 'Welcome Back!')

@section('content')
<!-- Stats Cards - Light Neutral Background (15%) -->
<div class="mb-6">
    <p class="text-gray-600">Showing data for {{ now()->format('F Y') }}</p>
</div>

<div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
    <!-- Total Employees -->
    <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Employees</p>
                <p class="text-3xl font-bold text-gray-900">{{ $total_employees }}</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Pending Approvals - Warning Amber (5%) -->
    <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Pending Approvals</p>
                <p class="text-3xl font-bold text-gray-900">{{ $total_pending }}</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-amber-100">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Approved Requests - Accent Green (10%) -->
    <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Approved This Month</p>
                <p class="text-3xl font-bold text-gray-900">{{ $total_approved }}</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Rejected Requests - Error Red (5%) -->
    <div class="p-6 bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Rejected Requests</p>
                <p class="text-3xl font-bold text-gray-900">{{ $total_rejected }}</p>
            </div>
            <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-lg">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
        </div>
    </div>
</div>

<div class="mb-6">
    <p class="text-gray-600">Showing data for {{ now()->format('Y') }}</p>
</div>

<div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-1">
    <!-- Monthly Requests Comparison Chart -->
    <div class="bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Monthly Requests Comparison</h3>
            <p class="text-sm text-gray-600">Comparison of different request types</p>
        </div>
        <div class="p-6">
            <canvas id="monthlyRequestsChart" width="400" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
    <!-- Reimbursement Trend -->
    <div class="bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Reimbursement Trend</h3>
            <p class="text-sm text-gray-600">Monthly reimbursement amounts</p>
        </div>
        <div class="p-6">
            <canvas id="reimbursementTrendChart" width="400" height="300"></canvas>
        </div>
    </div>

    <!-- Request Status Distribution -->
    <div class="bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Request Status Distribution</h3>
            <p class="text-sm text-gray-600">Overall status breakdown</p>
        </div>
        <div class="p-6">
            <canvas id="statusDistributionChart" width="400" height="300"></canvas>
        </div>
    </div>
</div>

{{-- <!-- More Charts -->
<div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
    <!-- Leave Types Breakdown -->
    <div class="bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Leave Types Breakdown</h3>
            <p class="text-sm text-gray-600">Distribution of leave types</p>
        </div>
        <div class="p-6">
            <canvas id="leaveTypesChart" width="400" height="300"></canvas>
        </div>
    </div>

    <!-- Overtime Hours by Department -->
    <div class="bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-900">Overtime Hours by Department</h3>
            <p class="text-sm text-gray-600">Total overtime hours per department</p>
        </div>
        <div class="p-6">
            <canvas id="overtimeChart" width="400" height="300"></canvas>
        </div>
    </div>
</div> --}}

<script>
    const chartData = {
        months: @json($months),
        reimbursements: @json($reimbursementsChartData),
        overtimes: @json($overtimesChartData),
        leaves: @json($leavesChartData),
        officialTravels: @json($officialTravelsChartData),
        reimbursementsTotal: @json($reimbursementsRupiahChartData),
        pendings: @json($total_pending),
        approveds: @json($total_approved),
        rejecteds: @json($total_rejected)
    };
</script>
@push('scripts')
@vite("resources/js/hr/dashboard/script.js")
@endpush
@endsection
