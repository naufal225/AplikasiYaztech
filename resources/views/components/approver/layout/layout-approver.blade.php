<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approver Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar - Primary Blue (35%) -->
        @include('components.approver.sidebar')

        <!-- Main Content -->
        <div class="relative z-10 flex flex-col flex-1 overflow-hidden lg:ml-0">
            <!-- Header - Secondary Sky Blue (20%) -->
            @include('components.approver.header')

            <!-- Dashboard Content -->
            <main class="relative z-10 flex-1 p-6 overflow-x-hidden overflow-y-auto bg-gray-50">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Sidebar Overlay for Mobile - Fixed positioning -->
    <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/20 lg:hidden"></div>

    @yield('partial-modal')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const leaveNav = document.getElementById('leave-nav');
            const officialTravelNav = document.getElementById('official-travel-nav');
            const overtimeNav = document.getElementById('overtime-nav');
            const reimbursementlNav = document.getElementById('reimbursement-nav');
            const badgeLeave = document.getElementById('leave-badge');
            const badgeTravel = document.getElementById('official-travel-badge');
            const badgeOvertime = document.getElementById('overtime-badge');
            const badgeReimbursement = document.getElementById('reimbursement-badge');
            if (!leaveNav || !badgeLeave || !officialTravelNav || !badgeTravel || !reimbursementlNav || !badgeReimbursement || !overtimeNav || !badgeOvertime || !window.Echo) return;

            const role = leaveNav.dataset.role;
            const divisionId = leaveNav.dataset.divisionId;

            function incrementBadge(badgeElement) {
                if (!badgeElement) return;
                let current = parseInt(badgeElement.textContent) || 0;
                badgeElement.textContent = current + 1;
                badgeElement.style.display = 'inline-flex';
            }

            if (role === 'approver') {
                window.Echo.private(`approver.division.${divisionId}`)
                    .listen('.leave.submitted', (e) => {
                        console.log('[Echo] leave.submitted received', e);
                        incrementBadge(badgeLeave);
                    })
                    .listen('.official-travel.submitted', (e) => {
                        console.log('[Echo] official-travel.submitted received', e);
                        incrementBadge(badgeTravel);
                    })
                    .listen('.overtime.submitted', (e) => {
                        console.log('[Echo] overtime.submitted received', e);
                        incrementBadge(badgeOvertime);
                    })
                    .listen('.reimbursement.submitted', (e) => {
                        console.log('[Echo] reimbursement.submitted received', e);
                        incrementBadge(badgeReimbursement);
                    });
            }

        });
    </script>


    <script>
        // Sidebar Toggle Functionality - Fixed
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            const isSidebarOpen = !sidebar.classList.contains('-translate-x-full');

            if (isSidebarOpen) {
                // Close sidebar
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            } else {
                // Open sidebar
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Event listeners
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });

        sidebarOverlay.addEventListener('click', function(e) {
            e.stopPropagation();
            closeSidebar();
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggle.contains(event.target);
            const isMobile = window.innerWidth < 1024;
            const isSidebarOpen = !sidebar.classList.contains('-translate-x-full');

            if (!isClickInsideSidebar && !isClickOnToggle && isMobile && isSidebarOpen) {
                closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                // Desktop view - ensure overlay is hidden and body scroll is enabled
                sidebarOverlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        });

        function loadLeaveTable() {
            fetch("{{ route('approver.leaves.index') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                // Ambil isi tabel dari response
                let parser = new DOMParser();
                let doc = parser.parseFromString(html, 'text/html');
                let newTable = doc.querySelector('#leave-table-wrapper'); // kasih id di view index
                if (newTable) {
                    document.querySelector('#leave-table-wrapper').innerHTML = newTable.innerHTML;
                }
            });
        }
    </script>

    @stack('scripts')

</body>

</html>
