@extends('Employee.layouts.app')

@section('title', 'Official Travel Requests')
@section('header', 'Official Travel Requests')
@section('subtitle', 'Manage your business travel requests')

@section('content')
    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">Official Travel Requests</h1>
                <p class="text-neutral-600">Plan and track your business trips</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('employee.official-travels.create') }}" class="btn-warning">
                    <i class="fas fa-plus mr-2"></i>
                    New Travel Request
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 p-6">
            <form method="GET" action="{{ route('employee.official-travels.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-input">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-input">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary mr-2">
                        <i class="fas fa-search mr-2"></i>
                        Filter
                    </button>
                    <a href="{{ route('employee.official-travels.index') }}" class="btn-secondary">
                        <i class="fas fa-refresh mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Official Travels Table -->
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Request</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Destination</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Budget</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @forelse($travels as $travel)
                            <tr class="hover:bg-neutral-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900">#{{ $travel->id }}</div>
                                        <div class="text-sm text-neutral-500">{{ $travel->created_at->format('M d, Y') }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-warning-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-warning-600 font-semibold text-xs">{{ substr($travel->employee->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-neutral-900">{{ $travel->employee->name }}</div>
                                            <div class="text-sm text-neutral-500">{{ $travel->employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-neutral-900">{{ $travel->destination }}</div>
                                    <div class="text-sm text-neutral-500">{{ Str::limit($travel->purpose, 30) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900">
                                        {{ $travel->date_start->format('M d') }} - {{ $travel->date_end->format('M d, Y') }}
                                    </div>
                                    <div class="text-sm text-neutral-500">{{ $travel->duration }} days</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-neutral-900">{{ $travel->formatted_total }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($travel->status === 'pending')
                                        <span class="badge-pending">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @elseif($travel->status === 'approved')
                                        <span class="badge-approved">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Approved
                                        </span>
                                    @elseif($travel->status === 'rejected')
                                        <span class="badge-rejected">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('employee.official-travels.show', $travel->id) }}" class="text-primary-600 hover:text-primary-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::id() === $travel->employee_id && $travel->status === 'pending')
                                            <a href="{{ route('employee.official-travels.edit', $travel->id) }}" class="text-secondary-600 hover:text-secondary-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('employee.official-travels.destroy', $travel->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error-600 hover:text-error-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if((Auth::id() === $travel->approver_id || Auth::user()->role === 'admin') && $travel->status === 'pending')
                                            <a href="{{ route('employee.official-travels.review', $travel->id) }}" class="text-success-600 hover:text-success-900">
                                                <i class="fas fa-gavel"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-neutral-400">
                                        <i class="fas fa-plane text-4xl mb-4"></i>
                                        <p class="text-lg font-medium">No travel requests found</p>
                                        <p class="text-sm">Submit your first travel request to get started</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($travels->hasPages())
                <div class="px-6 py-4 border-t border-neutral-200">
                    {{ $travels->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection