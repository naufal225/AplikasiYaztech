@extends('components.admin.layout.layout-admin')
@section('header', 'Cost Settings')
@section('subtitle', 'Manage application cost settings')

@section('content')
<main class="relative z-10 flex-1 p-0 space-y-6 overflow-x-hidden overflow-y-auto bg-gray-50">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Cost Settings</h1>
            <p class="text-neutral-600">Manage application cost configurations</p>
        </div>
    </div>

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

    <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
        <div class="px-6 py-4 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">Application Cost Settings</h2>
            <p class="text-sm text-neutral-600">Update the cost values used throughout the application</p>
        </div>

        <form action="{{ route('admin.cost-settings.update-multiple') }}" method="POST" class="p-6">
            @csrf
            <div class="space-y-6">
                @foreach($settings as $setting)
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-semibold text-neutral-700">
                            {{ $setting->name }}
                        </label>
                        <p class="mt-1 text-sm text-neutral-500">{{ $setting->description }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex items-center">
                            <span
                                class="px-3 py-2 text-sm border border-r-0 rounded-l-lg bg-neutral-50 border-neutral-300">Rp</span>
                            <input type="number" name="{{ $setting->key }}"
                                value="{{ old($setting->key, $setting->value) }}" step="0.01" min="0"
                                class="flex-1 block w-full px-3 py-2 border rounded-r-lg border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                placeholder="Enter value">
                        </div>
                        @error($setting->key)
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <hr class="border-neutral-200">
                @endforeach
            </div>

            <div class="flex justify-end pt-6 mt-6 space-x-4 border-t border-neutral-200">
                <button type="reset"
                    class="px-6 py-2 text-sm font-medium transition-colors duration-200 rounded-lg text-neutral-700 bg-neutral-100 hover:bg-neutral-200">
                    <i class="mr-2 fas fa-undo"></i>
                    Reset Changes
                </button>
                <button type="submit" class="btn-primary">
                    <i class="mr-2 fas fa-save"></i>
                    Save All Changes
                </button>
            </div>
        </form>
    </div>
</main>
@endsection