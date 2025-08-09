<header class="bg-gradient-to-r from-sky-600 to-sky-700 shadow-lg relative z-20">
    <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center">
            <button id="sidebar-toggle"
                class="text-white hover:text-sky-200 focus:outline-none focus:text-sky-200 lg:hidden">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <h2 class="ml-4 text-xl font-semibold text-white">Dashboard Analytics</h2>
        </div>

        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button class="text-white hover:text-sky-200 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-5 5v-5zM10.07 2.82l3.12 3.12M7.05 5.84L3.93 8.96" />
                </svg>
            </button>

            <!-- User Profile -->
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                    <span class="text-sky-600 font-semibold text-sm">A</span>
                </div>
                <span class="text-white font-medium">Admin</span>
            </div>
        </div>
    </div>
</header>
