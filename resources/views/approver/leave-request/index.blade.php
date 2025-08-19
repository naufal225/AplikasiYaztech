@extends('components.approver.layout.layout-approver')
@section('header', 'Manage Leaves')
@section('subtitle', 'Manage Leaves data')

@section('content')
<main class="relative z-10 flex-1 p-0 space-y-6 overflow-x-hidden overflow-y-auto bg-gray-50">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Leave Requests</h1>
            <p class="text-neutral-600">Manage and track your leave requests</p>
        </div>


    </div>

    <div class="">
        @if(session('success'))
        <div class="flex items-center p-4 my-6 border border-green-200 bg-green-50 rounded-xl">
            <div class="flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
        @endif
    </div>


    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
        <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-primary-100 text-primary-500">
                    <i class="text-xl fas fa-calendar-alt"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Total Requests</p>
                    <p class="text-lg font-semibold">{{ $totalRequests }}</p>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-warning-100 text-warning-500">
                    <i class="text-xl fas fa-clock"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Pending</p>
                    <p class="text-lg font-semibold">{{ $pendingRequests }}</p>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-success-100 text-success-500">
                    <i class="text-xl fas fa-check-circle"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Approved</p>
                    <p class="text-lg font-semibold">{{ $approvedRequests }}</p>
                </div>
            </div>
        </div>
        <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-error-100 text-error-500">
                    <i class="text-xl fas fa-times-circle"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-neutral-500">Rejected</p>
                    <p class="text-lg font-semibold">{{ $rejectedRequests }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 bg-white border rounded-xl shadow-soft border-neutral-200">
        <form id="filterForm" method="GET" action="{{ route('approver.leaves.index') }}"
            class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="block mb-2 text-sm font-medium text-neutral-700">Status</label>
                <select name="status" id="statusFilter" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status')==='approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status')==='rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <label class="block mb-2 text-sm font-medium text-neutral-700">From Date</label>
                <input type="date" name="from_date" id="fromDateFilter" value="{{ request('from_date') }}"
                    class="form-input">
            </div>
            <div>
                <label class="block mb-2 text-sm font-medium text-neutral-700">To Date</label>
                <input type="date" name="to_date" id="toDateFilter" value="{{ request('to_date') }}" class="form-input">
            </div>
            <div class="flex items-end">
                <button type="submit" class="mr-2 btn-primary">
                    <i class="mr-2 fas fa-search"></i>
                    Filter
                </button>
                <a href="{{ route('approver.leaves.index') }}" class="btn-secondary">
                    <i class="mr-2 fas fa-refresh"></i>
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-hidden bg-white border border-gray-100 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Leave Requests</h3>
            </div>
        </div>
        <div class="overflow-hidden bg-white border rounded-xl shadow-soft border-neutral-200">
            <div class="overflow-x-auto" x-data="leaveTable()"
                x-init="init({{ Auth::user()->division_id }}, '{{ Auth::user()->role == App\Roles::Approver->value ? 'approver' : 'manager' }}')">

                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Request ID</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Duration</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Status 1 - Team Lead</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Status 2 - Manager</th>
                            <th
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase text-neutral-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        <template x-for="row in rows" :key="row.id">
                            <tr class="transition-colors duration-200 hover:bg-neutral-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-neutral-900" x-text="'#'+row.id"></div>
                                        <div class="text-sm text-neutral-500" x-text="row.created_at_fmt"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-900" x-text="row.date_range"></div>
                                    <div class="text-sm text-neutral-500" x-text="row.total_days + ' days'"></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"
                                    x-html="row.status_1 === 'approved' ? `<span class='text-green-500 badge-approved'><i class='mr-1 fas fa-check-circle'></i>Approved</span>` : (row.status_1 === 'rejected' ? `<span class='text-red-500 badge-rejected'><i class='mr-1 fas fa-times-circle'></i>Rejected</span>` : `<span class='text-yellow-500 badge-pending'><i class='mr-1 fas fa-clock'></i>Pending</span>`)">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"
                                    x-html="row.status_2 === 'approved' ? `<span class='text-green-500 badge-approved'><i class='mr-1 fas fa-check-circle'></i>Approved</span>` : (row.status_2 === 'rejected' ? `<span class='text-red-500 badge-rejected'><i class='mr-1 fas fa-times-circle'></i>Rejected</span>` : `<span class='text-yellow-500 badge-pending'><i class='mr-1 fas fa-clock'></i>Pending</span>`)">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a :href="row.show_url" class="text-primary-600 hover:text-primary-900"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </template>
                        @forelse($leaves as $leave)
                        <tr class="transition-colors duration-200 hover:bg-neutral-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-neutral-900">#{{ $leave->id }}</div>
                                    <div class="text-sm text-neutral-500">{{ $leave->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-neutral-900">
                                    {{ \Carbon\Carbon::parse($leave->date_start)->format('M d') }} - {{
                                    \Carbon\Carbon::parse($leave->date_end)->format('M d, Y') }}
                                </div>
                                <div class="text-sm text-neutral-500">
                                    {{
                                    \Carbon\Carbon::parse($leave->date_start)->diffInDays(\Carbon\Carbon::parse($leave->date_end))
                                    + 1 }} days
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($leave->status_1 === 'pending')
                                <span class="text-yellow-500 badge-pending">
                                    <i class="mr-1 fas fa-clock"></i>
                                    Pending
                                </span>
                                @elseif($leave->status_1 === 'approved')
                                <span class="text-green-500 badge-approved">
                                    <i class="mr-1 fas fa-check-circle"></i>
                                    Approved
                                </span>
                                @elseif($leave->status_1 === 'rejected')
                                <span class="text-red-500 badge-rejected">
                                    <i class="mr-1 fas fa-times-circle"></i>
                                    Rejected
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($leave->status_2 === 'pending')
                                <span class="text-yellow-500 badge-pending">
                                    <i class="mr-1 fas fa-clock"></i>
                                    Pending
                                </span>
                                @elseif($leave->status_2 === 'approved')
                                <span class="text-green-500 badge-approved">
                                    <i class="mr-1 fas fa-check-circle"></i>
                                    Approved
                                </span>
                                @elseif($leave->status_2 === 'rejected')
                                <span class="text-red-500 badge-rejected">
                                    <i class="mr-1 fas fa-times-circle"></i>
                                    Rejected
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('approver.leaves.show', $leave->id) }}"
                                        class="text-primary-600 hover:text-primary-900" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>


                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-neutral-400">
                                    <i class="mb-4 text-4xl fas fa-inbox"></i>
                                    <p class="text-lg font-medium">No leave requests found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($leaves->hasPages())
            <div class="px-6 py-4 border-t border-neutral-200">
                {{ $leaves->links() }}
            </div>
            @endif
        </div>
    </div>

</main>

@endsection

@push('scripts')
<script>
    function leaveTable() {
  return {
    rows: [],
    badge(s){ if(s==='approved') return `<span class='text-green-500 badge-approved'><i class='mr-1 fas fa-check-circle'></i>Approved</span>`;
              if(s==='rejected') return `<span class='text-red-500 badge-rejected'><i class='mr-1 fas fa-times-circle'></i>Rejected</span>`;
              return `<span class='text-yellow-500 badge-pending'><i class='mr-1 fas fa-clock'></i>Pending</span>`; },
    mapLeave(lv){ return {
      id: lv.id,
      created_at_fmt: lv.created_at_fmt ?? '',
      date_range: `${lv.date_start_fmt ?? ''} - ${lv.date_end_fmt ?? ''}`,
      total_days: lv.total_days ?? '',
      status_1: lv.status_1 ?? 'pending',
      status_2: lv.status_2 ?? 'pending',
      show_url: `/approver/leaves/${lv.id}`,
    }},
    init(divisionId, role){
      const waitEcho=(cb,t=80)=>{ if(window.Echo) return cb(); if(t<=0) return console.error('Echo never loaded'); setTimeout(()=>waitEcho(cb,t-1),100); };
      waitEcho(()=> {
        const ch = role==='approver'
          ? Echo.private(`approver.division.${divisionId}`)
          : Echo.private(`manager.division.${divisionId}`);

        ch.subscribed(()=>console.log('✅ table subscribed', ch.name))
          .error(e=>console.error('❌ table', e))
          .listen('.leave.submitted', (e)=>{ if(role==='approver' && e?.leave){ this.rows.unshift(this.mapLeave(e.leave)); }})
          .listen('.leave.level-advanced', (e)=>{ if(role!=='approver' && e?.newLevel==='manager'){ this.rows.unshift(this.mapLeave(e.leave)); }});
      });
    }
  }
}
</script>
@endpush
