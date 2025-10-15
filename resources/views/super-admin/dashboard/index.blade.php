@extends('components.super-admin.layout.layout-super-admin')

@section('header', 'Dashboard')
@section('subtitle', 'Welcome Back!')

@section('content')
<div class="flex flex-col gap-6 xl:flex-row">
    <!-- Left Column: Stats Cards -->
    <div class="flex-1">
        <!-- Stats Cards -->
        <div class="mb-6">
            <p class="text-gray-600">Showing data for {{ now()->format('F Y') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Calendar - Responsif -->
    <div class="flex-[0.5] mt-6 xl:mt-12">
        <div class="bg-white border border-gray-200 rounded-xl shadow-soft" style="height: fit-content;">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-base font-semibold text-center text-gray-800">Employee Leave Calendar</h3>
            </div>
            <div class="p-3 sm:p-4">
                <div class="calendar-wrapper">
                    <div id="calendar" class="overflow-hidden bg-white rounded-xl">
                        <div class="flex items-center justify-between mb-2 calendar-header">
                            <button id="prev" class="p-1 transition rounded-full hover:bg-gray-100">
                                <i class="text-sm text-gray-600 fas fa-chevron-left"></i>
                            </button>
                            <h2 id="monthYear" class="text-base font-bold text-gray-800"></h2>
                            <button id="next" class="p-1 transition rounded-full hover:bg-gray-100">
                                <i class="text-sm text-gray-600 fas fa-chevron-right"></i>
                            </button>
                        </div>

                        <div
                            class="grid grid-cols-7 gap-0 pb-1 text-xs font-medium text-center text-gray-500 border-b border-gray-100">
                            <div>Min</div>
                            <div>Sen</div>
                            <div>Sel</div>
                            <div>Rab</div>
                            <div>Kam</div>
                            <div>Jum</div>
                            <div>Sab</div>
                        </div>

                        <div id="dates" class="grid grid-cols-7 gap-0 text-xs font-medium text-center text-gray-500">
                        </div>
                    </div>
                </div>
                <p class="mt-2 text-xs text-center text-red-500">
                    *Klik tanggal bertanda merah untuk melihat siapa saja yang cuti
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: Charts and Recent Requests -->
<div class="grid grid-cols-1 gap-6 mt-8 lg:grid-cols-2">
    <!-- Left: Charts -->
    <div class="space-y-6">
        <!-- Monthly Requests Chart -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Monthly Requests Overview</h3>
                <p class="text-sm text-gray-500">Total requests per month</p>
            </div>
            <div class="p-6">
                <canvas id="monthlyRequestsChart" width="400" height="250"></canvas>
            </div>
        </div>

        <!-- Request Types Distribution -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Request Types Distribution</h3>
                <p class="text-sm text-gray-500">Breakdown by request type</p>
            </div>
            <div class="p-6">
                <canvas id="requestTypesChart" width="400" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Right: Recent Requests -->
    <div class="mb-8 bg-white border border-gray-200 rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Requests</h3>
            <p class="text-sm text-gray-500">Your latest submissions</p>
        </div>
        <div class="p-6">
            @forelse($recentRequests as $request)
            <div class="flex items-center justify-between py-4 border-b border-gray-100 cursor-pointer last:border-0"
                onclick="window.location.href='{{ $request['url'] }}'">
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
                            {{ $request['title'] ?? ($request['type'] === App\Enums\TypeRequest::Overtimes->value ?
                            'Overtime
                            Request' : 'Travel Request') }}
                        </h4>

                        <!-- Profile Picture, Name, and Division -->
                        @if(isset($request['employee_name']))
                        <div class="flex items-center gap-2 mt-1">
                            @if(isset($request['url_profile']) && $request['url_profile'])
                            <img src="{{ $request['url_profile'] }}" alt="{{ $request['employee_name'] }}"
                                class="object-cover w-6 h-6 border border-gray-200 rounded-full">
                            @else
                            <!-- Default profile dengan background abu-abu terang -->
                            <div
                                class="flex items-center justify-center w-6 h-6 text-xs text-blue-600 border border-blue-100 rounded-full bg-blue-50">
                                {{ substr($request['employee_name'], 0, 1) }}
                            </div>
                            @endif

                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ $request['employee_name'] }}
                                </span>
                                @if(isset($request['division_name']))
                                <span class="text-xs text-gray-500">
                                    {{ $request['division_name'] }}
                                </span>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Date -->
                        <p class="mt-1 text-xs text-gray-500">
                            {{ $request['date'] }}
                        </p>
                    </div>
                </div>

                <!-- Kanan: status + arrow -->
                <div class="flex items-center flex-shrink-0 ml-3">
                    @if(isset($request['status_2']) && $request['status_2'] !== null)
                    {{-- Jika ada status_2, maka cek keduanya --}}
                    @if($request['status_1'] === 'approved' && $request['status_2'] === 'approved')
                    <span
                        class="px-3 py-1 mr-3 text-xs font-medium text-green-800 bg-green-100 rounded-full">Approved</span>
                    @elseif($request['status_1'] === 'rejected' || $request['status_2'] === 'rejected')
                    <span
                        class="px-3 py-1 mr-3 text-xs font-medium text-red-800 bg-red-100 rounded-full">Rejected</span>
                    @else
                    <span
                        class="px-3 py-1 mr-3 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Pending</span>
                    @endif
                    @else
                    {{-- Jika tidak ada status_2, cek hanya status_1 --}}
                    @if($request['status_1'] === 'approved')
                    <span
                        class="px-3 py-1 mr-3 text-xs font-medium text-green-800 bg-green-100 rounded-full">Approved</span>
                    @elseif($request['status_1'] === 'rejected')
                    <span
                        class="px-3 py-1 mr-3 text-xs font-medium text-red-800 bg-red-100 rounded-full">Rejected</span>
                    @else
                    <span
                        class="px-3 py-1 mr-3 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Pending</span>
                    @endif
                    @endif
                </div>
            </div>
            @empty
            <div class="py-8 text-center">
                <i class="mb-3 text-4xl text-gray-300 fas fa-inbox"></i>
                <p class="text-gray-500">No recent requests found.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal dengan Pagination -->
<div id="cutiModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/50 backdrop-blur-sm">
    <div class="bg-white p-6 rounded-2xl w-[95%] max-w-2xl shadow-lg transform transition-all scale-95 opacity-0"
        id="cutiModalContent">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">List of Employee on Leave</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Navigation dan Info -->
        <div class="flex items-center justify-between mb-4">
            <span id="currentPageInfo" class="text-sm text-gray-600">Page 1 of 1</span>
            <div class="flex gap-2">
                <button id="prevPage"
                    class="px-3 py-1 text-sm bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button id="nextPage"
                    class="px-3 py-1 text-sm bg-gray-100 rounded-lg hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Container untuk daftar karyawan dengan grid horizontal -->
        <div id="cutiContainer" class="overflow-hidden">
            <div id="cutiPages" class="flex transition-transform duration-300 ease-in-out">
                <!-- Halaman akan diisi oleh JavaScript -->
            </div>
        </div>
    </div>
</div>

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
<script>
    // Chart.js Configuration
    Chart.defaults.font.family = "Inter, system-ui, sans-serif";
    Chart.defaults.color = "#6B7280";

    // Color scheme based on the design system - Warna lebih solid dan tajam
    const colors = {
        primary: "#3B82F6",     // Blue lebih cerah
        secondary: "#06B6D4",   // Cyan lebih tajam
        accent: "#10B981",      // Green tetap
        warning: "#F59E0B",     // Amber tetap
        error: "#EF4444",       // Red tetap
        info: "#8B5CF6",        // Purple untuk variasi
        neutral: "#6B7280",     // Gray tetap
        light: "#F3F4F6",       // Light Gray tetap
    };

    // Initialize Charts
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Requests Chart
        const monthlyCtx = document.getElementById('monthlyRequestsChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: chartData.months,
                datasets: [
                    {
                        label: 'Reimbursements',
                        data: chartData.reimbursements,
                        borderColor: colors.primary,
                        backgroundColor: colors.primary + 'CC',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Overtimes',
                        data: chartData.overtimes,
                        borderColor: colors.accent,
                        backgroundColor: colors.accent + 'CC',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Leaves',
                        data: chartData.leaves,
                        borderColor: colors.warning,
                        backgroundColor: colors.warning + 'CC',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Official Travels',
                        data: chartData.officialTravels,
                        borderColor: colors.secondary,
                        backgroundColor: colors.secondary + 'CC',
                        tension: 0.4,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Request Types Distribution Chart
        const typesCtx = document.getElementById('requestTypesChart').getContext('2d');

        // Calculate total requests for each type
        const totalReimbursements = chartData.reimbursements.reduce((a, b) => a + b, 0);
        const totalOvertimes = chartData.overtimes.reduce((a, b) => a + b, 0);
        const totalLeaves = chartData.leaves.reduce((a, b) => a + b, 0);
        const totalTravels = chartData.officialTravels.reduce((a, b) => a + b, 0);

        new Chart(typesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Reimbursements', 'Overtimes', 'Leaves', 'Official Travels'],
                datasets: [{
                    data: [totalReimbursements, totalOvertimes, totalLeaves, totalTravels],
                    backgroundColor: [
                        colors.primary,
                        colors.accent,
                        colors.warning,
                        colors.secondary
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                cutout: '60%'
            }
        });
    });

    // Calendar functionality
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
                relative h-8 w-8 aspect-square flex items-center justify-center
                rounded-lg cursor-pointer text-xs
                hover:bg-red-100 transition
                ${isToday ? 'bg-gray-200 font-bold' : ''}
            `;

            let content = `<span>${day}</span>`;

            if (cutiPerTanggal[dateStr]) {
                content += `
                    <span class="absolute top-0 right-0 w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                `;
            }

            datesContainer.innerHTML += `
                <div class="${classes}" onclick="showEvent('${dateStr}')">
                    ${content}
                </div>
            `;
        }
    }

    // Variabel global untuk pagination
    let currentPage = 1;
    let totalPages = 1;
    const itemsPerPage = 6; // Jumlah item per halaman

    function showEvent(dateStr) {
        const modal = document.getElementById('cutiModal');
        const modalContent = document.getElementById('cutiModalContent');
        const cutiPages = document.getElementById('cutiPages');
        const currentPageInfo = document.getElementById('currentPageInfo');
        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');

        cutiPages.innerHTML = "";
        currentPage = 1;

        if (cutiPerTanggal[dateStr]) {
            const employees = cutiPerTanggal[dateStr];
            totalPages = Math.ceil(employees.length / itemsPerPage);

            // Buat halaman-halaman
            for (let page = 0; page < totalPages; page++) {
                const pageContainer = document.createElement('div');
                pageContainer.className = 'w-full flex-shrink-0 grid grid-cols-2 gap-3';

                const startIndex = page * itemsPerPage;
                const endIndex = Math.min(startIndex + itemsPerPage, employees.length);

                for (let i = startIndex; i < endIndex; i++) {
                    const cuti = employees[i];
                    let firstLetter = cuti.employee ? cuti.employee.substring(0, 1).toUpperCase() : "?";

                    pageContainer.innerHTML += `
                        <div class="flex items-center gap-2 p-3 rounded-lg bg-gray-50">
                            ${cuti.url_profile ? `
                                <img class="flex items-center justify-center object-cover w-10 h-10 rounded-full"
                                    src="${cuti.url_profile}" alt="${cuti.employee}">
                            ` : `
                                <span class="flex items-center justify-center w-10 h-10 text-xs text-blue-600 bg-blue-100 rounded-full">
                                    ${firstLetter}
                                </span>
                            `}
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800 truncate">${cuti.employee ?? '-'}</p>
                                <p class="text-xs text-gray-500 truncate">${cuti.email ?? '-'}</p>
                            </div>
                        </div>
                    `;
                }

                cutiPages.appendChild(pageContainer);
            }

            // Update info halaman dan tombol
            updatePaginationInfo();

        } else {
            cutiPages.innerHTML = `
                <div class="w-full py-8 text-center text-gray-600">
                    Tidak ada karyawan yang cuti pada tanggal ini
                </div>
            `;
            currentPageInfo.textContent = "Page 1 of 1";
            prevPageBtn.disabled = true;
            nextPageBtn.disabled = true;
        }

        modal.classList.remove("hidden");

        // Animasi muncul
        setTimeout(() => {
            modalContent.classList.remove("scale-95", "opacity-0");
            modalContent.classList.add("scale-100", "opacity-100");
        }, 10);
    }

    function updatePaginationInfo() {
        const currentPageInfo = document.getElementById('currentPageInfo');
        const prevPageBtn = document.getElementById('prevPage');
        const nextPageBtn = document.getElementById('nextPage');
        const cutiPages = document.getElementById('cutiPages');

        currentPageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages;

        // Geser halaman
        cutiPages.style.transform = `translateX(-${(currentPage - 1) * 100}%)`;
    }

    // Event listeners untuk pagination
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            updatePaginationInfo();
        }
    });

    document.getElementById('nextPage').addEventListener('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            updatePaginationInfo();
        }
    });

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
</script>
@endpush
@endsection
