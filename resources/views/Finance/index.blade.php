@extends('Finance.layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard Analytics')
@section('subtitle', 'Overview of all employee requests')

@section('content')
    <!-- Statistics All Employee Approved Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-5 mb-6 md:mb-8">
        <!-- Leaves -->
        <div class="bg-white rounded-xl shadow-soft p-4 md:p-6 border border-neutral-200 hover:shadow-medium transition-shadow duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">All Leave Requests</p>
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
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">All Overtime Requests</p>
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
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">All Reimbursement Requests</p>
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
                    <p class="text-neutral-600 text-xs md:text-sm font-medium mb-1">All Official Travel Requests</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Calendar Section -->
        <div class="bg-white rounded-xl border border-gray-200 mb-8 shadow-soft">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 text-center">Employee Leave Calendar</h3>
            </div>
            <div class="p-4 sm:p-6">
                <div class="calendar-wrapper">
                    <div id="calendar" class="bg-white rounded-xl overflow-hidden">
                        <div class="calendar-header flex justify-between items-center mb-4">
                            <button id="prev" class="p-2 rounded-full hover:bg-gray-100 transition">
                                <i class="fas fa-chevron-left text-gray-600"></i>
                            </button>
                            <h2 id="monthYear" class="text-lg font-bold text-gray-800"></h2>
                            <button id="next" class="p-2 rounded-full hover:bg-gray-100 transition">
                                <i class="fas fa-chevron-right text-gray-600"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-7 text-center font-medium text-gray-500 text-xs sm:text-sm border-b border-gray-100 pb-2">
                            <div>Min</div><div>Sen</div><div>Sel</div>
                            <div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                        </div>

                        <div id="dates" class="grid grid-cols-7 gap-1 sm:gap-2 text-center mt-2"></div>
                    </div>
                </div>
                <p class="text-red-500 text-center mt-3 text-xs sm:text-sm">
                    *Klik tanggal bertanda merah untuk melihat siapa saja yang cuti
                </p>
            </div>
        </div>

        <!-- Recent Requests Section -->
        <div class="bg-white rounded-lg border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Recent Approved Requests</h3>
                <p class="text-gray-500 text-sm">Employee latest submissions</p>
            </div>
            <div class="p-6">
                @forelse($recentRequests as $request)
                    <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-0">
                        <!-- Kiri: ikon + judul -->
                        <div class="flex items-center min-w-0">
                            @if($request['type'] === App\TypeRequest::Leaves->value)
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-calendar-alt text-blue-600"></i>
                                </div>
                            @elseif($request['type'] === App\TypeRequest::Reimbursements->value)
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-receipt text-purple-600"></i>
                                </div>
                            @elseif($request['type'] === App\TypeRequest::Overtimes->value)
                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-clock text-green-600"></i>
                                </div>
                            @elseif($request['type'] === App\TypeRequest::Travels->value)
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-plane text-yellow-600"></i>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <h4 class="font-medium text-gray-800 truncate">
                                    {{ $request['title'] ?? ($request['type'] === App\TypeRequest::Overtimes->value ? 'Overtime Request' : 'Travel Request') }}
                                </h4>
                                <p class="text-gray-500 text-sm truncate">{{ $request['date'] }}</p>
                            </div>
                        </div>

                        <!-- Kanan: status + arrow -->
                        <div class="flex items-center flex-shrink-0 ml-3">
                            @if($request['status_1'] === 'rejected')
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 mr-3">Rejected</span>
                            @elseif($request['status_1'] === 'approved')
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 mr-3">Approved</span>
                            @else
                                <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 mr-3">Pending</span>
                            @endif

                            <a href="{{ $request['url'] }}" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
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
    </div>

    <!-- Modal -->
    <div id="cutiModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white p-6 sm:p-8 rounded-2xl w-[90%] max-w-md shadow-lg transform transition-all scale-95 opacity-0"
            id="cutiModalContent">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800">List of Employee on Leave</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <ul id="cutiList" class="space-y-3 text-left text-sm sm:text-base"></ul>
        </div>
    </div>

    @push('scripts')
    const monthYear = document.getElementById("monthYear");
    const datesContainer = document.getElementById("dates");
    const prevBtn = document.getElementById("prev");
    const nextBtn = document.getElementById("next");

    let today = new Date();
    let currentMonth = today.getMonth();
    let currentYear = today.getFullYear();

    const monthNames = [
        "Januari","Februari","Maret","April","Mei","Juni",
        "Juli","Agustus","September","Oktober","November","Desember"
    ];

    const cutiPerTanggal = @json($cutiPerTanggal);

    function renderCalendar(month, year) {
        datesContainer.innerHTML = "";
        monthYear.textContent = monthNames[month] + " " + year;

        let firstDay = new Date(year, month, 1).getDay();
        let daysInMonth = new Date(year, month + 1, 0).getDate();

        // Kosongkan slot awal minggu
        for (let i = 0; i < firstDay; i++) {
            datesContainer.innerHTML += `<div></div>`;
        }

        // Isi tanggal
        for (let day = 1; day <= daysInMonth; day++) {
            let dateStr = `${year}-${String(month+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
            let isToday = (day === today.getDate() && month === today.getMonth() && year === today.getFullYear());

            let classes = `
                relative aspect-square flex items-center justify-center 
                rounded-lg cursor-pointer text-sm sm:text-base
                hover:bg-blue-100 transition
                ${isToday ? 'bg-gray-200 font-bold' : ''}
            `;

            let content = `<span>${day}</span>`;

            if (cutiPerTanggal[dateStr]) {
                content += `
                    <span class="absolute top-1 right-1 w-2 h-2 sm:w-2.5 sm:h-2.5 bg-red-500 rounded-full"></span>
                `;
            }

            datesContainer.innerHTML += `
                <div class="${classes}" onclick="showEvent('${dateStr}')">
                    ${content}
                </div>
            `;
        }
    }

    function showEvent(dateStr) {
        const modal = document.getElementById('cutiModal');
        const modalContent = document.getElementById('cutiModalContent');
        const list = document.getElementById('cutiList');
        list.innerHTML = "";

        if (cutiPerTanggal[dateStr]) {
            cutiPerTanggal[dateStr].forEach(cuti => {
                list.innerHTML += `
                    <li class="flex items-start gap-2">
                        <span class="w-6 h-6 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 text-xs"><i class="fas fa-user"></i></span>
                        <div>
                            <p class="font-medium text-gray-800">${cuti.employee}</p>
                            <p class="text-gray-500 text-xs">${cuti.email ?? '-'}</p>
                        </div>
                    </li>
                `;
            });
        } else {
            list.innerHTML = "<li class='text-gray-600'>Tidak ada cuti</li>";
        }

        modal.classList.remove("hidden");

        // Animasi muncul
        setTimeout(() => {
            modalContent.classList.remove("scale-95", "opacity-0");
            modalContent.classList.add("scale-100", "opacity-100");
        }, 10);
    }

    function closeModal() {
        const modal = document.getElementById('cutiModal');
        const modalContent = document.getElementById('cutiModalContent');

        modalContent.classList.add("scale-95", "opacity-0");
        modalContent.classList.remove("scale-100", "opacity-100");

        setTimeout(() => modal.classList.add("hidden"), 150);
    }

    prevBtn.onclick = () => {
        currentMonth--;
        if (currentMonth < 0) { currentMonth = 11; currentYear--; }
        renderCalendar(currentMonth, currentYear);
    };

    nextBtn.onclick = () => {
        currentMonth++;
        if (currentMonth > 11) { currentMonth = 0; currentYear++; }
        renderCalendar(currentMonth, currentYear);
    };

    renderCalendar(currentMonth, currentYear);

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
