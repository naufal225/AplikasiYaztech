@extends('errors.layout')

@section('title', 'Akses Ditolak')

@section('content')
    <div class="mb-4 text-5xl text-yellow-500">403</div>
    <h1 class="mb-2 text-2xl font-bold text-gray-900">Akses Ditolak</h1>
    <p class="mb-6 text-gray-600">
        Anda tidak memiliki izin untuk mengakses halaman ini.
    </p>
    @if(isset($exception) && $exception->getMessage())
        <div class="px-4 py-3 mb-6 text-sm text-yellow-800 bg-yellow-50 border border-yellow-200 rounded-lg">
            {{ $exception->getMessage() }}
        </div>
    @elseif(!empty($message))
        <div class="px-4 py-3 mb-6 text-sm text-yellow-800 bg-yellow-50 border border-yellow-200 rounded-lg">
            {{ $message }}
        </div>
    @endif
    <div class="space-y-3">
        <a href="{{ url('/') }}"
           class="inline-block px-6 py-2 text-white rounded-lg transition-colors
                  {{ auth()->check() ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700' }}">
            {{ auth()->check() ? 'Kembali ke Beranda' : 'Kembali ke Login' }}
        </a>
    </div>
@endsection
