@extends('Finance.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">Dashboard Analytics</h1>
                <p class="text-neutral-600">Overview of all employee requests</p>
            </div>
        </div>

        <!-- Statistics All Employee Approved Cards -->
        <p class="mb-2 text-sm text-neutral-500 ms-4">All Requests</p>
        <div class="grid grid-cols-1 gap-3 mb-4 sm:grid-cols-2 lg:grid-cols-4 md:gap-5 md:mb-6">
            <!-- Leaves -->
            <div class="p-4 transition-shadow duration-300 bg-white border rounded-xl shadow-soft md:p-6 border-neutral-200 hover:shadow-medium">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-xs font-medium text-neutral-600 md:text-sm">All Leave Requests</p>
                        <p class="text-2xl font-bold md:text-3xl text-neutral-900">{{ $leaveCount }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Total approved</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 md:w-12 md:h-12 rounded-xl">
                        <i class="text-lg text-blue-600 fas fa-calendar-alt md:text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Overtimes -->
            <div class="p-4 transition-shadow duration-300 bg-white border rounded-xl shadow-soft md:p-6 border-neutral-200 hover:shadow-medium">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-xs font-medium text-neutral-600 md:text-sm">All Overtime Requests</p>
                        <p class="text-2xl font-bold md:text-3xl text-neutral-900">{{ $overtimeCount }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Total approved</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-green-100 md:w-12 md:h-12 rounded-xl">
                        <i class="text-lg text-green-600 fas fa-clock md:text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Reimbursements -->
            <div class="p-4 transition-shadow duration-300 bg-white border rounded-xl shadow-soft md:p-6 border-neutral-200 hover:shadow-medium">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-xs font-medium text-neutral-600 md:text-sm">All Reimbursement Requests</p>
                        <p class="text-2xl font-bold md:text-3xl text-neutral-900">{{ $reimbursementCount }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Total approved</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-purple-100 md:w-12 md:h-12 rounded-xl">
                        <i class="text-lg text-purple-600 fas fa-receipt md:text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Official Travels -->
            <div class="p-4 transition-shadow duration-300 bg-white border rounded-xl shadow-soft md:p-6 border-neutral-200 hover:shadow-medium">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-xs font-medium text-neutral-600 md:text-sm">All Official Travel Requests</p>
                        <p class="text-2xl font-bold md:text-3xl text-neutral-900">{{ $officialTravelCount }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Total approved</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 bg-yellow-100 md:w-12 md:h-12 rounded-xl">
                        <i class="text-lg text-yellow-600 fas fa-plane md:text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Yours Cards - Adjusted for mobile -->
        <p class="mb-2 text-sm text-neutral-500 ms-4">Your Requests</p>
        <div class="grid grid-cols-1 gap-3 mb-6 sm:grid-cols-2 lg:grid-cols-4 md:gap-5 md:mb-8">
            <!-- Pending Approvals Card -->
            <div class="p-4 transition-shadow duration-300 bg-white border rounded-xl shadow-soft md:p-6 border-neutral-200 hover:shadow-medium">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-xs font-medium text-neutral-600 md:text-sm">Pending Approvals</p>
                        <p class="text-2xl font-bold md:text-3xl text-neutral-900">{{ $pendingYoursLeaves + $pendingYoursReimbursements + $pendingYoursOvertimes + $pendingYoursTravels }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Awaiting review</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-warning-100 rounded-xl">
                        <i class="text-lg fas fa-clock text-warning-600 md:text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Approved This Month Card -->
            <div class="p-4 transition-shadow duration-300 bg-white border rounded-xl shadow-soft md:p-6 border-neutral-200 hover:shadow-medium">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-xs font-medium text-neutral-600 md:text-sm">Approved This Month</p>
                        <p class="text-2xl font-bold md:text-3xl text-neutral-900">{{ $approvedYoursLeaves + $approvedYoursReimbursements + $approvedYoursOvertimes + $approvedYoursTravels }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Successfully approved</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-success-100 rounded-xl">
                        <i class="text-lg fas fa-check-circle text-success-600 md:text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Rejected Requests Card -->
            <div class="p-4 transition-shadow duration-300 bg-white border rounded-xl shadow-soft md:p-6 border-neutral-200 hover:shadow-medium">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-xs font-medium text-neutral-600 md:text-sm">Rejected This Month</p>
                        <p class="text-2xl font-bold md:text-3xl text-neutral-900">{{ $rejectedYoursLeaves + $rejectedYoursReimbursements + $rejectedYoursOvertimes + $rejectedYoursTravels }}</p>
                        <p class="mt-1 text-xs text-neutral-500">Rejected approved</p>
                    </div>
                    <div class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 bg-error-100 rounded-xl">
                        <i class="text-lg fas fa-times-circle text-error-600 md:text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Remaining Days Requests Card -->
            <div class="p-4 transition-shadow duration-300 bg-white border rounded-xl shadow-soft md:p-6 border-neutral-200 hover:shadow-medium">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-xs font-medium text-neutral-600 md:text-sm">Remaining Days</p>
                        <p class="text-2xl font-bold md:text-3xl text-neutral-900">{{ $sisaCuti }}/{{ env('CUTI_TAHUNAN', 20) }} ({{ now()->year }})</p>
                        <p class="mt-1 text-xs text-neutral-500">Remaining leave</p>
                    </div>
                    <div class="w-10 h-10 md:w-12 md:h-12 {{ $sisaCuti <= 0 ? 'bg-error-100 text-error-600' : ($sisaCuti > ((int) env('CUTI_TAHUNAN', 20) / 2) ? 'bg-success-100 text-success-600' : 'bg-warning-100 text-warning-600')}} rounded-xl flex items-center justify-center">
                        @if ($sisaCuti <= 0)
                            <i class="text-lg fas fa-times-circle md:text-xl"></i>
                        @elseif ($sisaCuti > ((int) env('CUTI_TAHUNAN', 20) / 2))
                            <i class="text-lg fas fa-check-circle md:text-xl"></i>
                        @else
                            <i class="text-lg fas fa-exclamation-circle md:text-xl"></i>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 lg:grid-cols-4">
            <button onclick="window.location.href='{{ route('finance.leaves.create') }}'" @if($sisaCuti <= 0) disabled @endif class="bg-primary-600 hover:bg-primary-700 text-white rounded-lg p-4 hover:shadow-md transition-all @if($sisaCuti <= 0) cursor-not-allowed @else cursor-pointer @endif">
                <div class="flex flex-col items-center text-center">
                    <div class="flex items-center justify-center w-12 h-12 mb-3 bg-white rounded-full bg-opacity-20">
                        <i class="text-xl fas fa-calendar-plus text-primary-600"></i>
                    </div>
                    <h3 class="mb-1 font-semibold">Request Leave</h3>
                    <p class="text-sm text-primary-100">Submit new leave request</p>
                </div>
            </button>

            <a href="{{ route('finance.reimbursements.create') }}" class="p-4 text-white transition-all rounded-lg bg-secondary-600 hover:bg-secondary-700 hover:shadow-md">
                <div class="flex flex-col items-center text-center">
                    <div class="flex items-center justify-center w-12 h-12 mb-3 bg-white rounded-full bg-opacity-20">
                        <i class="text-xl fas fa-receipt text-secondary-600"></i>
                    </div>
                    <h3 class="mb-1 font-semibold">Submit Reimbursement</h3>
                    <p class="text-sm text-secondary-100">Upload expense receipts</p>
                </div>
            </a>

            <a href="{{ route('finance.overtimes.create') }}" class="p-4 text-white transition-all rounded-lg bg-success-600 hover:bg-success-700 hover:shadow-md">
                <div class="flex flex-col items-center text-center">
                    <div class="flex items-center justify-center w-12 h-12 mb-3 bg-white rounded-full bg-opacity-20">
                        <i class="text-xl fas fa-clock text-success-600"></i>
                    </div>
                    <h3 class="mb-1 font-semibold">Request Overtime</h3>
                    <p class="text-sm text-success-100">Log overtime hours</p>
                </div>
            </a>

            <a href="{{ route('finance.official-travels.create') }}" class="p-4 text-white transition-all rounded-lg bg-warning-600 hover:bg-warning-700 hover:shadow-md">
                <div class="flex flex-col items-center text-center">
                    <div class="flex items-center justify-center w-12 h-12 mb-3 bg-white rounded-full bg-opacity-20">
                        <i class="text-xl fas fa-plane text-warning-600"></i>
                    </div>
                    <h3 class="mb-1 font-semibold">Request Travel</h3>
                    <p class="text-sm text-warning-100">Plan business trip</p>
                </div>
            </a>
        </div>

        <!-- Divider -->
        <div class="mt-6 mb-10 transform scale-y-50 border-t border-gray-300/80"></div>

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

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Calendar Section -->
            <div class="mb-8 bg-white border border-gray-200 rounded-xl shadow-soft">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-center text-gray-800">Employee Leave Calendar</h3>
                </div>
                <div class="p-4 sm:p-6">
                    <div class="calendar-wrapper">
                        <div id="calendar" class="overflow-hidden bg-white rounded-xl">
                            <div class="flex items-center justify-between mb-4 calendar-header">
                                <button id="prev" class="p-2 transition rounded-full hover:bg-gray-100">
                                    <i class="text-gray-600 fas fa-chevron-left"></i>
                                </button>
                                <h2 id="monthYear" class="text-lg font-bold text-gray-800"></h2>
                                <button id="next" class="p-2 transition rounded-full hover:bg-gray-100">
                                    <i class="text-gray-600 fas fa-chevron-right"></i>
                                </button>
                            </div>

                            <div class="grid grid-cols-7 pb-2 text-xs font-medium text-center text-gray-500 border-b border-gray-100 sm:text-sm">
                                <div>Min</div><div>Sen</div><div>Sel</div>
                                <div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                            </div>

                            <div id="dates" class="grid grid-cols-7 gap-1 mt-2 text-center sm:gap-2"></div>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-center text-red-500 sm:text-sm">
                        *Klik tanggal bertanda merah untuk melihat siapa saja yang cuti
                    </p>
                </div>
            </div>

            <!-- Recent Requests Section -->
            <div class="mb-8 bg-white border border-gray-200 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Recent All Approved Requests</h3>
                    <p class="text-sm text-gray-500">Employee latest submissions</p>
                </div>
                <div class="p-6">
                    @forelse($recentRequests as $request)
                        <div class="flex items-center justify-between py-4 border-b border-gray-100 cursor-pointer last:border-0" onclick="window.location.href='{{ $request['url'] }}'">
                            <!-- Kiri: ikon + judul -->
                            <div class="flex items-center min-w-0">
                                @if($request['type'] === App\Enums\TypeRequest::Leaves->value)
                                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-4 bg-blue-100 rounded-lg">
                                        <i class="text-blue-600 fas fa-calendar-alt"></i>
                                    </div>
                                @elseif($request['type'] === App\Enums\TypeRequest::Reimbursements->value)
                                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-4 bg-purple-100 rounded-lg">
                                        <i class="text-purple-600 fas fa-receipt"></i>
                                    </div>
                                @elseif($request['type'] === App\Enums\TypeRequest::Overtimes->value)
                                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-4 bg-green-100 rounded-lg">
                                        <i class="text-green-600 fas fa-clock"></i>
                                    </div>
                                @elseif($request['type'] === App\Enums\TypeRequest::Travels->value)
                                    <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 mr-4 bg-yellow-100 rounded-lg">
                                        <i class="text-yellow-600 fas fa-plane"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <h4 class="font-medium text-gray-800 truncate">
                                        {{ $request['title'] ?? ($request['type'] === App\Enums\TypeRequest::Overtimes->value ? 'Overtime Request' : 'Travel Request') }}
                                    </h4>
                                    <p class="text-sm text-gray-500 truncate">{{ $request['date'] }}</p>

                                    <div class="flex items-center pt-3 mt-4 mb-3 truncate border-t">
                                        <div class="flex items-center justify-center w-8 h-8 mr-3 rounded-full bg-success-100">
                                            @if($request['url_photo'])
                                                <img class="object-cover rounded-full"
                                                    src="{{ $request['url_photo'] }}" alt="{{ $request['name_owner'] }}">
                                            @else
                                                <span class="text-xs font-semibold text-success-600">{{ substr($request['name_owner'], 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $request['name_owner'] }}</div>
                                            <div class="text-sm text-neutral-500">{{ $request['email_owner'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kanan: status + arrow -->
                            <div class="flex items-center flex-shrink-0 ml-3">
                                @if(isset($request['status_2']) && $request['status_2'] !== null)
                                    {{-- Jika ada status_2, maka cek keduanya --}}
                                    @if($request['status_1'] === 'approved' && $request['status_2'] === 'approved')
                                        <span class="px-3 py-1 mr-3 text-xs font-medium text-green-800 bg-green-100 rounded-full">Approved</span>
                                    @elseif($request['status_1'] === 'rejected' || $request['status_2'] === 'rejected')
                                        <span class="px-3 py-1 mr-3 text-xs font-medium text-red-800 bg-red-100 rounded-full">Rejected</span>
                                    @else
                                        <span class="px-3 py-1 mr-3 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Pending</span>
                                    @endif
                                @else
                                    {{-- Jika tidak ada status_2, cek hanya status_1 --}}
                                    @if($request['status_1'] === 'approved')
                                        <span class="px-3 py-1 mr-3 text-xs font-medium text-green-800 bg-green-100 rounded-full">Approved</span>
                                    @elseif($request['status_1'] === 'rejected')
                                        <span class="px-3 py-1 mr-3 text-xs font-medium text-red-800 bg-red-100 rounded-full">Rejected</span>
                                    @else
                                        <span class="px-3 py-1 mr-3 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Pending</span>
                                    @endif
                                @endif

                                <a href="{{ $request['url'] }}" class="flex-shrink-0 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center">
                            <i class="mb-3 text-4xl text-gray-300 fas fa-inbox"></i>
                            <p class="text-gray-500">No recent approved requests found.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="cutiModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 backdrop-blur-sm">
            <div class="bg-white p-6 sm:p-8 rounded-2xl w-[90%] max-w-md shadow-lg transform transition-all scale-95 opacity-0"
                id="cutiModalContent">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800">List of Employee on Leave</h2>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <ul id="cutiList" class="space-y-3 text-sm text-left sm:text-base"></ul>
            </div>
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
                console.table(cuti);
                let firstLetter = cuti.employee ? cuti.employee.substring(0, 1).toUpperCase() : "?";
                list.innerHTML += `
                    <li class="flex items-start gap-2">
                        ${cuti.url_profile ? `
                            <img class="flex items-center justify-center object-cover w-10 h-10 rounded-full me-1"
                                src="${cuti.url_profile}" alt="${cuti.employee}">
                        ` : `
                            <span class="flex items-center justify-center w-10 h-10 text-xs text-blue-600 bg-blue-100 rounded-full me-1">
                                ${firstLetter}
                            </span>
                        `}
                        <div>
                            <p class="font-medium text-gray-800">${cuti.employee ?? '-'}</p>
                            <p class="text-xs text-gray-500">${cuti.email ?? '-'}</p>
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
