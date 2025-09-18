@extends('components.admin.layout.layout-admin')
@section('header', 'Edit Cost Setting')
@section('subtitle', 'Update cost setting value')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-neutral-700 hover:text-primary-600">
                    <i class="mr-2 fas fa-home"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                    <a href="{{ route('admin.cost-settings.index') }}"
                        class="text-sm font-medium text-neutral-700 hover:text-primary-600">Cost Settings</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="mx-2 fas fa-chevron-right text-neutral-400"></i>
                    <span class="text-sm font-medium text-neutral-500">Edit {{ $costSetting->name }}</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="bg-white border rounded-xl shadow-soft border-neutral-200">
        <div class="px-6 py-4 border-b border-neutral-200">
            <h2 class="text-lg font-bold text-neutral-900">Edit Cost Setting</h2>
            <p class="text-sm text-neutral-600">Update the value for {{ $costSetting->name }}</p>
        </div>

        @if ($errors->any())
        <div class="px-4 py-3 mx-6 mt-6 border rounded-lg bg-error-50 border-error-200 text-error-700">
            <ul class="pl-5 space-y-1 list-disc">
                @foreach ($errors->all() as $error)
                <li class="text-sm">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.cost-settings.update', $costSetting->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block mb-2 text-sm font-semibold text-neutral-700">
                    Setting Name
                </label>
                <div class="p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                    <p class="font-medium text-neutral-900">{{ $costSetting->name }}</p>
                </div>
            </div>

            <div>
                <label class="block mb-2 text-sm font-semibold text-neutral-700">
                    Description
                </label>
                <div class="p-3 border rounded-lg bg-neutral-50 border-neutral-200">
                    <p class="text-neutral-900">{{ $costSetting->description }}</p>
                </div>
            </div>

            <div>
                <label for="value" class="block mb-2 text-sm font-semibold text-neutral-700">
                    Value
                </label>
                <div class="flex items-center">
                    <span
                        class="px-3 py-2 text-sm border border-r-0 rounded-l-lg bg-neutral-50 border-neutral-300">Rp</span>
                    <input type="number" id="value" name="value" value="{{ old('value', $costSetting->value) }}"
                        step="0.01" min="0"
                        class="flex-1 block w-full px-3 py-2 border rounded-r-lg border-neutral-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                        required>
                </div>
                <p class="mt-1 text-xs text-neutral-500">Enter the numeric value without formatting</p>
            </div>

            <div class="flex justify-end pt-6 space-x-4 border-t border-neutral-200">
                <a href="{{ route('admin.cost-settings.index') }}"
                    class="px-6 py-2 text-sm font-medium transition-colors duration-200 rounded-lg text-neutral-700 bg-neutral-100 hover:bg-neutral-200">
                    <i class="mr-2 fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    <i class="mr-2 fas fa-save"></i>
                    Update Setting
                </button>
            </div>
        </form>
    </div>
</div>
@endsection