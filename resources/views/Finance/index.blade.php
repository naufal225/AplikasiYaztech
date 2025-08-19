@extends('Finance.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard Analytics')
@section('subtitle', 'Overview of all employee requests')

@section('content')
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-5 mb-6 md:mb-8">
        <!-- Leaves -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Leave Requests</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">{{ $leaveCount }}</p>
                    <p class="text-neutral-500 text-xs mt-1">Total approved</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-blue-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Overtimes -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Overtime Requests</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">{{ $overtimeCount }}</p>
                    <p class="text-neutral-500 text-xs mt-1">Total approved</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-green-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Reimbursements -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Reimbursements</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">{{ $reimbursementCount }}</p>
                    <p class="text-neutral-500 text-xs mt-1">Total approved</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-receipt text-purple-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Official Travels -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">Official Travels</p>
                    <p class="text-2xl md:text-3xl font-bold text-neutral-900">{{ $officialTravelCount }}</p>
                    <p class="text-neutral-500 text-xs mt-1">Total approved</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-plane text-yellow-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 gap-6 mb-8">
        <!-- Monthly Requests Comparison -->
        <div class="bg-white border border-gray-100 shadow-sm rounded-xl">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">Monthly Approved Requests Comparison</h3>
                <p class="text-sm text-gray-600">Comparison of request types per month</p>
            </div>
            <div class="p-6">
                <canvas id="monthlyRequestsChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>
    <!-- Recent Requests Section - Redesigned to match the image -->
    <div class="bg-white rounded-lg border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Approved Requests</h3>
            <p class="text-gray-500 text-sm">Your latest submissions</p>
        </div>
        <div class="p-6">
            @forelse($recentRequests as $request)
                <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0">
                    <div class="flex items-center">
                        @if($request['type'] === App\TypeRequest::Leaves->value)
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-calendar-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">{{ $request['title'] }}</h4>
                                <p class="text-gray-500 text-sm">{{ $request['date'] }}</p>
                            </div>
                        @elseif($request['type'] === App\TypeRequest::Reimbursements->value)
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-receipt text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">{{ $request['title'] }}</h4>
                                <p class="text-gray-500 text-sm">{{ $request['date'] }}</p>
                            </div>
                        @elseif($request['type'] === App\TypeRequest::Overtimes->value)
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-clock text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">Overtime Request</h4>
                                <p class="text-gray-500 text-sm">{{ $request['date'] }}</p>
                            </div>
                        @elseif($request['type'] === App\TypeRequest::Travels->value)
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-plane text-yellow-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-800">Travel Request</h4>
                                <p class="text-gray-500 text-sm">{{ $request['date'] }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center">
                        @if($request['status_1'] === 'rejected' || $request['status_2'] === 'rejected')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 mr-3">Rejected</span>
                        @elseif($request['status_1'] === 'approved' && $request['status_2'] === 'approved')
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 mr-3">Approved</span>
                        @else
                            <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 mr-3">Pending</span>
                        @endif

                        <a href="{{ $request['url'] }}" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">No recent approved requests found.</p>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
const chartData = {
            months: @json($months),
            leaves: @json($leavesChartData),
            overtimes: @json($overtimesChartData),
            reimbursements: @json($reimbursementsChartData),
            officialTravels: @json($officialTravelsChartData),
            reimbursementsTotal: @json($reimbursementsRupiahChartData),
        };

        // Chart.js Configuration
        Chart.defaults.font.family = "Inter, system-ui, sans-serif";
        Chart.defaults.color = "#6B7280";

        // Color scheme based on the design system
        const colors = {
            primary: "#2563EB", // Blue
            secondary: "#0EA5E9", // Sky Blue
            accent: "#10B981", // Green
            warning: "#F59E0B", // Amber
            error: "#EF4444", // Red
            neutral: "#6B7280", // Gray
            light: "#F3F4F6", // Light Gray
        };

        // 1. Monthly Requests Comparison Chart (Bar Chart)
        const monthlyRequestsCtx = document
            .getElementById("monthlyRequestsChart")
            .getContext("2d");
        new Chart(monthlyRequestsCtx, {
            type: "bar",
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: "Leave Requests",
                        data: chartData.leaves,
                        backgroundColor: colors.primary,
                        borderRadius: 6,
                    },
                    {
                        label: "Reimbursement",
                        data: chartData.reimbursements,
                        backgroundColor: colors.secondary,
                        borderRadius: 6,
                    },
                    {
                        label: "Overtime",
                        data: chartData.overtimes,
                        backgroundColor: colors.accent,
                        borderRadius: 6,
                    },
                    {
                        label: "Official Travel",
                        data: chartData.officialTravels,
                        backgroundColor: colors.warning,
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "#F3F4F6",
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });

        // 2. Request Status Distribution (Doughnut Chart)
        const statusDistributionCtx = document
            .getElementById("statusDistributionChart")
            .getContext("2d");
        new Chart(statusDistributionCtx, {
            type: "doughnut",
            data: {
                labels: ["Approved", "Pending", "Rejected"],
                datasets: [
                    {
                        data: [chartData.approveds, chartData.pendings, chartData.rejecteds],
                        backgroundColor: [colors.accent, colors.warning, colors.error],
                        borderWidth: 0,
                        cutout: "60%",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                        },
                    },
                },
            },
        });

        // 3. Reimbursement Trend (Line Chart)
        const reimbursementTrendCtx = document
            .getElementById("reimbursementTrendChart")
            .getContext("2d");
        new Chart(reimbursementTrendCtx, {
            type: "line",
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: "Amount (IDR)",
                        data: chartData.reimbursementsTotal,
                        borderColor: colors.secondary,
                        backgroundColor: colors.secondary + "20",
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: colors.secondary,
                        pointBorderColor: "#fff",
                        pointBorderWidth: 2,
                        pointRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: "#F3F4F6",
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });
    @endpush
@endsection
