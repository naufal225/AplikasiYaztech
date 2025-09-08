@extends('Employee.layouts.app')

@section('title', 'Reimbursement Requests')
@section('header', 'Reimbursement Requests')
@section('subtitle', 'Manage your reimbursement claims')

@section('content')
    <div class="max-w-4xl mx-auto">
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
                        <a href="{{ route('employee.reimbursements.index') }}" class="text-sm font-medium text-neutral-700 hover:text-primary-600">Reimbursement Requests</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <span class="text-sm font-medium text-neutral-500">Klaim #RY{{ $reimbursement->id }}</span>
                    </div>
                </li>
            </ol>
        </nav>
        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column - Main Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Request Header -->
                <div class="relative bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
                    <!-- Overlay Checklist -->
                    @if($reimbursement->marked_down)
                        <div class="absolute inset-0 flex items-center justify-center bg-white/70 z-10 rounded-xl">
                            <i class="fas fa-check-circle bg-white rounded-full text-green-500 text-5xl drop-shadow-lg"></i>
                        </div>
                    @endif
                    
                    <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-xl font-bold text-white">Reimbursement Claim #RY{{ $reimbursement->id }}</h1>
                                <p class="text-primary-100 text-sm">Submitted on {{ $reimbursement->created_at->format('M d, Y \a\t H:i') }}</p>
                            </div>
                            <div class="text-right">
                                @if($reimbursement->status_1 === 'rejected' || $reimbursement->status_2 === 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-error-100 text-error-800">
                                        <i class="mr-1 mt-1 fas fa-times-circle"></i>
                                        Rejected
                                    </span>
                                @elseif($reimbursement->status_1 === 'approved' && $reimbursement->status_2 === 'approved')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-success-100 text-success-800">
                                        <i class="mr-1 mt-1 fas fa-check-circle"></i>
                                        Approved
                                    </span>
                                @elseif($reimbursement->status_1 === 'pending' || $reimbursement->status_2 === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-warning-100 text-warning-800">
                                        <i class="mr-1 mt-1 fas fa-clock"></i>
                                        {{ $reimbursement->status_1 === 'pending' ? 'Pending' : 'In Progress' }} Review
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Reimbursement Details -->
                <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h2 class="text-lg font-bold text-neutral-900">Claim Details</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Email -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Email</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-envelope text-primary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ Auth::user()->email }}</span>
                                </div>
                            </div>
                            <!-- Approver -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Approver 1</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-user-check text-info-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $reimbursement->approver->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <!-- Total (was Amount) -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Total Amount</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-dollar-sign text-primary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">Rp {{ number_format($reimbursement->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <!-- Date -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Date of Expense</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-calendar-day text-secondary-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ \Carbon\Carbon::parse($reimbursement->date)->format('l, M d, Y') }}</span>
                                </div>
                            </div>
                            <!-- Customer -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Customer</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    <i class="fas fa-users text-info-600 mr-3"></i>
                                    <span class="text-neutral-900 font-medium">{{ $reimbursement->customer ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <!-- Invoice Path (was Attachment) -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Invoice</label>
                                <div class="p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    @if($reimbursement->invoice_path)
                                        <a href="{{ Storage::url($reimbursement->invoice_path) }}" target="_blank" class="flex items-center text-primary-600 hover:text-primary-800 font-medium">
                                            <i class="fas fa-file-alt mr-2"></i>
                                            View Invoice ({{ pathinfo($reimbursement->invoice_path, PATHINFO_EXTENSION) }})
                                        </a>
                                    @else
                                        <p class="text-neutral-500">No invoice provided.</p>
                                    @endif
                                </div>
                            </div>
                            <!-- Status -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Status - Approver 1</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    @if($reimbursement->status_1 === 'pending')
                                        <i class="fas fa-clock text-warning-600 mr-3"></i>
                                        <span class="text-warning-800 font-medium">Pending Review</span>
                                    @elseif($reimbursement->status_1 === 'approved')
                                        <i class="fas fa-check-circle text-success-600 mr-3"></i>
                                        <span class="text-success-800 font-medium">Approved</span>
                                    @elseif($reimbursement->status_1 === 'rejected')
                                        <i class="fas fa-times-circle text-error-600 mr-3"></i>
                                        <span class="text-error-800 font-medium">Rejected</span>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Status - Approver 2</label>
                                <div class="flex items-center p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                                    @if($reimbursement->status_2 === 'pending')
                                        <i class="fas fa-clock text-warning-600 mr-3"></i>
                                        <span class="text-warning-800 font-medium">Pending Review</span>
                                    @elseif($reimbursement->status_2 === 'approved')
                                        <i class="fas fa-check-circle text-success-600 mr-3"></i>
                                        <span class="text-success-800 font-medium">Approved</span>
                                    @elseif($reimbursement->status_2 === 'rejected')
                                        <i class="fas fa-times-circle text-error-600 mr-3"></i>
                                        <span class="text-error-800 font-medium">Rejected</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Note -->
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Note - Approver 1</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                    <span class="text-neutral-900">{{ $reimbursement->note_1 ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-neutral-700">Note - Approver 2</label>
                                <div class="flex items-center p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                                    <i class="mr-3 fas fa-sticky-note text-info-600"></i>
                                    <span class="text-neutral-900">{{ $reimbursement->note_2 ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
                    <div class="px-6 py-4 border-b border-neutral-200">
                        <h3 class="text-lg font-bold text-neutral-900">Actions</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if(Auth::id() === $reimbursement->employee_id && $reimbursement->status_1 === 'pending')
                            <a href="{{ route('employee.reimbursements.edit', $reimbursement->id) }}" class="w-full flex items-center justify-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Request
                            </a>
                            <form action="{{ route('employee.reimbursements.destroy', $reimbursement->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this reimbursement request?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-error-600 hover:bg-error-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Request
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('employee.reimbursements.index') }}" class="w-full flex items-center justify-center px-4 py-2 bg-neutral-600 hover:bg-neutral-700 text-white font-semibold rounded-lg transition-colors duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back to List
                        </a>
                        
                        @if ($reimbursement->status_1 == 'approved' && $reimbursement->status_2 == 'approved')
                            <button onclick="window.location.href='{{ route('employee.reimbursements.exportPdf', $reimbursement->id) }}'" class="w-full flex items-center justify-center px-4 py-2 bg-secondary-600 hover:bg-secondary-700 text-white font-semibold rounded-lg transition-colors duration-200">
                                <i class="fas fa-print mr-2"></i>
                                Print Request
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection