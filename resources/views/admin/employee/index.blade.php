@extends('components.admin.layout.layout-admin')

@section('content')
<!-- Employee Management Content -->
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 relative z-10">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manage Employee</h1>
                <p class="mt-2 text-sm text-gray-600">Manage your employee data and information</p>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                <!-- Import Excel Button - Warning Amber (5%) -->
                <button id="importExcelBtn"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    Import Excel
                </button>

                <!-- Add Employee Button - Primary Blue (35%) -->
                <a id="addEmployeeBtn" href="{{ route('admin.employee.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Employee
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" placeholder="Search employees..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Search Button -->
                <button
                    class="px-6 py-2 bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 text-white font-medium rounded-lg transition-all duration-200">
                    Search
                </button>
            </div>
        </div>
    </div>

    <!-- Employee Table - Light Neutral Background (15%) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Employee List</h3>
            </div>
        </div>

        <!-- Table Content -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Name</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Email</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($employees as $employee)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <!-- Edit Button - Secondary Sky Blue (20%) -->
                                <a
                                    href="{{ route('admin.employee.edit', $employee->id) }}"
                                    class="inline-flex items-center px-3 py-1 bg-sky-100 hover:bg-sky-200 text-sky-700 text-xs font-medium rounded-md transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </a>
                                <!-- Delete Button - Error Red (5%) -->
                                <button
                                    class="inline-flex items-center px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-md transition-colors">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-2">
                    {{ $employees->links() }}
                </div>
            </div>
        </div>
    </div>
</main>

@endsection

@section('partial-modal')
<!-- Import Excel Modal with Enhanced Drag & Drop -->
<div id="importExcelModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div
            class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Import Employee Data</h3>
                    <p class="text-sm text-gray-500 mt-1">Upload Excel file to import employee data</p>
                </div>
                <button id="closeImportModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Enhanced Drag & Drop Area -->
                <div id="dropZone"
                    class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all duration-300 hover:border-amber-400 hover:bg-amber-50 cursor-pointer group">
                    <!-- Default State -->
                    <div id="defaultState" class="space-y-4">
                        <div class="flex justify-center">
                            <div
                                class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-lg font-medium text-gray-900">Drop your Excel file here</p>
                            <p class="text-sm text-gray-500 mt-1">or click to browse files</p>
                        </div>
                        <div class="flex items-center justify-center space-x-2 text-xs text-gray-400">
                            <span>Supported formats:</span>
                            <span class="px-2 py-1 bg-gray-100 rounded text-gray-600 font-medium">.xlsx</span>
                            <span class="px-2 py-1 bg-gray-100 rounded text-gray-600 font-medium">.xls</span>
                        </div>
                    </div>

                    <!-- Drag Over State -->
                    <div id="dragOverState" class="hidden space-y-4">
                        <div class="flex justify-center">
                            <div
                                class="w-16 h-16 bg-amber-200 rounded-full flex items-center justify-center animate-pulse">
                                <svg class="w-8 h-8 text-amber-700" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-lg font-medium text-amber-700">Release to upload file</p>
                            <p class="text-sm text-amber-600">Drop your Excel file here</p>
                        </div>
                    </div>

                    <!-- File Input -->
                    <input id="excel-file" name="excel_file" type="file" accept=".xlsx,.xls"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required>
                </div>

                <!-- Selected File Display -->
                <div id="selected-file" class="hidden">
                    <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-xl">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-green-800" id="file-name"></p>
                            <p class="text-xs text-green-600" id="file-size"></p>
                        </div>
                        <button type="button" id="remove-file"
                            class="ml-4 text-green-600 hover:text-green-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Error Display -->
                <div id="error-message" class="hidden">
                    <div class="flex items-center p-4 bg-red-50 border border-red-200 rounded-xl">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800" id="error-text"></p>
                        </div>
                    </div>
                </div>

                <!-- Download Template Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm text-blue-800">
                                Need a template?
                                <a href="" class="font-medium underline hover:text-blue-900 transition-colors">
                                    Download Excel Template
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelImportBtn"
                        class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" id="importBtn"
                        class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                        <span id="importBtnText">Import Data</span>
                        <svg id="importBtnSpinner" class="hidden animate-spin -mr-1 ml-2 h-4 w-4 text-white" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite("resources/js/admin/employee/script-main.js")
@endpush
