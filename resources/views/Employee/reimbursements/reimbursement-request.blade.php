@extends('Employee.layouts.app')

@section('title', 'Submit Reimbursement')
@section('header', 'Submit Reimbursement')
@section('subtitle', 'Submit your expense reimbursement request')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-soft border border-neutral-200">
            <div class="px-6 py-4 border-b border-neutral-200">
                <h2 class="text-lg font-bold text-neutral-900">Submit Reimbursement Request</h2>
                <p class="text-neutral-600 text-sm">Fill in the details of your expense reimbursement</p>
            </div>
            
            @if ($errors->any())
                <div class="mx-6 mt-6 bg-error-50 border border-error-200 text-error-700 px-4 py-3 rounded-lg">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('employee.reimbursements.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                
                <div>
                    <label for="approver_id" class="block text-sm font-semibold text-neutral-700 mb-2">Approver</label>
                    <select id="approver_id" name="approver_id" class="form-select" required>
                        <option value="">Select Approver</option>
                        @foreach($approvers as $approver)
                            <option value="{{ $approver->id }}" {{ old('approver_id') == $approver->id ? 'selected' : '' }}>
                                {{ $approver->name }} ({{ ucfirst($approver->role) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date" class="block text-sm font-semibold text-neutral-700 mb-2">Expense Date</label>
                        <input type="date" id="date" name="date" class="form-input" value="{{ old('date') }}" required>
                    </div>
                    
                    <div>
                        <label for="total" class="block text-sm font-semibold text-neutral-700 mb-2">Total Amount (Rp)</label>
                        <input type="number" id="total" name="total" class="form-input" value="{{ old('total') }}" 
                               placeholder="0" min="0" step="0.01" required>
                    </div>
                </div>
                
                <div>
                    <label for="customer_id" class="block text-sm font-semibold text-neutral-700 mb-2">Customer (Optional)</label>
                    <select id="customer_id" name="customer_id" class="form-select">
                        <option value="">Select Customer (if applicable)</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-semibold text-neutral-700 mb-2">Description</label>
                    <textarea id="description" name="description" rows="4" class="form-textarea" 
                              placeholder="Describe the expense details..." required>{{ old('description') }}</textarea>
                </div>
                
                <div>
                    <label for="invoice" class="block text-sm font-semibold text-neutral-700 mb-2">Invoice/Receipt</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-neutral-300 border-dashed rounded-lg hover:border-neutral-400 transition-colors duration-200">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-cloud-upload-alt text-neutral-400 text-3xl"></i>
                            <div class="flex text-sm text-neutral-600">
                                <label for="invoice" class="relative cursor-pointer bg-white rounded-md font-medium text-secondary-600 hover:text-secondary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-secondary-500">
                                    <span>Upload a file</span>
                                    <input id="invoice" name="invoice" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-neutral-500">PDF, PNG, JPG up to 2MB</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 pt-6 border-t border-neutral-200">
                    <a href="{{ route('employee.reimbursements.index') }}" class="px-6 py-2 text-sm font-medium text-neutral-700 bg-neutral-100 hover:bg-neutral-200 rounded-lg transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="btn-secondary">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // File upload preview
        document.getElementById('invoice').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const uploadArea = e.target.closest('.border-dashed');
                uploadArea.innerHTML = `
                    <div class="space-y-1 text-center">
                        <i class="fas fa-file-alt text-secondary-600 text-3xl"></i>
                        <div class="text-sm text-neutral-900 font-medium">${fileName}</div>
                        <div class="text-xs text-neutral-500">${fileSize} MB</div>
                        <button type="button" onclick="clearFile()" class="text-xs text-error-600 hover:text-error-800">Remove</button>
                    </div>
                `;
            }
        });

        function clearFile() {
            document.getElementById('invoice').value = '';
            location.reload();
        }
    </script>
@endsection